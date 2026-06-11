<?php
// php para desativar os filmes
require_once '../conexao.php';

// recebe o id do filme
$id = (int)($_POST['id'] ?? 0);

// se for menos que 1 da erro
if ($id < 1) {
    header('Location: ../index.php?erro=id_invalido');
    exit;
}

// verifica se há locações ativas para este filme
$check = $pdo->prepare("SELECT COUNT(*) FROM locacoes WHERE filme_id = :id AND devolvido = false");
$check->execute([':id' => $id]);
$ativos = (int)$check->fetchColumn();

// se tiver locado, não permite desativar
if ($ativos > 0) {
    header('Location: ../index.php?erro=filme_locado');
    exit;
}

// atualiza o status do filme para inativo e não apaga
$stmt = $pdo->prepare("UPDATE filmes SET ativo = false WHERE id = :id");
$stmt->execute([':id' => $id]);


// exibe mensagem de sucesso
header('Location: ../index.php?ok=desativado');
exit;
?>