<?php
    require_once '../conexao.php';
    require_once '../logger.php';
    require_once '../helpers.php';

    $sql = 'SELECT id, nome FROM clientes ORDER BY nome ASC';
    $consulta = $pdo->prepare($sql);
    $consulta->execute();

    $clientes = $consulta->fetchAll();

    $sql = 'SELECT id, nome FROM precos WHERE ativo = 1 ORDER BY nome ASC';
    $consulta = $pdo->prepare($sql);
    $consulta->execute();
    $precos = $consulta->fetchAll();

    $erros = [];

    $id_cliente = '';
    $id_preco = '';
    $data_inicio = '';
    $data_fim = '';
    $observacao = '';

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
            $consulta->execute([':id' => $id_cliente]);

            if(!$consulta->fetch()){
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

        $erros = array_merge($erros, validarContrato($data_inicio, $data_fim));

        if(empty($erros)){
            try{
                $status = calcularStatusContrato($data_inicio, $data_fim);

                $sql = 'INSERT INTO contratos (id_cliente, id_preco, data_inicio, data_fim, status, observacao) 
                        VALUES (:id_cliente, :id_preco, :data_inicio, :data_fim, :status, :observacao)';
                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ":id_cliente" => $id_cliente,
                    ":id_preco" => $id_preco,
                    ":data_inicio" => $data_inicio,
                    ":data_fim" => $data_fim,
                    ":status" => $status,
                    ":observacao" => $observacao
                ]);

                $id_contrato = $pdo->lastInsertId();
                registrarLog("Contrato criado: ID $id_contrato");

                definirMensagem("Contrato cadastrado com sucesso.", "sucesso");
                header("Location: listar.php");
                exit;
            }
            catch(PDOException $e){
                $erros[] = "Erro ao cadastrar contrato.";
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

<h2>Novo Contrato</h2>

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
    <a class="botao-cancelar" href="listar.php">Cancelar</a>
</form>

<?php require_once '../layout/footer.php'; ?>