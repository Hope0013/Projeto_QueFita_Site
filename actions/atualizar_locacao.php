<?php
require_once '../conexao.php';

$id              = (int)($_POST['id']              ?? 0);
$status_pagamento = trim($_POST['status_pagamento'] ?? '');
$devolvido        = isset($_POST['devolvido']) ? (int)$_POST['devolvido'] : -1;

if ($id < 1) {
    header('Location: ../locacoes.php?erro=id_invalido');
    exit;
}

// Busca estado atual
$atual = $pdo->prepare("SELECT devolvido, filme_id FROM locacoes WHERE id = :id");
$atual->execute([':id' => $id]);
$loc = $atual->fetch(PDO::FETCH_ASSOC);

if (!$loc) {
    header('Location: ../locacoes.php?erro=nao_encontrado');
    exit;
}

$pdo->beginTransaction();
try {
    // Monta update dinâmico
    $sets   = [];
    $params = [':id' => $id];

    if ($status_pagamento !== '') {
        $sets[] = 'status_pagamento = :spag';
        $params[':spag'] = $status_pagamento;
    }

    if ($devolvido !== -1) {
        $novo_dev = (bool)$devolvido;
        $era_dev  = (bool)$loc['devolvido'];
        $sets[]   = 'devolvido = :dev';
        $params[':dev'] = $novo_dev ? 'true' : 'false';

        // Ajusta estoque só se o estado mudou
        if ($novo_dev && !$era_dev) {
            // Marcou como devolvido => +1
            $upd = $pdo->prepare(
                "UPDATE filmes SET quantidade_disponivel = quantidade_disponivel + 1 WHERE id = :fid"
            );
            $upd->execute([':fid' => $loc['filme_id']]);
        } elseif (!$novo_dev && $era_dev) {
            // Desmarcou devolução => -1
            $upd = $pdo->prepare(
                "UPDATE filmes SET quantidade_disponivel = GREATEST(quantidade_disponivel - 1, 0) WHERE id = :fid"
            );
            $upd->execute([':fid' => $loc['filme_id']]);
        }
    }

    if (!empty($sets)) {
        $sql  = "UPDATE locacoes SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: ../locacoes.php?erro=transacao_falhou');
    exit;
}

header('Location: ../locacoes.php?ok=atualizado');
exit;
