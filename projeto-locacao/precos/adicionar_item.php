<?php
    require_once '../conexao.php';
    require_once '../logger.php';
    require_once '../helpers.php';

    $id_preco = obterId();

    $sql = 'SELECT id FROM precos WHERE id = :id';
    $consulta = $pdo->prepare($sql);
    $consulta->execute([':id' => $id_preco]);
    $preco = $consulta->fetch();

    if(!$preco){
        redirecionarErro("Tabela de preços não encontrada.");
    }

    $sql = 'SELECT id, descricao FROM equipamentos WHERE ativo = 1 ORDER BY descricao ASC';
    $consulta = $pdo->prepare($sql);
    $consulta->execute();
    $equipamentos = $consulta->fetchAll();

    $erros = [];

    $id_equipamento = '';
    $valor_diaria = 1.00;

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $id_equipamento = (int) trim($_POST["id_equipamento"] ?? '');
        $valor_diaria = normalizarDecimal((string)($_POST["valor_diaria"] ?? '0'));
        $valor_diaria = (float) $valor_diaria;

        $erros = validarPrecoItem($id_equipamento, $valor_diaria);

        $sql = 'SELECT COUNT(*) FROM preco_itens WHERE id_preco = :id_preco AND id_equipamento = :id_equipamento';
        $consulta = $pdo->prepare($sql);
        $consulta->execute([":id_preco" => $id_preco, ":id_equipamento" => $id_equipamento]);

        if($consulta->fetchColumn() > 0){
            $erros[] = "Este equipamento já foi adicionado à tabela de preços.";
        }

        if(empty($erros)){
            $sql = 'SELECT id FROM equipamentos WHERE id = :id AND ativo = 1';
            $consulta = $pdo->prepare($sql);
            $consulta->execute([":id" => $id_equipamento]);
            $equipamento = $consulta->fetch();

            if(!$equipamento){
                $erros[] = "Equipamento não encontrado ou inativo.";
            } else {
                try{
                    $sql = 'INSERT INTO preco_itens (id_preco, id_equipamento, valor_diaria) 
                            VALUES (:id_preco, :id_equipamento, :valor_diaria)';
                    $stmt = $pdo->prepare($sql);

                    $stmt->execute([
                        ":id_preco" => $id_preco,
                        ":id_equipamento" => $id_equipamento,
                        ":valor_diaria" => $valor_diaria
                    ]);

                    registrarLog("Item $id_equipamento adicionado a tabela de preços: $id_preco");

                    definirMensagem("Equipamento adicionado à tabela de preços com sucesso.", "sucesso");
                    header("Location: ver.php?id=" . (int)$id_preco);
                    exit;
                }
                catch(PDOException $e){
                    $erros[] = "Erro ao adicionar equipamento à tabela de preços.";
                }
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

<h2>Adicionar Item a Tabela de Preços <?= $id_preco ?></h2>

<form method="POST">
    <label>Equipamento:</label><br>
    <select name="id_equipamento" required>
        <option value="" disabled <?= empty($id_equipamento) ? 'selected' : '' ?>>- Selecione um equipamento -</option>
        <?php foreach ($equipamentos as $equipamento): ?>
            <option value="<?= e($equipamento['id']) ?>" <?= $id_equipamento == $equipamento['id'] ? 'selected' : '' ?>>
                <?= e($equipamento['descricao']) ?>
            </option>
        <?php endforeach; ?>
    </select><br>

    <label>Valor da Diária:</label><br>
    <input type="number" name="valor_diaria" step="0.01" min="0.01" value="<?= e(formatarInputDecimal($valor_diaria)) ?>" required><br>

    <button type="submit">Salvar</button>
    <a class="botao-cancelar" href="ver.php?id=<?= (int)$id_preco ?>">Cancelar</a>
</form>

<?php require_once '../layout/footer.php'; ?>