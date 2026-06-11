<?php
// php para registrar as locações
require_once '../conexao.php';

$filme_id    = (int)($_POST['filme_id']    ?? 0);
$nome        = trim($_POST['nome']         ?? '');
$data_loc    = $_POST['data_locacao']      ?? '';
$data_dev    = $_POST['data_devolucao']    ?? '';

// pega os parametros do form

// validando campos obrigatorios e numericos
if ($filme_id < 1 || $nome === '' || $data_loc === '' || $data_dev === '') {
    header('Location: ../locacoes.php?erro=campos_invalidos');
    exit;
}

// verifica disponibilidade, se está ativo e se a copias
$check = $pdo->prepare("SELECT quantidade_disponivel FROM filmes WHERE id = :id AND ativo = true");
$check->execute([':id' => $filme_id]);
$filme = $check->fetch(PDO::FETCH_ASSOC);

// se o filme não for encontrado, estiver inativo ou se a quantidade disponível for 0, não deixa locar
if (!$filme || $filme['quantidade_disponivel'] < 1) {
    header('Location: ../locacoes.php?erro=sem_estoque');
    exit;
}

// o 'beginTransaction' desativa o salvamento automático do bd, garantindo que se uma das operações falhar, nada será alterado
try {
    $ins = $pdo->prepare(
        // insere o novo registro na tabela "locacoes" e o status de pagamento vem como nao pago e o status de devolução vem com pendente
        "INSERT INTO locacoes (filme_id, nome_cliente, data_locacao, data_devolucao, status_pagamento, devolvido)
         VALUES (:fid, :nome, :dloc, :ddev, 'Pendente', false)"
    );
    $ins->execute([
        ':fid'  => $filme_id,
        ':nome' => $nome,
        ':dloc' => $data_loc,
        ':ddev' => $data_dev,
    ]);

    // se locarmos, a quantidade de filmes disponiveis diminui em 1
    $dec = $pdo->prepare(
        "UPDATE filmes SET quantidade_disponivel = quantidade_disponivel - 1 WHERE id = :id"
    );
    $dec->execute([':id' => $filme_id]);

    // se tudo der certo, haverá o commit e tudo será atualizado
    $pdo->commit();

} catch (Exception $e) {
    // se tiver qualquer erro dentro do "try", o "rollBack" desfaz tudo
    $pdo->rollBack();
    header('Location: ../locacoes.php?erro=transacao_falhou');
    exit;
}

// exibe mensagem de sucesso
header('Location: ../locacoes.php?ok=registrado');
exit;
?>