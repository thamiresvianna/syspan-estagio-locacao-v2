<?php
    require_once '../conexao.php';
    require_once '../logger.php';
    require_once '../helpers.php';

    $id = obterId();

    $sql = 'SELECT id, codigo, descricao, categoria, marca, modelo, numero_serie, ativo, observacao, created_at 
            FROM equipamentos WHERE id = :id';
    $consulta = $pdo->prepare($sql);
    $consulta->execute([':id' => $id]);

    $equipamento = $consulta->fetch();

    if(!$equipamento){
        redirecionarErro("Equipamento não encontrado.");
    }

    $erro = '';

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        exigirCsrf();

        $sql = 'SELECT (SELECT COUNT(*) FROM contrato_itens WHERE id_equipamento = :id1) +
                       (SELECT COUNT(*) FROM preco_itens WHERE id_equipamento = :id2)';
        $consulta = $pdo->prepare($sql);
        $consulta -> execute([':id1' => $id, ':id2' => $id]);

        $total_itens = $consulta->fetchColumn();

        if($total_itens > 0){
            $erro = "Não é possível excluir este equipamento porque ele possui contratos ou preços vinculados.";
        } else {
            try{
                $sql = 'DELETE FROM equipamentos WHERE id = :id';
                $stmt = $pdo->prepare($sql);

                $stmt->execute([":id" => $id]);

                if($stmt->rowCount() === 0){
                    $erro = "Erro ao excluir equipamento.";
                } else {
                    registrarLog("Equipamento excluído: ID $id");

                    definirMensagem("Equipamento excluído com sucesso.", "sucesso");
                    header("Location: listar.php");
                    exit;
                }
            }
            catch(PDOException $e){
                $erro = "Erro ao excluir equipamento.";
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

<h2>Excluir Equipamento</h2>

<p>Tem certeza que deseja excluir o equipamento: <strong><?= e($equipamento["descricao"]) ?></strong>?</p>

<form method="POST">
    <?= campoTokenCsrf() ?>
    
    <button type="submit">Excluir</button>
    <a class="botao-cancelar" href="listar.php">Cancelar</a>
</form>

<?php require_once '../layout/footer.php'; ?>