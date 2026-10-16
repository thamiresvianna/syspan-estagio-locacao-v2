<?php
    require_once '../conexao.php';
    require_once '../logger.php';
    require_once '../helpers.php';

    $erros = [];

    $codigo = '';
    $descricao = '';
    $categoria = '';
    $marca = '';
    $modelo = '';
    $numero_serie = '';
    $ativo = 1;
    $observacao = '';

    if($_SERVER["REQUEST_METHOD"] == "POST"){
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
            $sql = 'SELECT id FROM equipamentos WHERE codigo = :codigo';
            $consulta = $pdo->prepare($sql);
            $consulta->execute([":codigo" => $codigo]);

            if($consulta->fetchColumn()){
                $erros[] = "Já existe um equipamento com esse código.";
            }
        }

        if(empty($erros)){
            try{
                $sql = 'INSERT INTO equipamentos (codigo, descricao, categoria, marca, modelo, numero_serie, ativo, observacao) 
                VALUES (:codigo, :descricao, :categoria, :marca, :modelo, :numero_serie, :ativo, :observacao)';
                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ":codigo" => $codigo,
                    ":descricao" => $descricao,
                    ":categoria" => $categoria,
                    ":marca" => $marca,
                    ":modelo" => $modelo,
                    ":numero_serie" => $numero_serie,
                    ":ativo" => $ativo,
                    ":observacao" => $observacao
                ]);

                registrarLog("Equipamento cadastrado: $descricao");

                definirMensagem("Equipamento cadastrado com sucesso.", "sucesso");
                header("Location: listar.php");
                exit;
            }
            catch(PDOException $e){
                $erros[] = "Erro ao cadastrar equipamento.";
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

<h2>Inserir Equipamento</h2>

<form method="POST" class="form-grid">
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