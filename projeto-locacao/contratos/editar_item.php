<?php
    require_once '../conexao.php';
    require_once '../logger.php';
    require_once '../helpers.php';

    $erros = [];
    $id_item = obterId();

    $sql = 'SELECT contrato_itens.id, contrato_itens.id_contrato, contrato_itens.id_equipamento, contrato_itens.diaria, contrato_itens.qtd FROM contrato_itens 
            WHERE contrato_itens.id = :id';
    $consulta = $pdo->prepare($sql);
    $consulta->execute([':id' => $id_item]);

    $item = $consulta->fetch();

    if(!$item){
        redirecionarErro("Item não encontrado.");
    }

    $sql = 'SELECT id, data_inicio, data_fim FROM contratos WHERE id = :id';
    $consulta = $pdo->prepare($sql);
    $consulta->execute([':id' => $item['id_contrato']]);
    $contrato = $consulta->fetch();

    if(!$contrato){
        redirecionarErro("Contrato não encontrado.");
    }

    $status = calcularStatusContrato($contrato['data_inicio'], $contrato['data_fim']);

    if($status == "ENCERRADO"){
        redirecionarErro("Não é possível editar itens de um contrato encerrado.", "ver.php?id=" . (int)$item['id_contrato']);
    }

    $sql = 'SELECT equipamentos.id, equipamentos.descricao, preco_itens.valor_diaria FROM equipamentos
            INNER JOIN preco_itens ON preco_itens.id_equipamento = equipamentos.id
            INNER JOIN contratos ON contratos.id_preco = preco_itens.id_preco
            WHERE contratos.id = :id_contrato AND (equipamentos.ativo = 1 OR equipamentos.id = :id_equipamento)
            ORDER BY equipamentos.descricao';
    $consulta = $pdo->prepare($sql);
    $consulta->execute([':id_contrato' => $item['id_contrato'], ':id_equipamento' => $item['id_equipamento']]);

    $equipamentos = $consulta->fetchAll();

    $id_equipamento = $item['id_equipamento'];
    $qtd = $item['qtd'];

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $id_equipamento = (int) trim($_POST["id_equipamento"] ?? '');
        $qtd = (int) trim($_POST["qtd"] ?? '');

        $erros = validarContratoItem($id_equipamento, $qtd);

        if(empty($erros)){
            $sql = 'SELECT preco_itens.valor_diaria FROM preco_itens 
                    INNER JOIN contratos ON contratos.id_preco = preco_itens.id_preco
                    WHERE preco_itens.id_equipamento = :id_equipamento AND contratos.id = :id_contrato';
            $consulta = $pdo->prepare($sql);
            $consulta->execute([":id_equipamento" => $id_equipamento, ":id_contrato" => $item['id_contrato']]);
            $equipamento = $consulta->fetch();

            if(!$equipamento){
                $erros[] = "Equipamento não encontrado.";
            }
        }

        if(empty($erros)){
            $sqlCheck = 'SELECT id FROM contrato_itens WHERE id_contrato = :id_contrato AND id_equipamento = :id_equipamento AND id <> :id';
            $consultaCheck = $pdo->prepare($sqlCheck);
            $consultaCheck->execute([":id_contrato" => $item['id_contrato'], ":id_equipamento" => $id_equipamento, ":id" => $id_item]);
            $item_existente = $consultaCheck->fetchColumn();

            if($item_existente){
                $erros[] = "Este equipamento já foi adicionado ao contrato.";
            }
        }

        if(empty($erros)){
            try{
                $sql = 'UPDATE contrato_itens SET id_equipamento = :id_equipamento, diaria = :diaria, qtd = :qtd WHERE id = :id';
                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ":id_equipamento" => $id_equipamento,
                    ":diaria" => $equipamento['valor_diaria'],
                    ":qtd" => $qtd,
                    ":id" => $id_item
                ]);

                registrarLog("Item do contrato editado: ID $id_item");

                definirMensagem("Item do contrato atualizado com sucesso.", "sucesso");
                header("Location: ver.php?id=". (int)$item['id_contrato']);
                exit;
            }
            catch(PDOException $e){
                $erros[] = "Erro ao editar item do contrato.";
            }
        }
    }

    require_once '../layout/header.php';
?>

<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($erros)) {
        mostrarErros($erros);
    }
?>

<h2>Editar Item do Contrato</h2>

<form method="POST">
    <label>Equipamento:</label><br>
    <select name="id_equipamento" required>
        <option value="" disabled <?= empty($id_equipamento) ? 'selected' : '' ?>>- Selecione um equipamento -</option>
        <?php foreach ($equipamentos as $equipamento): ?>
            <option value="<?= e($equipamento['id']) ?>" <?= $id_equipamento == $equipamento['id'] ? 'selected' : '' ?>>
                <?= e($equipamento['descricao']) ?> (R$ <?= formatarMoeda($equipamento["valor_diaria"]) ?>)
            </option>
        <?php endforeach; ?>
    </select><br>

    <label>Quantidade:</label><br>
    <input type="number" name="qtd" min="1" value="<?= e($qtd ?? '') ?>" required><br>

    <button type="submit">Salvar</button>
    <a class="botao-cancelar" href="ver.php?id=<?= (int)$item['id_contrato'] ?>">Cancelar</a>
</form>

<?php require_once '../layout/footer.php'; ?>