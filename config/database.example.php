<?php
// COPIE este arquivo para config/database.php e preencha com suas credenciais.
// Nunca commite config/database.php com dados reais.
$host     = 'localhost';
$db_name  = 'clinica_prev_dentistas';
$username = 'root';
$password = 'SUA_SENHA_AQUI';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) { die("Erro DB: " . $e->getMessage()); }
