<?php
// Inicia a sessão apenas se ela ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verifica se o utilizador está logado. Se não estiver, expulsa para o login.
if (!isset($_SESSION['usuario'])) {
    header("Location: /mindtech/login.php");
    exit;
}

// 2. O MOTOR DE PERMISSÕES: Função que barra quem não tem acesso
if (!function_exists('verificarAcesso')) {
    function verificarAcesso($perfis_permitidos) {
        // Pega o perfil do utilizador logado (G, A, T, ou E)
        $perfil_logado = $_SESSION['usuario']['perfil'] ?? '';

        // Verifica se o perfil do utilizador está dentro da lista de perfis permitidos para a página
        if (!in_array($perfil_logado, $perfis_permitidos)) {
            // Se NÃO estiver, expulsa de volta para o dashboard
            header("Location: /mindtech/dashboard/index.php?erro=acesso_negado");
            exit;
        }
    }
}
?>