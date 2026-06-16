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

    // mensagens de confirmação e erro
    $msgs_ok = [
        'registrado' => 'Locação registrada! Estoque atualizado.',
        'atualizado' => 'Status atualizado com sucesso.',
    ];
    $msgs_erro = [
        'campos_invalidos'   => 'Preencha todos os campos obrigatórios.',
        'sem_estoque'        => 'Filme sem estoque disponível no momento.',
        'transacao_falhou'   => 'Erro interno. Tente novamente.',
        'id_invalido'        => 'ID inválido.',
        'nao_encontrado'     => 'Locação não encontrada.',
    ];

    // seleciona os filmes do banco de dados onde o o status ativo é igual a "true" para inseri-los no select do formulario
    $filmes_disp = $pdo->query(
        "SELECT id, titulo, quantidade_disponivel FROM filmes
         WHERE ativo = true ORDER BY titulo ASC"
    )->fetchAll(PDO::FETCH_ASSOC);

    // "junta" a tabela de locação e de filmes. ele busca o id do filme para achar seu verdadeiro nome e exibir na barra de selecionar os filmes
    $locacoes = $pdo->query(
        "SELECT l.*, f.titulo AS titulo_filme
         FROM locacoes l
         JOIN filmes f ON f.id = l.filme_id
         ORDER BY l.id DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

    // pega a data de hoje para colocarmos na data de locação
    $hoje = new DateTime();
?>

<main class="conteudo-principal">

    <?php if ($ok && isset($msgs_ok[$ok])): ?>
        <div class="alerta alerta-ok"><?= $msgs_ok[$ok] ?></div>
    <?php endif; ?>
    <?php if ($erro && isset($msgs_erro[$erro])): ?>
        <div class="alerta alerta-erro"><?= $msgs_erro[$erro] ?></div>
    <?php endif; ?>

    <section class="cartao-form">
        <h1>Registrar Locação</h1>

        <form method="POST" action="actions/registrar_locacao.php">

            <div class="form-linha">
                <div class="espaco-form">
                    <label for="filme_id">Filme</label>
                    <select name="filme_id" id="filme_id" required>
                        <option value="">-- Selecione um filme --</option>
                        <?php foreach ($filmes_disp as $f): ?>
                            <!-- para cada filme, exibe o nome caso ele esteja disponivel -->
                            <option value="<?= $f['id'] ?>">
                                <?= htmlspecialchars($f['titulo']) ?>
                                (<?= $f['quantidade_disponivel'] ?> disponível)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="espaco-form">
                    <label for="nome">Nome do Cliente</label>
                    <input type="text" name="nome" id="nome"
                           placeholder="Nome completo..." minlength="3" maxlength="40" required>
                </div>
            </div>

            <div class="form-linha">
                <div class="espaco-form">
                    <!-- a data de locação é preenchida automaticamente com a data atual -->
                    <label for="data_locacao">Data de Locação</label>
                    <input type="date" name="data_locacao" id="data_locacao"
                           value="<?= date('Y-m-d') ?>" readonly>
                </div>
                <div class="espaco-form">
                    <label for="data_devolucao">Data de Devolução Prevista</label>
                    <input type="date" name="data_devolucao" id="data_devolucao" 
                    value="<?php $hoje = new DateTime(); $hoje->modify('+7 days'); echo $hoje->format('Y-m-d'); ?>" readonly>
                </div>
            </div>

            <input type="submit" value="Registrar Locação">
        </form>
    </section>

    <h2 class="secao-titulo">Locações (<?= count($locacoes) ?>)</h2>

    <?php if (empty($locacoes)): ?>
        <p style="color:var(--muted); text-align:center; padding:2rem 0;">
            Nenhuma locação registrada ainda.
        </p>
        <!-- caso nao tenha nenhuma locação, so exibira a mensagem acima -->
    <?php else: ?>
        <!-- caso tenha locações, ele abrirá uma tabela para colocar as informações como nome do filme, nome do cliente etc -->
    <div class="tabela-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Filme</th>
                    <th>Cliente</th>
                    <th>Locação</th>
                    <th>Devolução Prev.</th>
                    <th>Prazo</th>
                    <th>Pagamento</th>
                    <th>Devolução</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($locacoes as $loc):
                $data_dev = new DateTime($loc['data_devolucao']);
                // pra cada locação, teremos uma data de devolução definida pelo usuario
                $atrasado = (!$loc['devolvido']) && ($hoje > $data_dev);
                // o filme fica com status de atrasado caso nao esteja devolvido e se a data atual for maior que a data de devolução
            ?>
                <tr>
                    <td><?= $loc['id'] ?></td>
                    <td><?= htmlspecialchars($loc['titulo_filme']) ?></td>
                    <td><?= htmlspecialchars($loc['nome_cliente']) ?></td>
                    <td><?= date('d/m/Y', strtotime($loc['data_locacao'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($loc['data_devolucao'])) ?></td>
                    <!-- exibe as informações na tabela -->

                    <!-- tags de prazo -->
                    <td>
                        <?php if ($loc['devolvido']): ?>
                            <span class="tag tag-devolvido">Devolvido</span>
                        <?php elseif ($atrasado): ?>
                            <span class="tag tag-atrasado">⚠ Atrasado</span>
                        <?php else: ?>
                            <span class="tag tag-prazo">No Prazo</span>
                        <?php endif; ?>
                    </td>

                    <!-- form para atualizar as informações de pagamento da locação -->
                    <td>
                        <form method="POST" action="actions/atualizar_locacao.php"
                              class="status-radio" onchange="this.submit()">
                            <input type="hidden" name="id" value="<?= $loc['id'] ?>">
                            <label>
                                <input type="radio" name="status_pagamento" value="Pendente"
                                    <?= $loc['status_pagamento'] === 'Pendente' ? 'checked' : '' ?>>
                                    <!-- confere o status do pagamento e exibe em um radio com as opções: pendente e pago -->
                                <span class="tag tag-pendente">Pendente</span>
                            </label>
                            <label>
                                <input type="radio" name="status_pagamento" value="Pago"
                                    <?= $loc['status_pagamento'] === 'Pago' ? 'checked' : '' ?>>
                                <span class="tag tag-pago">Pago</span>
                            </label>
                        </form>
                    </td>

                    <!-- form para atualizar as informações de devolução da locação -->
                    <td>
                        <form method="POST" action="actions/atualizar_locacao.php"
                              class="status-radio" onchange="this.submit()">
                            <input type="hidden" name="id" value="<?= $loc['id'] ?>">
                            <label>
                                <input type="radio" name="devolvido" value="0"
                                    <?= !$loc['devolvido'] ? 'checked' : '' ?>>
                                <span class="tag tag-ndevolvido">Não Dev.</span>
                                <!-- confere se o filme esta com status de devolvido ou nao -->
                            </label>
                            <label>
                                <input type="radio" name="devolvido" value="1"
                                    <?= $loc['devolvido'] ? 'checked' : '' ?>>
                                <span class="tag tag-devolvido">Devolvido</span>
                            </label>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</main>

</body>
</html>
