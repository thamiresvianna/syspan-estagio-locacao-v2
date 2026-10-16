<?php
    require_once '../conexao.php';
    require_once '../logger.php';
    require_once '../helpers.php';

    $erros = [];
    $id = obterId();

    $sql = 'SELECT id, id_cliente, id_preco, data_inicio, data_fim, status, observacao, created_at FROM contratos WHERE id = :id';
    $consulta = $pdo->prepare($sql);
    $consulta -> execute([':id' => $id]);

    $contrato = $consulta->fetch();

    if(!$contrato){
        redirecionarErro("Contrato não encontrado.");
    }

    $sql = 'SELECT id, nome FROM clientes ORDER BY nome ASC';
    $consulta = $pdo->prepare($sql);
    $consulta->execute();

    $clientes = $consulta->fetchAll();

    $sql = 'SELECT id, nome FROM precos WHERE ativo = 1 OR id = :id_preco ORDER BY nome ASC';
    $consulta = $pdo->prepare($sql);
    $consulta->execute([':id_preco' => $contrato['id_preco']]);
    $precos = $consulta->fetchAll();

    $id_cliente = $contrato['id_cliente'];
    $id_preco = $contrato['id_preco'];
    $data_inicio = $contrato['data_inicio'];
    $data_fim = $contrato['data_fim'];
    $observacao = $contrato['observacao'];

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $id_cliente = (int) trim($_POST["id_cliente"] ?? '');
        $id_preco = (int) trim($_POST["id_preco"] ?? '');
        $data_inicio = trim($_POST["data_inicio"] ?? '');
        $data_fim = trim($_POST["data_fim"] ?? '');
        $observacao = trim($_POST["observacao"] ?? '');

        if($id_cliente <= 0){
            $erros[] = "Cliente inválido.";
        } else {
            $sql = 'SELECT id FROM clientes WHERE id = :id';
            $consulta = $pdo->prepare($sql);
            $consulta->execute([":id" => $id_cliente]);

            if(!$consulta->fetchColumn()){
                $erros[] = "Cliente não encontrado.";
            }
        }
        if($id_preco <= 0){
            $erros[] = "Preço inválido.";
        } else {
            $sql = 'SELECT id FROM precos WHERE id = :id';
            $consulta = $pdo->prepare($sql);
            $consulta->execute([":id" => $id_preco]);

            if(!$consulta->fetchColumn()){
                $erros[] = "Tabela de preços não encontrada.";
            }
        }

        if(empty($erros)){
            $erros = array_merge($erros, validarContrato($data_inicio, $data_fim));

            if($id_preco != $contrato['id_preco']){
                $sql = 'SELECT COUNT(*) FROM contrato_itens WHERE id_contrato = :id';
                $consulta = $pdo->prepare($sql);
                $consulta->execute([":id" => $id]);

                if($consulta->fetchColumn() > 0){
                    $erros[] = "Não é possível alterar a tabela de preços após adicionar equipamentos ao contrato.";
                }
            }
        }
        
        if(empty($erros)){
            try{
                $status = calcularStatusContrato($data_inicio, $data_fim);

                $sql = 'UPDATE contratos SET id_cliente = :id_cliente, id_preco = :id_preco, data_inicio = :data_inicio, 
                        data_fim = :data_fim, status = :status, observacao = :observacao WHERE id = :id';
                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ":id_cliente" => $id_cliente,
                    ":id_preco" => $id_preco,
                    ":data_inicio" => $data_inicio,
                    ":data_fim" => $data_fim,
                    ":status" => $status,
                    ":observacao" => $observacao,
                    ":id" => $id
                ]);

                registrarLog("Contrato editado: ID $id");

                definirMensagem("Contrato atualizado com sucesso.", "sucesso");
                header("Location: ver.php?id=" . (int)$id);
                exit;
            }
            catch(PDOException $e){
                $erros[] = "Erro ao atualizar contrato.";
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

<h2>Editar Contrato Nº <?= numeroContrato($contrato['id']) ?></h2>

<form method="POST">
    <label>Cliente:</label><br>
    <select name="id_cliente" required>
        <option value="" disabled <?= empty($id_cliente) ? 'selected' : '' ?>>- Selecione um cliente -</option>
        <?php foreach ($clientes as $cliente): ?>
            <option value="<?= e($cliente['id']) ?>" <?= $id_cliente == $cliente['id'] ? 'selected' : '' ?>>
                <?= e($cliente['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select><br>

    <label>Data de Início:</label><br>
    <input type="date" name="data_inicio" value="<?= e($data_inicio ?? '') ?>" required><br>

    <label>Data de Fim:</label><br>
    <input type="date" name="data_fim" value="<?= e($data_fim ?? '') ?>" required><br>

    <label>Preço:</label><br>
    <select name="id_preco" required>
        <option value="" disabled <?= empty($id_preco) ? 'selected' : '' ?>>- Selecione um preço -</option>
        <?php foreach ($precos as $preco): ?>
            <option value="<?= e($preco['id']) ?>" <?= $id_preco == $preco['id'] ? 'selected' : '' ?>>
                <?= e($preco['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select><br>

    <label>Observação:</label><br>
    <textarea name="observacao"><?= e($observacao ?? '') ?></textarea><br>

    <button type="submit">Salvar</button>
    <a class="botao-cancelar" href="ver.php?id=<?= (int)$id ?>">Cancelar</a>
</form>

<?php require_once '../layout/footer.php'; ?>