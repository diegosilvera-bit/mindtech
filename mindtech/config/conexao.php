<?php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "mindtech";

// 1. CONEXÃO MYSQLI (Para usar nas telas de Fornecedores, etc)
$conn = mysqli_connect($servidor, $usuario, $senha, $banco);

if (!$conn) {
    die("Falha na conexão MySQLi: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// 2. CONEXÃO PDO (Para não quebrar o seu login.php atual)
try {
    $pdo = new PDO("mysql:host=$servidor;dbname=$banco;charset=utf8mb4", $usuario, $senha);
    // Configura o PDO para mostrar erros caso o SQL falhe
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Falha na conexão PDO: " . $e->getMessage());
}
?>