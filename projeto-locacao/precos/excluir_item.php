<?php
    require_once '../conexao.php';
    require_once '../logger.php';
    require_once '../helpers.php';

    $id_item = obterId();

    $sql = 'SELECT preco_itens.id_preco, preco_itens.id_equipamento, equipamentos.descricao FROM preco_itens 
            INNER JOIN equipamentos ON preco_itens.id_equipamento = equipamentos.id 
            WHERE preco_itens.id = :id';
    $consulta = $pdo->prepare($sql);
    $consulta->execute([':id' => $id_item]);

    $item = $consulta->fetch();

    if(!$item){
        redirecionarErro("Item não encontrado.");
    }

    $erro = '';

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $sql = 'SELECT COUNT(*) FROM contrato_itens WHERE id_equipamento = :id_equipamento';
        $consulta = $pdo->prepare($sql);
        $consulta -> execute([':id_equipamento' => $item['id_equipamento']]);

        $equipamento_usado = $consulta->fetchColumn();

        if($equipamento_usado > 0){
            $erro = "Não é possível excluir este equipamento porque ele já possui contratos vinculados.";
        } else {
            try{
                $sql = 'DELETE FROM preco_itens WHERE id = :id';
                $stmt = $pdo->prepare($sql);

                $stmt->execute([":id" => $id_item]);

                if($stmt->rowCount() === 0){
                    $erro = "Erro ao excluir item.";
                } else {
                    registrarLog("Item da tabela de preços excluído: ID $id_item");

                    definirMensagem("Item da tabela de preços excluído com sucesso.", "sucesso");
                    header("Location: ver.php?id=" . (int)$item['id_preco']);
                    exit;
                }
            }
            catch(PDOException $e){
                $erro = "Erro ao excluir item.";
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

<h2>Excluir Equipamento da Tabela de Preços</h2>

<p>Tem certeza que deseja excluir o item: <strong><?= e($item["descricao"]) ?></strong> da tabela de preços?</p>

<form method="POST">
    <button type="submit">Excluir</button>
    <a class="botao-cancelar" href="ver.php?id=<?= (int)$item['id_preco'] ?>">Cancelar</a>
</form>

<?php require_once '../layout/footer.php'; ?>