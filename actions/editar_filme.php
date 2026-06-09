<?php
require_once '../conexao.php';

$id     = (int)($_POST['id']     ?? 0);
$titulo = trim($_POST['titulo']  ?? '');
$genero = trim($_POST['genero']  ?? '');
$total  = (int)($_POST['total']  ?? 0);
$disp   = (int)($_POST['disp']   ?? 0);
$ativo  = isset($_POST['ativo'])  ? (bool)(int)$_POST['ativo'] : true;

if ($id < 1 || $titulo === '' || $genero === '' || $total < 0 || $disp < 0) {
    header('Location: ../index.php?erro=campos_invalidos');
    exit;
}
if ($disp > $total) { $disp = $total; }

// Upload de nova imagem
$foto_sql  = '';
$foto_bind = [];

if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $mime    = mime_content_type($_FILES['foto']['tmp_name']);

    if (in_array($mime, $allowed) && $_FILES['foto']['size'] <= 4 * 1024 * 1024) {
        $ext     = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nome    = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destino = __DIR__ . '/../uploads/imagens/' . $nome;
        move_uploaded_file($_FILES['foto']['tmp_name'], $destino);
        $foto_sql        = ', foto = :foto';
        $foto_bind[':foto'] = 'uploads/imagens/' . $nome;
    }
}

$sql = "UPDATE filmes
        SET titulo = :titulo,
            genero = :genero,
            quantidade_total = :total,
            quantidade_disponivel = :disp,
            ativo = :ativo
            $foto_sql
        WHERE id = :id";

$stmt = $pdo->prepare($sql);
$params = array_merge([
    ':titulo' => $titulo,
    ':genero' => $genero,
    ':total'  => $total,
    ':disp'   => $disp,
    ':ativo'  => $ativo ? 'true' : 'false',
    ':id'     => $id,
], $foto_bind);

$stmt->execute($params);
header('Location: ../index.php?ok=editado');
exit;
?>