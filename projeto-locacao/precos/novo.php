<?php
    require_once '../conexao.php';
    require_once '../logger.php';
    require_once '../helpers.php';

    $erros = [];

    $nome = '';
    $descricao = '';
    $ativo = 1;

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $nome = trim($_POST["nome"] ?? '');
        $descricao = trim($_POST["descricao"] ?? '');
        $ativo = isset($_POST["ativo"]) ? 1 : 0;
        
        $erros = validarPreco($nome, $descricao);

        if(empty($erros)){
            try{
                $sql = 'INSERT INTO precos (nome, descricao, ativo) VALUES (:nome, :descricao, :ativo)';
                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ":nome" => $nome,
                    ":descricao" => $descricao,
                    ":ativo" => $ativo
                ]);

                registrarLog("Tabela de preços cadastrada: $nome");

                definirMensagem("Tabela de preços cadastrada com sucesso.", "sucesso");
                header("Location: listar.php");
                exit;
            }
            catch(PDOException $e){
                $erros[] = "Erro ao cadastrar tabela de preços.";
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

<h2>Inserir Tabela de Preços</h2>

<form method="POST">
    <label>Nome da Tabela:</label><br>
    <input type="text" name="nome" value="<?= e($nome ?? '') ?>" required><br>

    <label>Descrição:</label><br>
    <input type="text" name="descricao" value="<?= e($descricao ?? '') ?>"><br>

    <label>Ativo:</label>
    <input type="checkbox" name="ativo" value="1" <?= $ativo ? 'checked' : '' ?>><br><br>

    <button type="submit">Salvar</button>
    <a class="botao-cancelar" href="listar.php">Cancelar</a>
</form>

<?php require_once '../layout/footer.php'; ?>