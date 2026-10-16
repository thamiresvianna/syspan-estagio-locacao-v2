<?php
    require_once '../conexao.php';
    require_once '../helpers.php';

    $id = obterId();

    $sql = 'SELECT id, nome, descricao, ativo, created_at, updated_at FROM precos WHERE id = :id';

    $consulta = $pdo->prepare($sql);
    $consulta->execute([':id' => $id]);
    $preco = $consulta->fetch();

    if(!$preco){
        redirecionarErro("Tabela de preços não encontrada.");
    }

    $sqlItens = 'SELECT preco_itens.id, equipamentos.descricao AS equipamento, preco_itens.valor_diaria FROM preco_itens 
                INNER JOIN equipamentos ON preco_itens.id_equipamento = equipamentos.id 
                WHERE preco_itens.id_preco = :id
                ORDER BY equipamentos.descricao ASC';
    $consultaItens = $pdo->prepare($sqlItens);
    $consultaItens->execute([':id' => $id]);
    $precoItens = $consultaItens->fetchAll();

    $qtd_equipamentos = count($precoItens);

    require_once '../layout/header.php';
?>

<h2>Detalhes da Tabela de Preços <?= $preco['id'] ?></h2>

<div>
    <p><strong>Nome:</strong> <?= e($preco["nome"]) ?></p>
    <p><strong>Descrição:</strong> <?= e($preco["descricao"]) ?></p>
    <p><strong>Quantidade de equipamentos:</strong> <?= e($qtd_equipamentos) ?></p>
    <p><strong>Ativo:</strong> <?= e($preco["ativo"] ? 'Sim' : 'Não' ) ?> </p>
    <p><strong>Criado em:</strong> <?= formatarData($preco["created_at"]) ?></p>
    <p><strong>Atualizado em:</strong> <?= mostrarValor(formatarData($preco["updated_at"])) ?></p>
</div>

<br><a class="links" href="listar.php">Voltar para listagem</a>
<a class="links" href="adicionar_item.php?id=<?= (int)$preco['id'] ?>">Adicionar Item</a><br><br><hr>

<h3>Itens da Tabela</h3>

<?php if(!empty($precoItens)): ?>
    <table>
        <tr>
            <th>ID do Item</th>
            <th>Equipamento</th>
            <th>Valor da Diária</th>
            <th>Ações</th>
        </tr>
        
        <?php foreach($precoItens as $row): ?>
            <tr>
                <td><?= (int)$row["id"] ?></td>
                <td><?= e($row["equipamento"]) ?></td>
                <td>R$ <?= formatarMoeda((float)$row["valor_diaria"]) ?></td>
                <td>
                    <a class="botao-editar" href="editar_item.php?id=<?= (int)$row["id"] ?>">Editar</a>
                    <a class="botao-excluir" href="excluir_item.php?id=<?= (int)$row["id"] ?>">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>

    </table>

<?php else: ?>
    <p>Nenhum equipamento foi registrado para esta tabela de preços ainda.</p>
<?php endif; ?>

<?php require_once '../layout/footer.php'; ?>