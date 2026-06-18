<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA: Gerente (G) e Atendimento (A) podem inativar equipamentos
verificarAcesso(['G', 'A']);

include '../config/conexao.php'; 

$id_equipamento = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_equipamento > 0) {
    // 1. Descobre o status atual do equipamento
    $sql_busca = "SELECT ativo FROM equipamentos WHERE id_equipamento = $id_equipamento";
    $res = mysqli_query($conn, $sql_busca);
    
    if ($row = mysqli_fetch_assoc($res)) {
        // 2. Inverte o status
        $novo_status = $row['ativo'] == 1 ? 0 : 1;
        
        // 3. Atualiza na base de dados
        $sql_update = "UPDATE equipamentos SET ativo = $novo_status WHERE id_equipamento = $id_equipamento";
        mysqli_query($conn, $sql_update);
        
        $msg = $novo_status == 1 ? "equip_ativado" : "equip_inativado";
        header("Location: listar.php?msg=$msg");
        exit;
    }
}

header("Location: listar.php");
exit;
?>