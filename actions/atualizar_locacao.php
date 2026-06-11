<?php
// php de atualização das locações (atualiza status do pagamento e de devolução)
require_once '../conexao.php';

// pega todos os parametros dos formularios
$id              = (int)($_POST['id']              ?? 0);
$status_pagamento = trim($_POST['status_pagamento'] ?? '');
$devolvido        = isset($_POST['devolvido']) ? (int)$_POST['devolvido'] : -1;

// se o id for menor que 1 a mensagem de erro é exibida
if ($id < 1) {
    header('Location: ../locacoes.php?erro=id_invalido');
    exit;
}

// prepara a consulta SQL de forma segura para evitar sql injection. traz apenas o status de devolução e o ID do filme
$atual = $pdo->prepare("SELECT devolvido, filme_id FROM locacoes WHERE id = :id");
// atribui o valor do $id para o :id
$atual->execute([':id' => $id]);
// pega os dados do bd e o transforma em um array associativo (chave/valor). agora, a variável $loc terá os índices $loc['devolvido'] e $loc['filme_id']
$loc = $atual->fetch(PDO::FETCH_ASSOC);

// se o programa não encontrar a $loc ele exibe mensagem de erro
if (!$loc) {
    header('Location: ../locacoes.php?erro=nao_encontrado');
    exit;
}


$pdo->beginTransaction();
try {
    // monta update dinâmico
    $sets   = [];
    $params = [':id' => $id];

    // verifica se o usuário enviou uma alteração para o status de pagamento
    if ($status_pagamento !== '') {
        $sets[] = 'status_pagamento = :spag';
        $params[':spag'] = $status_pagamento;
    }

    // verifica se teve tentativa de alteração no status de devolução
    if ($devolvido !== -1) {
        // converte os status atual e anterior para booleano
        $novo_dev = (bool)$devolvido;
        $era_dev  = (bool)$loc['devolvido'];

        // adiciona o campo de devolução à lista de atualização dinâmica da locação
        $sets[]   = 'devolvido = :dev';
        $params[':dev'] = $novo_dev ? 'true' : 'false';

        // ajusta o estoque só se o estado mudou
        if ($novo_dev && !$era_dev) {
            // marcou como devolvido -> +1
            $upd = $pdo->prepare(
                "UPDATE filmes SET quantidade_disponivel = quantidade_disponivel + 1 WHERE id = :fid"
            );
            $upd->execute([':fid' => $loc['filme_id']]);
        } elseif (!$novo_dev && $era_dev) {
            // desmarcou a devolução -> -1
            $upd = $pdo->prepare(
                "UPDATE filmes SET quantidade_disponivel = GREATEST(quantidade_disponivel - 1, 0) WHERE id = :fid"
                // o GREATEST impede que erros de concorrência ou cliques duplos deixem o estoque negativo
            );
            $upd->execute([':fid' => $loc['filme_id']]);
        }
    }

    // se houver campos modificados monta e executa a query dinamicamente
    if (!empty($sets)) {
        // implode -> junta os campos geradas com vírgulas no meio
        $sql  = "UPDATE locacoes SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    // confirma todas as alterações no banco de dados
    $pdo->commit();

    // se qualquer query falhar, desfaz todas as alterações
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: ../locacoes.php?erro=transacao_falhou');
    exit;
}


// exibe mensagem de sucesso
header('Location: ../locacoes.php?ok=atualizado');
exit;
