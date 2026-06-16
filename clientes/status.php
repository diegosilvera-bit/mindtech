<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA: Gerente (G) e Atendimento (A) podem inativar clientes
verificarAcesso(['G', 'A']);

include '../config/conexao.php'; 

$id_cliente = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_cliente > 0) {
    // 1. Descobre o status atual do cliente
    $sql_busca = "SELECT ativo FROM clientes WHERE id_cliente = $id_cliente";
    $res = mysqli_query($conn, $sql_busca);
    
    if ($row = mysqli_fetch_assoc($res)) {
        // 2. Inverte o status (se é 1 vira 0, se é 0 vira 1)
        $novo_status = $row['ativo'] == 1 ? 0 : 1;
        
        // 3. Atualiza na base de dados
        $sql_update = "UPDATE clientes SET ativo = $novo_status WHERE id_cliente = $id_cliente";
        mysqli_query($conn, $sql_update);
        
        $msg = $novo_status == 1 ? "cliente_ativado" : "cliente_inativado";
        header("Location: listar.php?msg=$msg");
        exit;
    }
}

header("Location: listar.php");
exit;
?>