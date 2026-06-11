<?php
    // Conexão com o banco de dados
    $host     = 'localhost';
    $port     = '5432';
    $dbname   = 'locadora';
    $user     = 'postgres';
    $password = 'postgres';

    try {
        // Ele tenta conectar
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $pdo = new PDO($dsn, $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        // Se não conectar, da mensagem de erro
        die(json_encode(['erro' => 'Erro na conexão: ' . $e->getMessage()]));
    }
?>
