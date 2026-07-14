<?php
session_start();
require_once 'config/conexao.php'; // Inclui a conexão com o banco (onde está o $pdo)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mensagem = '';
$tipo_alerta = '';
$mostrar_formulario = false;
$id_usuario = null;

// 1. Verifica se o token veio pelo link (na URL)
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // 2. Procura o token no banco de dados e verifica se não expirou
    $stmt = $pdo->prepare("SELECT id_usuario, token_expiracao FROM usuarios WHERE token_recuperacao = ? LIMIT 1");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $data_atual = date('Y-m-d H:i:s');
        
        // Verifica se a data atual é menor ou igual à data de expiração (1 hora)
        if ($data_atual <= $usuario['token_expiracao']) {
            $mostrar_formulario = true;
            $id_usuario = $usuario['id_usuario'];
        } else {
            $mensagem = "Este link de recuperação expirou. Por favor, solicite um novo.";
            $tipo_alerta = "danger";
        }
    } else {
        $mensagem = "Link de recuperação inválido ou a palavra-passe já foi alterada.";
        $tipo_alerta = "danger";
    }
} else {
    $mensagem = "Nenhum token de recuperação foi fornecido.";
    $tipo_alerta = "danger";
}

// 3. Processa o formulário quando o utilizador digita a nova senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mostrar_formulario) {
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if ($nova_senha !== $confirmar_senha) {
        $mensagem = "As palavras-passe não coincidem. Tente novamente.";
        $tipo_alerta = "warning";
    } else {
        // 4. Atualiza a senha no banco de dados e ANULA o token para que o link não possa ser usado de novo
        $stmt_update = $pdo->prepare("UPDATE usuarios SET senha = ?, token_recuperacao = NULL, token_expiracao = NULL WHERE id_usuario = ?");
        
        if ($stmt_update->execute([$nova_senha, $id_usuario])) {
            $mensagem = "A sua palavra-passe foi redefinida com sucesso! Já pode iniciar sessão.";
            $tipo_alerta = "success";
            $mostrar_formulario = false; // Esconde o formulário pois já deu sucesso
        } else {
            $mensagem = "Ocorreu um erro ao atualizar a palavra-passe. Tente novamente.";
            $tipo_alerta = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Criar Nova Senha - MindTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #121212 !important;
            color: #ffffff !important;
            height: 100vh;
        }
        .login-card {
            background-color: #1e1e1e;
            border: 1px solid #2d2d2d;
            border-radius: 12px;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.4);
        }
        .form-control {
            background-color: #2b2b2b;
            border: 1px solid #3d3d3d;
            color: #fff;
        }
        
        /* Correções de foco para manter o estilo MindTech (dourado) */
        .form-control:focus {
            border-color: #ecc245 !important;
            box-shadow: none !important;
            background-color: #2b2b2b;
            color: #fff;
        }
        .input-group:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(236, 194, 69, 0.25) !important;
            border-radius: 0.375rem;
        }
        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: #ecc245 !important;
        }

        .text-brand { color: #ecc245; }
        .btn-brand {
            background-color: #ecc245;
            color: #121212;
            font-weight: bold;
        }
        .btn-brand:hover {
            background-color: #d4ad3c;
            color: #000;
        }
        .hover-white:hover { color: #fff !important; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            
            <div class="card login-card p-4 p-sm-5 text-center">
                <div class="mb-4">
                    <h2 class="text-brand fw-bold mb-3"><i class="bi bi-shield-lock-fill me-2"></i>MINDTECH</h2>
                    <h4 class="mb-2">Nova Palavra-passe</h4>
                    <?php if ($mostrar_formulario): ?>
                        <p class="text-muted small">Crie uma nova palavra-passe segura para a sua conta.</p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($mensagem)): ?>
                    <div class="alert alert-<?= $tipo_alerta ?> bg-<?= $tipo_alerta ?> bg-opacity-10 border-<?= $tipo_alerta ?> border-opacity-50 text-<?= $tipo_alerta ?> py-3 small fw-bold">
                        <?= $mensagem ?>
                    </div>
                <?php endif; ?>

                <?php if ($mostrar_formulario): ?>
                    <form method="POST" action="">
                        <div class="mb-3 text-start">
                            <label for="nova_senha" class="form-label text-muted small fw-bold">Nova Palavra-passe</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control" id="nova_senha" name="nova_senha" placeholder="Digite a nova senha" required minlength="6">
                            </div>
                        </div>

                        <div class="mb-4 text-start">
                            <label for="confirmar_senha" class="form-label text-muted small fw-bold">Confirmar Palavra-passe</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-key-fill"></i></span>
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" placeholder="Repita a senha" required minlength="6">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-brand w-100 py-2 mb-3">
                            <i class="bi bi-check-circle me-2"></i>Guardar Nova Senha
                        </button>
                    </form>
                <?php endif; ?>

                <div class="mt-2">
                    <a href="login.php" class="text-muted small text-decoration-none hover-white">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Ir para o Login
                    </a>
                </div>

            </div>

        </div>
    </div>
</div>

</body>
</html>