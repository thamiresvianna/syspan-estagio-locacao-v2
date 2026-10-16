<?php
    require_once '../conexao.php';
    require_once '../helpers.php';

    $id = obterId();

    $sql = 'SELECT id, tipo_pessoa, nome, cpf_cnpj, email, telefone, cep, endereco, numero, 
            complemento, bairro, cidade, estado, observacao, created_at,updated_at FROM clientes WHERE id = :id';
    $consulta = $pdo->prepare($sql);
    $consulta -> execute([':id' => $id]);

    $cliente = $consulta->fetch();

    if(!$cliente){
        redirecionarErro("Cliente não encontrado.");
    }

    require_once '../layout/header.php';
?>

<h2>Detalhes do Cliente <?= $cliente['id'] ?></h2>

<div>
    <p><strong>Tipo de Pessoa:</strong> <?= e($cliente["tipo_pessoa"] == 'F' ? 'Pessoa Física' : 'Pessoa Jurídica') ?></p>
    <p><strong>CPF/CNPJ:</strong> <?= e(formatarCpfCnpj($cliente["cpf_cnpj"])) ?></p>
    <p><strong>Nome:</strong> <?= e($cliente["nome"]) ?></p>
    <p><strong>E-mail:</strong> <?= e($cliente["email"]) ?></p>
    <p><strong>Telefone:</strong> <?= e(formatarTelefone($cliente["telefone"])) ?></p><br>

    <h3>Endereço</h3>
    <p><strong>CEP:</strong> <?= mostrarValor(formatarCep($cliente["cep"])) ?></p>
    <p><strong>Endereço:</strong> <?= mostrarValor($cliente["endereco"]) ?></p>
    <p><strong>Número:</strong> <?= mostrarValor($cliente["numero"]) ?></p>
    <p><strong>Complemento:</strong> <?= mostrarValor($cliente["complemento"]) ?></p>
    <p><strong>Bairro:</strong> <?= mostrarValor($cliente["bairro"]) ?></p>
    <p><strong>Cidade:</strong> <?= mostrarValor($cliente["cidade"]) ?></p>
    <p><strong>Estado:</strong> <?= mostrarValor($cliente["estado"]) ?></p><br>

    <h3>Informações Adicionais</h3>
    <p><strong>Observação:</strong> <?= mostrarValor($cliente["observacao"]) ?></p>
    <p><strong>Cadastrado em:</strong> <?= formatarData($cliente["created_at"]) ?></p>
    <p><strong>Atualizado em:</strong> <?= mostrarValor(formatarData($cliente["updated_at"])) ?></p>
</div>

<br><a class="links" href="listar.php">Voltar</a>

<?php require_once '../layout/footer.php'; ?>