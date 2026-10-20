<?php
    require_once '../conexao.php';
    require_once '../logger.php';
    require_once '../helpers.php';

    $id = obterId();

    $sql = 'SELECT id, nome FROM clientes WHERE id = :id';
    $consulta = $pdo->prepare($sql);
    $consulta -> execute([':id' => $id]);

    $cliente = $consulta->fetch();

    if(!$cliente){
        redirecionarErro("Cliente não encontrado.");
    }

    $erro = '';

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        exigirCsrf();
        
        $sql = 'SELECT COUNT(*) FROM contratos WHERE id_cliente = :id';
        $consulta = $pdo->prepare($sql);
        $consulta -> execute([':id' => $id]);

        $total_contratos = $consulta->fetchColumn();

        if($total_contratos > 0){
            $erro = "Não é possível excluir este cliente porque ele possui contratos vinculados.";
        } else {
            try{
                $sql = 'DELETE FROM clientes WHERE id = :id';
                $stmt = $pdo->prepare($sql);

                $stmt->execute([":id" => $id]);

                if($stmt->rowCount() === 0){
                    $erro = "Erro ao excluir cliente.";
                } else {
                    registrarLog("Cliente excluído: ID $id");

                    definirMensagem("Cliente excluído com sucesso.", "sucesso");
                    header("Location: listar.php");
                    exit;
                }
            }
            catch(PDOException $e){
                $erro = "Erro ao excluir cliente.";
            }
        }
    }

    require_once '../layout/header.php';
?>

<?php 
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($erro)){
        mostrarErros([$erro]);
    }
?>

<h2>Excluir Cliente</h2>

<p>Tem certeza que deseja excluir o cliente: <strong><?= e($cliente["nome"]) ?></strong>?</p>

<form method="POST">
    <?= campoTokenCsrf() ?>
    
    <button type="submit">Excluir</button>
    <a class="botao-cancelar" href="listar.php">Cancelar</a>
</form>

<?php require_once '../layout/footer.php'; ?>