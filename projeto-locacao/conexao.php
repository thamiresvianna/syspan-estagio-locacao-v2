<?php
    declare(strict_types=1);

    $host = '127.0.0.1';
    $porta = '3306';
    $banco = 'syspan_locacao_estagio_v2';
    $usuario = 'root';
    $senha = '';

    $dsn = "mysql:host=$host;port=$porta;dbname=$banco;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $usuario, $senha, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        die("Erro ao conectar no banco: " . $e->getMessage());
    }
?>