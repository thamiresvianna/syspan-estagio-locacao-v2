<?php
    require_once '../conexao.php';
    require_once '../helpers.php';

    $id = obterId();

    $sql = 'SELECT id, codigo, descricao, categoria, marca, modelo, numero_serie, ativo, observacao, created_at, updated_at 
            FROM equipamentos WHERE id = :id';
    $consulta = $pdo->prepare($sql);
    $consulta -> execute([':id' => $id]);

    $equipamento = $consulta->fetch();

    if(!$equipamento){
        redirecionarErro("Equipamento não encontrado.");
    }

    require_once '../layout/header.php';
?>

<h2>Detalhes do Equipamento <?= $equipamento['id'] ?></h2>

<div>
    <p><strong>Código:</strong> <?= e($equipamento["codigo"]) ?></p>
    <p><strong>Descrição:</strong> <?= e($equipamento["descricao"]) ?></p>
    <p><strong>Categoria:</strong> <?= mostrarValor($equipamento["categoria"]) ?></p>
    <p><strong>Marca:</strong> <?= mostrarValor($equipamento["marca"]) ?></p>
    <p><strong>Modelo:</strong> <?= mostrarValor($equipamento["modelo"]) ?></p>
    <p><strong>Número de Série:</strong> <?= mostrarValor($equipamento["numero_serie"]) ?></p>
    <p><strong>Ativo:</strong> <?= e($equipamento["ativo"] ? 'Sim' : 'Não') ?></p><br>

    <h3>Informações Adicionais</h3>
    <p><strong>Observação:</strong> <?= mostrarValor($equipamento["observacao"]) ?></p>
    <p><strong>Cadastrado em:</strong> <?= formatarData($equipamento["created_at"]) ?></p>
    <p><strong>Atualizado em:</strong> <?= mostrarValor(formatarData($equipamento["updated_at"])) ?></p>
</div>

<br><a class="links" href="listar.php">Voltar</a>

<?php require_once '../layout/footer.php'; ?>