<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
// LIGA O MODO DE DEPURAÇÃO
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA MÁXIMA
verificarAcesso(['G']);

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

$id_alvo = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_logado = $_SESSION['usuario']['id'] ?? $_SESSION['usuario']['id_usuario'] ?? 0;

if ($id_alvo > 0) {
    
    // Proteção: O gerente não pode excluir a sua própria conta!
    if ($id_alvo == $id_logado) {
        header("Location: listar.php?erro=auto_exclusao");
        exit;
    }

    // TENTA excluir o usuário. Se a base de dados bloquear devido a histórico (O.S. ou Peças), captura o erro.
    try {
        $sql = "DELETE FROM usuarios WHERE id_usuario = $id_alvo";
        $resultado = mysqli_query($conn, $sql);
        
        // Caso o mysqli não dispare exceção mas retorne falso
        if (!$resultado) {
            header("Location: listar.php?erro=vinculo");
            exit;
        }

    } catch (Exception $e) {
        // Capturou a quebra do sistema! Redireciona com segurança.
        header("Location: listar.php?erro=vinculo");
        exit;
    }
}

// Se apagou com sucesso, volta para a lista
mysqli_close($conn);
header("Location: listar.php");
exit;
?>