<?php
    require_once '../conexao.php';
    require_once '../logger.php';
    require_once '../helpers.php';

    $id_contrato = obterId();

    $sql = 'SELECT id, id_preco, data_inicio, data_fim FROM contratos WHERE id = :id';
    $consulta = $pdo->prepare($sql);
    $consulta->execute([':id' => $id_contrato]);
    $contrato = $consulta->fetch();

    if(!$contrato){
        redirecionarErro("Contrato não encontrado.");
    }

    $status = calcularStatusContrato($contrato['data_inicio'], $contrato['data_fim']);

    if($status == "ENCERRADO"){
        redirecionarErro("Não é possível adicionar equipamentos em um contrato encerrado.", "ver.php?id=" . (int)$id_contrato);
    }

    if(empty($contrato['id_preco'])){
        redirecionarErro("Contrato não possui tabela de preços vinculada.", "ver.php?id=" . (int)$id_contrato);
    }

    $sql = 'SELECT equipamentos.id, equipamentos.descricao, preco_itens.valor_diaria 
            FROM equipamentos INNER JOIN preco_itens ON equipamentos.id = preco_itens.id_equipamento
            WHERE equipamentos.ativo = 1 AND preco_itens.id_preco = :id_preco
            ORDER BY equipamentos.descricao';
    $consulta = $pdo->prepare($sql);
    $consulta->execute([':id_preco' => $contrato['id_preco']]);
    $equipamentos = $consulta->fetchAll();

    $diarias_equipamentos = [];

    foreach($equipamentos as $equipamento){
        $diarias_equipamentos[$equipamento['id']] = $equipamento['valor_diaria'];
    }

    $erros = [];

    $id_equipamento = '';
    $qtd = 1;

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $id_equipamento = (int) trim($_POST["id_equipamento"] ?? '');
        $qtd = (int) trim($_POST["qtd"] ?? '');

        $erros = validarContratoItem($id_equipamento, $qtd);

        if(empty($erros)){
            if(!isset($diarias_equipamentos[$id_equipamento])){
                $erros[] = "Este equipamento não possui preço cadastrado nesta tabela.";
            }
        }

        if(empty($erros)){
            try {
                $sqlCheck = 'SELECT id, qtd FROM contrato_itens WHERE id_contrato = :id_contrato AND id_equipamento = :id_equipamento';
                $consultaCheck = $pdo->prepare($sqlCheck);
                $consultaCheck->execute([":id_contrato" => $id_contrato, ":id_equipamento" => $id_equipamento]);
                $item_existente = $consultaCheck->fetch();

                if($item_existente){
                    $sql = 'UPDATE contrato_itens SET qtd = qtd + :qtd, diaria = :diaria WHERE id = :id';
                    $stmt = $pdo->prepare($sql);

                    $stmt->execute([
                        ":qtd" => $qtd,
                        ":diaria" => $diarias_equipamentos[$id_equipamento],
                        ":id" => $item_existente['id']
                    ]);

                    registrarLog("Quantidade do item $id_equipamento atualizado ao contrato: $id_contrato");
                } else {
                    $diaria = $diarias_equipamentos[$id_equipamento];

                    $sql = 'INSERT INTO contrato_itens (id_contrato, id_equipamento, diaria, qtd) 
                            VALUES (:id_contrato, :id_equipamento, :diaria, :qtd)';
                    $stmt = $pdo->prepare($sql);

                    $stmt->execute([
                        ":id_contrato" => $id_contrato,
                        ":id_equipamento" => $id_equipamento,
                        ":diaria" => $diaria,
                        ":qtd" => $qtd
                    ]);

                    registrarLog("Item $id_equipamento adicionado ao contrato: $id_contrato");
                }

                definirMensagem("Equipamento adicionado ao contrato com sucesso.", "sucesso");
                header("Location: ver.php?id=" . (int)$id_contrato);
                exit;
            }
            catch(PDOException $e){
                $erros[] = "Erro ao cadastrar equipamento ao contrato.";
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

<h2>Adicionar Item ao Contrato Nº <?= numeroContrato($contrato['id']) ?></h2>

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
    <a class="botao-cancelar" href="ver.php?id=<?= (int)$id_contrato ?>">Cancelar</a>
</form>

<?php require_once '../layout/footer.php'; ?>