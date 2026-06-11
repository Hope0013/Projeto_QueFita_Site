<?php
// php para editar as informações dos filmes
require_once '../conexao.php';

$id     = (int)($_POST['id']     ?? 0);
$titulo = trim($_POST['titulo']  ?? '');
$genero = trim($_POST['genero']  ?? '');
$total  = (int)($_POST['total']  ?? 0);
$disp   = (int)($_POST['disp']   ?? 0);
$ativo  = isset($_POST['ativo'])  ? (bool)(int)$_POST['ativo'] : true;
// Pega os parametros do form

// validando campos obrigatorios e numericos
if ($id < 1 || $titulo === '' || $genero === '' || $total < 0 || $disp < 0) {
    header('Location: ../index.php?erro=campos_invalidos');
    exit;
}

// verifica o titulo, para não permitir titulo dupliado
$stmt = $pdo->prepare("SELECT id FROM filmes WHERE LOWER(titulo) = LOWER(?)");
$stmt->execute([$titulo]);
if ($stmt->rowCount() > 0) {
    header("Location: ../index.php?erro=titulo_duplicado");
    exit;
}

// a quantidade disponivel nao pode ser maior que o total de copias
if ($disp > $total) { $disp = $total; }

// upload de nova imagem
$foto_sql  = '';
$foto_bind = [];

// confirma o envio e se não houve erro
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $mime    = mime_content_type($_FILES['foto']['tmp_name']);

    // verifica se o arquivo é de um tipo permitido
    if (in_array($mime, $allowed) && $_FILES['foto']['size'] <= 4 * 1024 * 1024) {
        
        // gera o nome unico do arquivo
        $ext     = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nome    = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destino = __DIR__ . '/../uploads/imagens/' . $nome;
        
        // move para a pasta de imagens
        move_uploaded_file($_FILES['foto']['tmp_name'], $destino);

        // atualiza as variaveis para incluir a foto na query SQL
        $foto_sql        = ', foto = :foto';
        $foto_bind[':foto'] = 'uploads/imagens/' . $nome;
    }
}

// se houver update, ele molda as novas informações
$sql = "UPDATE filmes
        SET titulo = :titulo,
            genero = :genero,
            quantidade_total = :total,
            quantidade_disponivel = :disp,
            ativo = :ativo
            $foto_sql
        WHERE id = :id";

// prepara os parametros
$stmt = $pdo->prepare($sql);

// atribui o valor para os moldes
$params = array_merge([
    ':titulo' => $titulo,
    ':genero' => $genero,
    ':total'  => $total,
    ':disp'   => $disp,
    ':ativo'  => $ativo ? 'true' : 'false',
    ':id'     => $id,
], $foto_bind);

// executa a query
$stmt->execute($params);

// exibe mensagem de sucesso
header('Location: ../index.php?ok=editado');
exit;
?>