<?php
require_once '../conexao.php';

$titulo = trim($_POST['titulo'] ?? '');
$genero = trim($_POST['genero'] ?? '');
$total  = (int)($_POST['total']  ?? 0);
$ativo  = isset($_POST['ativo']) ? (int)$_POST['ativo'] : 1;

if ($titulo === '' || $genero === '' || $total < 1) {
    header('Location: ../index.php?erro=campos_invalidos');
    exit;
}

// Upload de imagem
$foto_path = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $mime    = mime_content_type($_FILES['foto']['tmp_name']);

    if (!in_array($mime, $allowed)) {
        header('Location: ../index.php?erro=tipo_invalido');
        exit;
    }
    if ($_FILES['foto']['size'] > 4 * 1024 * 1024) {
        header('Location: ../index.php?erro=arquivo_grande');
        exit;
    }

    $ext       = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $nome      = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destino   = __DIR__ . '/../uploads/imagens/' . $nome;
    move_uploaded_file($_FILES['foto']['tmp_name'], $destino);
    $foto_path = 'uploads/imagens/' . $nome;
}

$sql = "INSERT INTO filmes (titulo, genero, quantidade_total, quantidade_disponivel, ativo, foto)
        VALUES (:titulo, :genero, :total, :total, :ativo, :foto)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':titulo'  => $titulo,
    ':genero'  => $genero,
    ':total'   => $total,
    ':ativo'   => $ativo ? 'true' : 'false',
    ':foto'    => $foto_path,
]);

header('Location: ../index.php?ok=cadastrado');
exit;
