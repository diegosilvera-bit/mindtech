<?php require_once __DIR__ . '/../includes/functions.php'; ?>
<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: /mindtech/login.php');
    exit;
}
?>
