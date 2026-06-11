<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUE FITA</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
    include "includes/header.php";
    require_once "conexao.php";

    $ok   = $_GET['ok']   ?? '';
    $erro = $_GET['erro'] ?? '';

    // Sugestão da IA. Fazer uma parte só para as mensagens que serão exibidas após ações como cadastrar, deletar, locar etc.
    $msgs_ok = [
        'cadastrado' => 'Filme cadastrado com sucesso!',
        'editado'    => 'Filme atualizado com sucesso!',
        'desativado' => 'Filme desativado.',
    ];
    $msgs_erro = [
        'campos_invalidos' => 'Preencha todos os campos obrigatórios corretamente.',
        'tipo_invalido'    => 'Tipo de imagem não permitido.',
        'arquivo_grande'   => 'A imagem não pode ultrapassar 4 MB.',
        'filme_locado'     => 'Não é possível desativar: o filme possui locação ativa.',
        'id_invalido'      => 'ID inválido.',
        'titulo_duplicado' => 'O título já existe no catálogo.',
    ];

    // Pega os filmes do banco de dados para exibir na tela em ordem alfabetica
    $filmes = $pdo->query("SELECT * FROM filmes ORDER BY titulo ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="conteudo-principal">

    <!-- Função para exibir as mensagens. Caso seja $ok, ela exibe na cor verde e mostra a $msgs_ok daquela ação-->
    <?php if ($ok && isset($msgs_ok[$ok])): ?>
        <div class="alerta alerta-ok"><?= $msgs_ok[$ok] ?></div>
    <?php endif; ?>
    
    <!-- endif -> Usado para indicar para o php onde acaba o bloco if-->
    <?php if ($erro && isset($msgs_erro[$erro])): ?>
        <div class="alerta alerta-erro"><?= $msgs_erro[$erro] ?></div>
    <?php endif; ?>

    <section class="cartao-form">
        <h1>Cadastrar Novo Filme</h1>

        <form method="POST" action="actions/cadastrar_filme.php" enctype="multipart/form-data" id="formFilme">
        <!-- form para o usuário cadastrar um novo filme -->
            <div class="form-linha">
                <div class="espaco-form">
                    <label for="titulo">Título do Filme</label>
                    <input type="text" name="titulo" id="titulo" placeholder="Ex.: Harry Potter" required>
                </div>
                <div class="espaco-form">
                    <label for="genero">Gênero</label>
                    <input type="text" name="genero" id="genero" placeholder="Ex.: Ação, Drama..." required>
                </div>
            </div>

            <div class="form-linha">
                <div class="espaco-form">
                    <label for="total">Quantidade Total</label>
                    <input type="number" name="total" id="total" min="1"
                           placeholder="Qtd. de cópias" oninput="sincronizar()" required>
                           <!-- quando o usuario digitar, a função sincronizar vai ser executada e a quantidade total e disponivel vão ser a mesma -->
                </div>
                <div class="espaco-form">
                    <label for="disponivel">Quantidade Disponível</label>
                    <input type="number" name="disponivel" id="disponivel" readonly>
                    <!-- o readonly não deixa que o usuário use essa parte, para que ele não possa alterar o valor -->
                </div>
            </div>

            <!-- radio para o usuário escolher se o filme estará ativo ou inativo -->
            <div class="espaco-form">
                <label>Status</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="ativo" value="1" checked> Ativo
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="ativo" value="0"> Inativo
                    </label>
                </div>
            </div>

            <!-- espaço para pegar fotos direto da maquina do usuário -->
            <div class="espaco-form">
                <label for="foto">Capa do Filme (imagem)</label>
                <div class="upload-area" onclick="document.getElementById('foto').click()">
                    <div id="upload-texto">Clique para escolher uma imagem (JPG, PNG, WEBP - Máx.: 4 MB)</div>
                    <img id="upload-preview" class="upload-preview" style="display:none" alt="preview">
                    <input type="file" name="foto" id="foto" accept="image/*"
                           style="display:none" onchange="previewFoto(event)">
                </div>
            </div>

            <input type="submit" value="Registrar Filme">
        </form>
    </section>
    
    <!--parte para exibir os filmes registrados -->
    <h2 class="secao-titulo">Catálogo (<?= count($filmes) ?> filmes)</h2>
    <!-- o count é para contar o total de filmes e exibir na tela -->

    <div class="grade-filmes">
        <?php foreach ($filmes as $f): ?>
            <!-- o card-filme é usado para exibir a imagem normla, caso o filme estiver ativo e se estiver inativo, aparecerá mais apagado -->
            <div class="card-filme <?= $f['ativo'] ? '' : 'inativo' ?>"
                 onclick="abrirModal(<?= htmlspecialchars(json_encode($f), ENT_QUOTES) ?>)">
                <!-- htmlspecialchars -> usado para o codigo nao quebrar caso o titulo tenha caracteres especiais -->
                <!-- json_encode -> pega todas as informações do filme (id, nome, genero) e transforma em string JSON para o java script entender -->
                <?php if (!empty($f['foto'])): ?>
                    <img class="card-capa" src="<?= htmlspecialchars($f['foto']) ?>"
                         alt="<?= htmlspecialchars($f['titulo']) ?>">
                         <!-- esse if confere se o usuario adicionou alguma foto, se sim, abre a tag img para exibi-la -->
                <?php else: ?>
                    <div class="card-capa-placeholder">🎬</div>
                <?php endif; ?>
                <!-- caso não, aparecerá apenas um emoji -->

                <div class="card-info">
                    <div class="card-titulo"><?= htmlspecialchars($f['titulo']) ?></div>
                    <div class="card-disp"><?= htmlspecialchars($f['genero']) ?></div>
                    <?php if ($f['ativo']): ?>
                        <?php if ($f['quantidade_disponivel'] > 0): ?>
                            <span class="badge-disp ok">✔ <?= $f['quantidade_disponivel'] ?> disponível</span>
                            <!-- se o filme estiver ativo, o nome, o genero e a disponibilidade irão aparecer embaixo da imagem -->
                        <?php else: ?>
                            <span class="badge-disp sem">✖ Sem estoque</span>
                            <!-- caso esteja sem estoque, aparecerá a mensagem acima -->
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge-disp sem">Inativo</span>
                        <!-- e se tiver inativo, aparecera inativo -->
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</main>

<div class="modal-overlay" id="modalOverlay" onclick="fecharModalFora(event)">
    <div class="modal-caixa">
        <button class="modal-fechar" onclick="fecharModal()">✕</button>
        <h2>Editar Filme</h2>

        <!-- form para editar as informações do filme -->

        <form method="POST" action="actions/editar_filme.php"
              enctype="multipart/form-data" id="formEditar">
            <input type="hidden" name="id" id="edit_id">

            <div class="espaco-form">
                <label for="edit_titulo">Título</label>
                <input type="text" name="titulo" id="edit_titulo" required>
            </div>

            <div class="espaco-form">
                <label for="edit_genero">Gênero</label>
                <input type="text" name="genero" id="edit_genero" required>
            </div>

            <div class="form-linha">
                <div class="espaco-form">
                    <label for="edit_total">Qtd. Total</label>
                    <input type="number" name="total" id="edit_total" min="0" required>
                </div>
                <div class="espaco-form">
                    <label for="edit_disp">Qtd. Disponível</label>
                    <input type="number" name="disp" id="edit_disp" min="0" required>
                </div>
            </div>

            <div class="espaco-form">
                <label>Status</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="ativo" id="edit_ativo_sim" value="1"> Ativo
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="ativo" id="edit_ativo_nao" value="0"> Inativo
                    </label>
                </div>
            </div>

            <div class="espaco-form">
                <label for="edit_foto">Nova Capa (opcional)</label>
                <div class="upload-area" onclick="document.getElementById('edit_foto').click()">
                    <div id="edit-upload-texto">Trocar imagem...</div>
                    <img id="edit-upload-preview" class="upload-preview" style="display:none" alt="preview">
                    <input type="file" name="foto" id="edit_foto" accept="image/*"
                           style="display:none" onchange="previewFotoEdit(event)">
                </div>
            </div>

            <div class="modal-acoes">
                <input type="submit" value="Salvar Alterações" class="btn btn-primario">

                <!-- botão para desativar o filme -->
                <button type="button" class="btn btn-perigo"
                        onclick="confirmarDesativar()">Desativar Filme</button>
            </div>
        </form>

        <!-- form separado apenas para deletar filme, mas ele não aparece para o usuario, so é executado apos o usuario confirmar que quer desativar o filme -->
        <form method="POST" action="actions/desativar_filme.php" id="formDesativar" style="display:none">
            <input type="hidden" name="id" id="desativar_id">
        </form>
    </div>
</div>

<script>
function previewFoto(e) {
    const file = e.target.files[0];
    // pega o primeiro arquivo enviado pelo usuário no input de upload de imagens
    if (!file) return;
    // se o usuário fechar a janela sem escolher nenhuma foto a função para de executar para evitar erros

    // busca no HTML a tag img onde colocará a imagem
    const img = document.getElementById('upload-preview');

    // cria uma URL temporária para o arquivo e define como a origem da imagem para usarmos na hora de exibir
    img.src = URL.createObjectURL(file);

    // block para ser exibido na tela 
    img.style.display = 'block';
    
    // busca o elemento de texto no HTML e atualiza ele com o nome original do arquivo
    document.getElementById('upload-texto').textContent = file.name;
}
function previewFotoEdit(e) {
    const file = e.target.files[0];
    if (!file) return;
    // busca a tag img onde ele vai inserir a nova imagem
    const img = document.getElementById('edit-upload-preview');
    img.src = URL.createObjectURL(file);
    img.style.display = 'block';
    
    // busca o elemento de texto no HTML e atualiza ele com o nome original do arquivo, mas desta vez no edit-upload-texto
    document.getElementById('edit-upload-texto').textContent = file.name;
}

function sincronizar() {
    // o sincronizar pega o valor digitado na quantidade total
    const t = document.getElementById('total').value;
    // e coloca esse mesmo valor na quantidade disponivel
    document.getElementById('disponivel').value = t;
}

function abrirModal(filme) {
    // função para abrir o modal de editar as informações do filme
    document.getElementById('edit_id').value          = filme.id;
    document.getElementById('edit_titulo').value      = filme.titulo;
    document.getElementById('edit_genero').value      = filme.genero;
    document.getElementById('edit_total').value       = filme.quantidade_total;
    document.getElementById('edit_disp').value        = filme.quantidade_disponivel;
    document.getElementById('desativar_id').value     = filme.id;
    // pega os valores digitados pelo usuario anteriormente

    // pega o valor do radio para definir que o filme está ativo
    const ativo = filme.ativo === true || filme.ativo === 't' || filme.ativo === '1' || filme.ativo === 1;
    document.getElementById('edit_ativo_sim').checked = ativo;
    document.getElementById('edit_ativo_nao').checked = !ativo;
    // define o ativo e o inativo

    document.getElementById('edit-upload-preview').style.display = 'none';
    document.getElementById('edit-upload-texto').textContent = 'Trocar imagem...';
    document.getElementById('edit_foto').value = '';

    document.getElementById('modalOverlay').classList.add('aberto');
}

// função para fechar o modal com o X no canto do modal
function fecharModal() {
    document.getElementById('modalOverlay').classList.remove('aberto');
}

// função para fechar o modal ao clicar fora do quadrado
function fecharModalFora(e) {
    if (e.target === document.getElementById('modalOverlay')) fecharModal();
}

// função para exibir um pop-up para o usuario confirmar se ele quer desativar o filme
function confirmarDesativar() {
    if (confirm('Deseja desativar este filme? Ele não será apagado, apenas marcado como inativo.')) {
        document.getElementById('formDesativar').submit();
    }
}
</script>

</body>
</html>
