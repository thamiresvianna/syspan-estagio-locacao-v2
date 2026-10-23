<?php
    declare(strict_types=1);

    $arquivoConfiguracao = __DIR__ . '/config.local.php';

    if(!is_file($arquivoConfiguracao)) {
        error_log("Arquivo config.local.php não encontrado.");
        die("Configuração do banco de dados não encontrada. Copie config.exemplo.php para config.local.php e preencha os dados do banco.");
    }

    $config = require $arquivoConfiguracao;

    if(!is_array($config)) {
        error_log("config.local.php deve retornar um array.");
        die("Configuração do banco de dados inválida. Confira o arquivo config.local.php.");
    }

    foreach(['host', 'porta', 'banco', 'usuario', 'senha'] as $chave) {
        if(!array_key_exists($chave, $config)) {
            error_log("Chave '$chave' ausente em config.local.php.");
            die("Configuração do banco de dados incompleta. Confira o arquivo config.local.php.");
        }
    }

    $dsn = "mysql:host={$config['host']};port={$config['porta']};dbname={$config['banco']};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $config['usuario'], $config['senha'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        error_log("Erro ao conectar no banco: " . $e->getMessage());
        die("Não foi possível conectar ao banco de dados. Por favor, tente novamente mais tarde.");
    }
?>