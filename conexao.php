<?php
    $host     = 'localhost';
    $port     = '5432';
    $dbname   = 'locadora';
    $user     = 'postgres';
    $password = 'postgres';

    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $pdo = new PDO($dsn, $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die(json_encode(['erro' => 'Erro na conexão: ' . $e->getMessage()]));
    }
?>
