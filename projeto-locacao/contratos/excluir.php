<?php
    require_once '../conexao.php';
    require_once '../logger.php';
    require_once '../helpers.php';

    $id = obterId();

    $sql = 'SELECT id, id_cliente, id_preco, data_inicio, data_fim, status, observacao, created_at FROM contratos WHERE id = :id';
    $consulta = $pdo->prepare($sql);
    $consulta->execute([':id' => $id]);

    $contrato = $consulta->fetch();

    if(!$contrato){
        redirecionarErro("Contrato não encontrado.");
    }

    $erro = '';

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $sql = 'SELECT COUNT(*) FROM contrato_itens WHERE id_contrato = :id';
        $consulta = $pdo->prepare($sql);
        $consulta -> execute([':id' => $id]);

        $total_itens = $consulta->fetchColumn();

        if($total_itens > 0){
            $erro = "Não é possível excluir este contrato porque ele possui equipamentos vinculados.";
        } else {
            try{
                $sql = 'DELETE FROM contratos WHERE id = :id';
                $stmt = $pdo->prepare($sql);

                $stmt->execute([":id" => $id]);

                if($stmt->rowCount() === 0){
                    $erro = "Erro ao excluir contrato.";
                } else {
                    registrarLog("Contrato excluído: ID $id");

                    definirMensagem("Contrato excluído com sucesso.", "sucesso");
                    header("Location: listar.php");
                    exit;
                }
            }
            catch(PDOException $e){
                $erro = "Erro ao excluir contrato.";
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

<h2>Excluir Contrato Nº <?= numeroContrato($contrato['id']) ?></h2>

<p>Tem certeza que deseja excluir o contrato: <strong>Nº <?= str_pad($contrato['id'], 4, '0', STR_PAD_LEFT) ?></strong>?</p>

<form method="POST">
    <button type="submit">Excluir</button>
    <a class="botao-cancelar" href="listar.php">Cancelar</a>
</form>

<?php require_once '../layout/footer.php'; ?>