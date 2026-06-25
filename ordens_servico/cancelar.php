<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
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
    // 1. Consulta o status atual da O.S. antes de qualquer alteração
    $sql_check = "SELECT status FROM ordens_servico WHERE id_os = $id_os";
    $result_check = mysqli_query($conn, $sql_check);
    
    if ($result_check && mysqli_num_rows($result_check) > 0) {
        $os_atual = mysqli_fetch_assoc($result_check);
        
        // 2. Trava de integridade: Não permite cancelar O.S. já finalizada ou já cancelada
        if ($os_atual['status'] === 'FINALIZADO') {
            die('<div class="container mt-5"><div class="alert alert-danger shadow-sm border-0"><i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Operação Negada:</strong> Uma Ordem de Serviço já FINALIZADA não pode ser cancelada. <br><br><a href="listar.php" class="btn btn-sm btn-secondary">Voltar à Lista</a></div></div>');
        }

        if ($os_atual['status'] === 'CANCELADO') {
            header("Location: listar.php?msg=ja_cancelada");
            exit;
        }

        // 3. Efetua o cancelamento seguro
        $sql_update = "UPDATE ordens_servico SET status = 'CANCELADO' WHERE id_os = $id_os";
        
        if (mysqli_query($conn, $sql_update)) {
            header("Location: listar.php?msg=os_cancelada");
            exit;
        } else {
            die("Erro ao cancelar a Ordem de Serviço: " . mysqli_error($conn));
        }
    }
}

header("Location: listar.php");
exit;
?>