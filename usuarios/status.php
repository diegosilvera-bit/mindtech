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
$acao = isset($_GET['acao']) ? $_GET['acao'] : 'inativar';
$id_logado = $_SESSION['usuario']['id'] ?? $_SESSION['usuario']['id_usuario'] ?? 0;

if ($id_alvo > 0) {
    
    // Proteção: O gerente não pode inativar a sua própria conta!
    if ($id_alvo == $id_logado) {
        header("Location: listar.php?erro=auto_exclusao");
        exit;
    }

    // Define o novo status (1 para ativar, 0 para inativar)
    $novo_status = ($acao === 'ativar') ? 1 : 0;

    // Atualiza o status do usuário em vez de deletar
    $sql = "UPDATE usuarios SET ativo = $novo_status WHERE id_usuario = $id_alvo";
    
    if (!mysqli_query($conn, $sql)) {
        header("Location: listar.php?erro=falha_atualizacao");
        exit;
    }
}

// Volta para a lista
mysqli_close($conn);
header("Location: listar.php");
exit;
?>