<?php
require_once '../conexao.php';

$filme_id    = (int)($_POST['filme_id']    ?? 0);
$nome        = trim($_POST['nome']         ?? '');
$data_loc    = $_POST['data_locacao']      ?? '';
$data_dev    = $_POST['data_devolucao']    ?? '';

if ($filme_id < 1 || $nome === '' || $data_loc === '' || $data_dev === '') {
    header('Location: ../locacoes.php?erro=campos_invalidos');
    exit;
}

// Verifica disponibilidade
$check = $pdo->prepare("SELECT quantidade_disponivel FROM filmes WHERE id = :id AND ativo = true");
$check->execute([':id' => $filme_id]);
$filme = $check->fetch(PDO::FETCH_ASSOC);

if (!$filme || $filme['quantidade_disponivel'] < 1) {
    header('Location: ../locacoes.php?erro=sem_estoque');
    exit;
}

// Insere locação e decrementa disponível em transação
$pdo->beginTransaction();
try {
    $ins = $pdo->prepare(
        "INSERT INTO locacoes (filme_id, nome_cliente, data_locacao, data_devolucao, status_pagamento, devolvido)
         VALUES (:fid, :nome, :dloc, :ddev, 'Pendente', false)"
    );
    $ins->execute([
        ':fid'  => $filme_id,
        ':nome' => $nome,
        ':dloc' => $data_loc,
        ':ddev' => $data_dev,
    ]);

    $dec = $pdo->prepare(
        "UPDATE filmes SET quantidade_disponivel = quantidade_disponivel - 1 WHERE id = :id"
    );
    $dec->execute([':id' => $filme_id]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: ../locacoes.php?erro=transacao_falhou');
    exit;
}

header('Location: ../locacoes.php?ok=registrado');
exit;
?>