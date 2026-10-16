<?php
    require_once '../conexao.php';
    require_once '../helpers.php';

    $id = obterId();

    $sql = 'SELECT contratos.id, clientes.nome AS cliente, clientes.tipo_pessoa, clientes.cpf_cnpj, clientes.telefone, 
            precos.nome AS tabela_preco, contratos.data_inicio, contratos.data_fim, 
            contratos.status, contratos.observacao, contratos.created_at, contratos.updated_at FROM contratos 
            INNER JOIN clientes ON contratos.id_cliente = clientes.id
            LEFT JOIN precos ON contratos.id_preco = precos.id
            WHERE contratos.id = :id';

    $consulta = $pdo->prepare($sql);
    $consulta->execute([':id' => $id]);
    $contrato = $consulta->fetch();

    if(!$contrato){
        redirecionarErro("Contrato não encontrado.");
    }

    $status_atual = calcularStatusContrato($contrato['data_inicio'], $contrato['data_fim']);

    $total_dias = calcularTotalDias($contrato['data_inicio'], $contrato['data_fim']);

    $sqlItens = 'SELECT contrato_itens.id, contrato_itens.id_contrato, equipamentos.descricao AS equipamento, contrato_itens.diaria, contrato_itens.qtd
                FROM contrato_itens INNER JOIN equipamentos ON contrato_itens.id_equipamento = equipamentos.id
                WHERE contrato_itens.id_contrato = :id
                ORDER BY equipamentos.descricao ASC';
    $consultaItens = $pdo->prepare($sqlItens);
    $consultaItens->execute([':id' => $id]);
    $contratoItens = $consultaItens->fetchAll();

    require_once '../layout/header.php';
?>

<h2>Contrato Nº <?= numeroContrato($contrato['id']) ?></h2>

<div>
    <h3>Cliente</h3>
    <p><strong>Nome:</strong> <?= e($contrato["cliente"]) ?></p>
    <p><strong><?= e($contrato["tipo_pessoa"] == 'F' ? 'CPF:' : 'CNPJ:') ?></strong> <?= e(formatarCpfCnpj($contrato["cpf_cnpj"])) ?></p>
    <p><strong>Telefone:</strong> <?= e(formatarTelefone($contrato["telefone"])) ?></p><br>

    <h3>Período</h3>
    <p><?= formatarDataSimples($contrato["data_inicio"]) ?> até <?= formatarDataSimples($contrato["data_fim"]) ?> </p>
    <p><strong>Duração:</strong> <?= $total_dias ?> dias</p>
    <p><strong>Status:</strong> <span class="status <?= strtolower($status_atual) ?>"><?= e($status_atual) ?></span></p>
    <p><strong>Tabela de Preços:</strong> <?= mostrarValor($contrato["tabela_preco"]) ?></p><br>

    <h3>Informações Adicionais</h3>
    <p><strong>Observação:</strong> <?= mostrarValor($contrato["observacao"]) ?></p>
    <p><strong>Cadastrado em:</strong> <?= formatarData($contrato["created_at"]) ?></p>    
    <p><strong>Atualizado em:</strong> <?= mostrarValor(formatarData($contrato["updated_at"])) ?></p>
</div>

<br><a class="links" href="listar.php">Voltar para listagem</a>
<a class="links" href="adicionar_item.php?id=<?= (int)$contrato['id'] ?>">Adicionar Equipamento</a><br><br><hr>

<h3>Itens do Contrato</h3>

<?php if(!empty($contratoItens)): ?>
    <table>
        <tr>
            <th>ID do Item</th>
            <th>Equipamento</th>
            <th>Valor Diária Unitária</th>
            <th>Quantidade</th>
            <th>Subtotal por Dia</th>
            <th>Total do Período</th>
            <th>Ações</th>
        </tr>

        <?php
            $valor_total = 0;
            foreach($contratoItens as $row): 
                $subtotal = $row["diaria"] * $row["qtd"];
                $total_periodo = $subtotal * $total_dias;
                $valor_total += $total_periodo;
        ?>
            <tr>
                <td><?= (int)$row["id"] ?></td>
                <td><?= e($row["equipamento"]) ?></td>
                <td>R$ <?= formatarMoeda($row["diaria"]) ?></td>
                <td><?= (int)$row["qtd"] ?></td>
                <td>R$ <?= formatarMoeda($subtotal) ?></td>
                <td>R$ <?= formatarMoeda($total_periodo) ?></td>
                <td>
                    <a class="botao-editar" href="editar_item.php?id=<?= (int)$row["id"] ?>">Editar</a>
                    <a class="botao-excluir" href="excluir_item.php?id=<?= (int)$row["id"] ?>">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="total-contrato">
        <strong>Total do Contrato:</strong>
        <span>R$ <?= formatarMoeda($valor_total) ?></span>
    </div>

<?php else: ?>
    <p>Nenhum equipamento foi registrado para este contrato ainda.</p>
<?php endif; ?>

<?php require_once '../layout/footer.php'; ?>