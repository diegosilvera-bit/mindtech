<?php
session_start();
require_once 'config/conexao.php'; // Ajuste o caminho se necessário

$mensagem = '';
$tipo_alerta = '';

// Aqui entrará a lógica de envio de e-mail na próxima etapa...
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    // Simulação temporária até colocarmos o PHPMailer:
    if (!empty($email)) {
        $mensagem = "Se este e-mail estiver registado, receberá um link de recuperação em breve. (Simulação)";
        $tipo_alerta = "success";
    } else {
        $mensagem = "Por favor, digite um e-mail válido.";
        $tipo_alerta = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar Senha - MindTech</title>
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
        .form-control:focus {
            background-color: #333;
            border-color: #ecc245;
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(236, 194, 69, 0.25);
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
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            
            <div class="card login-card p-4 p-sm-5 text-center">
                <div class="mb-4">
                    <h2 class="text-brand fw-bold mb-3"><i class="bi bi-cpu-fill me-2"></i>MINDTECH</h2>
                    <h4 class="mb-2">Recuperar Palavra-passe</h4>
                    <p class="text-muted small">Digite o e-mail associado à sua conta e enviar-lhe-emos instruções para redefinir a sua senha.</p>
                </div>

                <?php if (!empty($mensagem)): ?>
                    <div class="alert alert-<?= $tipo_alerta ?> bg-<?= $tipo_alerta ?> bg-opacity-10 border-<?= $tipo_alerta ?> border-opacity-50 text-<?= $tipo_alerta ?> py-2 small">
                        <?= $mensagem ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-4 text-start">
                        <label for="email" class="form-label text-muted small fw-bold">E-mail de Registo</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="exemplo@email.com" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-brand w-100 py-2 mb-3">
                        <i class="bi bi-send me-2"></i>Enviar Link de Recuperação
                    </button>
                    
                    <a href="login.php" class="text-muted small text-decoration-none hover-white">
                        <i class="bi bi-arrow-left me-1"></i>Voltar ao Login
                    </a>
                </form>
            </div>

        </div>
    </div>
</div>

</body>
</html>