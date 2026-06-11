<?php
// php para cadastrar filmes
require_once '../conexao.php';

$titulo = trim($_POST['titulo'] ?? '');
$genero = trim($_POST['genero'] ?? '');
$total  = (int)($_POST['total']  ?? 0);
$ativo  = isset($_POST['ativo']) ? (int)$_POST['ativo'] : 1;
// pega todos os parametros do form

// validação -> campos obrigatórios não podem ser vazios e o estoque inicial deve ser de pelo menos 1
if ($titulo === '' || $genero === '' || $total < 1) {
    header('Location: ../index.php?erro=campos_invalidos');
    exit;
}

// verifica se o titulo já existe no banco de dados
$stmt = $pdo->prepare("SELECT id FROM filmes WHERE LOWER(titulo) = LOWER(?)");
// usa o "lower" para deixar tudo minusculo para não deixar passar um titulo igual, mas em maiusculo
$stmt->execute([$titulo]);

if ($stmt->rowCount() > 0) {
    header("Location: ../index.php?erro=titulo_duplicado");
    exit;
}

// upload de imagem

// começa nula
$foto_path = null;
    // verifica se o arquivo foi enviado e se não ocorreu nenhum erro
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    // define os tipos de imagens possiveis de serem enviados
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $mime    = mime_content_type($_FILES['foto']['tmp_name']);

    // se o tipo do arquivo não estiver no array de permitidos, não aceita o upload
    if (!in_array($mime, $allowed)) {
        header('Location: ../index.php?erro=tipo_invalido');
        exit;
    }
    //  limita o tamanho da imagem em ate 4MB
    if ($_FILES['foto']['size'] > 4 * 1024 * 1024) {
        header('Location: ../index.php?erro=arquivo_grande');
        exit;
    }

    // extrai a extensão do arquivo original
    $ext       = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    // cria um nome único para o arquivo usando o timestamp atual e uma string aleatória para evitar arquivos com o mesmo nome
    $nome      = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    // define o caminho onde a imagem vai ser armazenada
    $destino   = __DIR__ . '/../uploads/imagens/' . $nome;
    // move a imagem para a pasta
    move_uploaded_file($_FILES['foto']['tmp_name'], $destino);
    // salva o caminho em uma variavel
    $foto_path = 'uploads/imagens/' . $nome;
}

// insere no banco de dados moldes para receberem os valores reais
$sql = "INSERT INTO filmes (titulo, genero, quantidade_total, quantidade_disponivel, ativo, foto)
        VALUES (:titulo, :genero, :total, :total, :ativo, :foto)";

$stmt = $pdo->prepare($sql);

// substitui os moldes pelos valores 
$stmt->execute([
    ':titulo'  => $titulo,
    ':genero'  => $genero,
    ':total'   => $total,
    ':ativo'   => $ativo ? 'true' : 'false',
    ':foto'    => $foto_path,
]);


// exibe mensagem de sucesso
header('Location: ../index.php?ok=cadastrado');
exit;
