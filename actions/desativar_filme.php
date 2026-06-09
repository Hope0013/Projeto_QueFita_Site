<?php
require_once '../conexao.php';

$id = (int)($_POST['id'] ?? 0);
if ($id < 1) {
    header('Location: ../index.php?erro=id_invalido');
    exit;
}

// Verifica se há locações ativas para este filme
$check = $pdo->prepare("SELECT COUNT(*) FROM locacoes WHERE filme_id = :id AND devolvido = false");
$check->execute([':id' => $id]);
$ativos = (int)$check->fetchColumn();

if ($ativos > 0) {
    header('Location: ../index.php?erro=filme_locado');
    exit;
}

$stmt = $pdo->prepare("UPDATE filmes SET ativo = false WHERE id = :id");
$stmt->execute([':id' => $id]);

header('Location: ../index.php?ok=desativado');
exit;
?>