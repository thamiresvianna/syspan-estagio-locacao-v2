<?php
    require_once '../conexao.php';
    require_once '../logger.php';
    require_once '../helpers.php';

    $id = obterId();

    $sql = 'SELECT id, nome, descricao, ativo, created_at FROM precos WHERE id = :id';
    $consulta = $pdo->prepare($sql);
    $consulta->execute([':id' => $id]);

    $precos = $consulta->fetch();

    if(!$precos){
        redirecionarErro("Tabela de preços não encontrada.");
    }

    $erro = '';

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $sql = 'SELECT COUNT(*) FROM preco_itens WHERE id_preco = :id';
        $consulta = $pdo->prepare($sql);
        $consulta -> execute([':id' => $id]);

        $total_itens = $consulta->fetchColumn();

        if($total_itens > 0){
            $erro = "Não é possível excluir esta tabela de preços porque ela possui equipamentos vinculados.";
        } else {
            try{
                $sql = 'DELETE FROM precos WHERE id = :id';
                $stmt = $pdo->prepare($sql);

                $stmt->execute([":id" => $id]);

                if($stmt->rowCount() === 0){
                    $erro = "Erro ao excluir tabela de preços.";
                } else {
                    registrarLog("Tabela de preços excluída: ID $id");

                    definirMensagem("Tabela de preços excluída com sucesso.", "sucesso");
                    header("Location: listar.php");
                    exit;
                }
            }
            catch(PDOException $e){
                $erro = "Erro ao excluir tabela de preços.";
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

<h2>Excluir Tabela de Preços</h2>

<p>Tem certeza que deseja excluir a tabela de preços: <strong><?= e($precos["nome"]) ?></strong>?</p>

<form method="POST">
    <button type="submit">Excluir</button>
    <a class="botao-cancelar" href="listar.php">Cancelar</a>
</form>

<?php require_once '../layout/footer.php'; ?>