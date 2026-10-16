<?php require_once __DIR__ . '/../helpers.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Mini Locação Syspan</title>

        <link rel="stylesheet" href="/syspan-estagio-locacao-v2/projeto-locacao/assets/css/style.css">
    </head>

    <body>
        <h1>Mini Locação Syspan</h1>

        <nav>
            <a href="/syspan-estagio-locacao-v2/projeto-locacao/index.php">Home</a>
            <a href="/syspan-estagio-locacao-v2/projeto-locacao/clientes/listar.php">Clientes</a>
            <a href="/syspan-estagio-locacao-v2/projeto-locacao/equipamentos/listar.php">Equipamentos</a>
            <a href="/syspan-estagio-locacao-v2/projeto-locacao/precos/listar.php">Preços</a>
            <a href="/syspan-estagio-locacao-v2/projeto-locacao/contratos/listar.php">Contratos</a>
        </nav>

        <hr>

<?php exibirMensagem(); ?>