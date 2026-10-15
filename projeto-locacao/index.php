<?php
    require_once 'layout/header.php';
?>

<main class="pagina-inicial">
    <h2>Sistema de Locação - Syspan</h2>

    <p class="descricao-index">Este sistema permite gerenciar clientes, equipamentos e contratos de locação de forma simples e organizada.</p>

    <div class="caixa-index">
        <a class="card-index" href="clientes/listar.php">
            <h3>Clientes</h3>
            <p>Cadastre, consulte, edite e exclua os clientes do sistema.</p>
            <span class="acessar-card">Acessar ➜</span>
        </a>

        <a class="card-index" href="equipamentos/listar.php">
            <h3>Equipamentos</h3>
            <p>Cadastre, consulte, edite e exclua os equipamentos disponíveis para locação.</p>
            <span class="acessar-card">Acessar ➜</span>
        </a>

        <a class="card-index" href="precos/listar.php">
            <h3>Tabelas de Preços</h3>
            <p>Cadastre e gerencie as tabelas de preços e os valores dos equipamentos.</p>
            <span class="acessar-card">Acessar ➜</span>
        </a>

        <a class="card-index" href="contratos/listar.php">
            <h3>Contratos</h3>
            <p>Cadastre contratos, adicione equipamentos e acompanhe as locações.</p>
            <span class="acessar-card">Acessar ➜</span>
        </a>
    </div>
</main>

<?php require_once 'layout/footer.php'; ?>