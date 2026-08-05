<?php
// Exibe erros em ambiente de desenvolvimento (desative em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA: Restringe a exclusão para perfis autorizados (ex: Gerente 'G')
verificarAcesso(['G']);

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

// Captura e valida o ID recebido via URL
$id_fornecedor = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_fornecedor > 0) {
    try {
        // Prepara a instrução SQL com Prepared Statements para evitar SQL Injection
        $stmt = mysqli_prepare($conn, "DELETE FROM fornecedores WHERE id_fornecedor = ?");
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id_fornecedor);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                
                // Redireciona com mensagem de sucesso
                header("Location: listar.php?status=sucesso_delecao");
                exit;
            }
        }
    } catch (mysqli_sql_exception $e) {
        // Captura falhas de chave estrangeira ou erros do MySQL sem derrubar a aplicação
        mysqli_close($conn);
        header("Location: listar.php?erro=" . urlencode("Não foi possível excluir o fornecedor: " . $e->getMessage()));
        exit;
    }
}

// Caso o ID seja inválido, apenas retorna à lista
header("Location: listar.php");
exit;