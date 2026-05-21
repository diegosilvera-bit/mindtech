<?php require_once __DIR__ . '/includes/functions.php'; ?>
<?php
session_start();
session_destroy();
header('Location: login.php');
?>
