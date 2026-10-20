<?php
    require_once '../conexao.php';
    require_once '../logger.php';
    require_once '../helpers.php';

    $erros = [];
    $id = obterId();

    $sql = 'SELECT id, codigo, descricao, categoria, marca, modelo, numero_serie, ativo, observacao, created_at 
            FROM equipamentos WHERE id = :id';
    $consulta = $pdo->prepare($sql);
    $consulta->execute([':id' => $id]);

    $equipamento = $consulta->fetch();

    if(!$equipamento){
        redirecionarErro("Equipamento não encontrado.");
    }

    $codigo = $equipamento['codigo'];
    $descricao = $equipamento['descricao'];
    $categoria = $equipamento['categoria'];
    $marca = $equipamento['marca'];
    $modelo = $equipamento['modelo'];
    $numero_serie = $equipamento['numero_serie'];
    $ativo = $equipamento['ativo'];
    $observacao = $equipamento['observacao'];

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        exigirCsrf();

        $codigo = trim($_POST["codigo"] ?? '');
        $descricao = trim($_POST["descricao"] ?? '');
        $categoria = trim($_POST["categoria"] ?? '');
        $marca = trim($_POST["marca"] ?? '');
        $modelo = trim($_POST["modelo"] ?? '');
        $numero_serie = trim($_POST["numero_serie"] ?? '');
        $ativo = isset($_POST["ativo"]) ? 1 : 0;
        $observacao = trim($_POST["observacao"] ?? '');

        $erros = validarEquipamento($codigo, $descricao, $categoria, $marca, $modelo, $numero_serie);

        if(empty($erros)){
            $sql = 'SELECT id FROM equipamentos WHERE codigo = :codigo AND id <> :id';
            $consulta = $pdo->prepare($sql);
            $consulta->execute([":codigo" => $codigo, ":id" => $id]);

            if($consulta->fetchColumn()){
                $erros[] = "Já existe um equipamento com esse código.";
            }
        }
        
        if(empty($erros)){
            try{
                $sql = 'UPDATE equipamentos SET codigo = :codigo, descricao = :descricao, categoria = :categoria, 
                        marca = :marca, modelo = :modelo, numero_serie = :numero_serie, ativo = :ativo, observacao = :observacao 
                        WHERE id = :id';
                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ":codigo" => $codigo,
                    ":descricao" => $descricao,
                    ":categoria" => $categoria,
                    ":marca" => $marca,
                    ":modelo" => $modelo,
                    ":numero_serie" => $numero_serie,
                    ":ativo" => $ativo,
                    ":observacao" => $observacao,
                    ":id" => $id
                ]);

                registrarLog("Equipamento editado: ID $id");

                definirMensagem("Equipamento atualizado com sucesso.", "sucesso");
                header("Location: listar.php");
                exit;
            }
            catch(PDOException $e){
                $erros[] = "Erro ao atualizar equipamento.";
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

<h2>Editar Equipamento</h2>

<form method="POST" class="form-grid">
    <?= campoTokenCsrf() ?>
    
    <fieldset>
        <legend>Dados do Equipamento</legend>
        <div class="grid-campos">
            <div class="campo-form">
                <label>Código:</label><br>
                <input type="text" name="codigo" value="<?= e($codigo ?? '') ?>" required><br>
            </div>

            <div class="campo-form">
                <label>Descrição:</label><br>
                <input type="text" name="descricao" value="<?= e($descricao ?? '') ?>" required><br>
            </div>

            <div class="campo-form">
                <label>Categoria:</label><br>
                <input type="text" name="categoria" value="<?= e($categoria ?? '') ?>"><br>
            </div>

            <div class="campo-form">
                <label>Marca:</label><br>
                <input type="text" name="marca" value="<?= e($marca ?? '') ?>"><br>
            </div>

            <div class="campo-form">
                <label>Modelo:</label><br>
                <input type="text" name="modelo" value="<?= e($modelo ?? '') ?>"><br>
            </div>

            <div class="campo-form">
                <label>Número de Série:</label><br>
                <input type="text" name="numero_serie" value="<?= e($numero_serie ?? '') ?>"><br>
            </div>

            <div class="campo-form">
                <label>Ativo:</label>
                <input type="checkbox" name="ativo" value="1" <?= $ativo ? 'checked' : '' ?>><br>
            </div>
        </div>
    </fieldset>
    
    <fieldset>
        <legend>Informações Adicionais</legend>
        <div class="campo">
            <label>Observação:</label><br>
            <textarea name="observacao"><?= e($observacao ?? '') ?></textarea><br><br>
        </div>
    </fieldset>

    <div class="botoes-acoes">
        <button type="submit">Salvar</button>
        <a class="botao-cancelar" href="listar.php">Cancelar</a>
    </div>
</form>

<?php require_once '../layout/footer.php'; ?>