<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA: Apenas Gerente e Atendimento podem cancelar uma O.S.
verificarAcesso(['G', 'A']);

include '../config/conexao.php'; 

$id_os = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_os > 0) {
    // Atualiza o status diretamente para CANCELADO
    $sql_update = "UPDATE ordens_servico SET status = 'CANCELADO' WHERE id_os = $id_os";
    
    if (mysqli_query($conn, $sql_update)) {
        header("Location: listar.php?msg=os_cancelada");
        exit;
    }
}

header("Location: listar.php");
exit;
?>