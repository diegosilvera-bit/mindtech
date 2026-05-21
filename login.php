<?php require_once __DIR__ . '/includes/functions.php'; ?>
<?php
session_start();
require_once 'config/conexao.php';
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE login = ? AND senha = ?");
    $stmt->execute([$_POST['login'], $_POST['senha']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usuario) {
        $_SESSION['usuario'] = $usuario;
        header('Location: dashboard/index.php');
        exit;
    } else {
        $erro = 'Login ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - MindTech</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/*diego/*
/* Remove o brancão de fundo e centraliza a caixa de login na tela verticalmente */
body {
    background-color: #121212 !important;
    color: #ffffff !important;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Força o container a não sumir com o flex do body */
.container {
    margin-top: 0 !important;
}

/* Transforma o quadrado branco em um bloco escuro premium */
.card {
    background-color: #1e1e1e !important;
    border: 1px solid #2d2d2d !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5) !important;
}

/* Título MindTech brilhando em Dourado */
h3 {
    color: #ecc245 !important;
    font-weight: bold !important;
}

/* Caixas de input para Usuário e Senha escuras */
.form-control {
    background-color: #262626 !important;
    color: #ffffff !important;
    border: 1px solid #2d2d2d !important;
}

/* Efeito de foco ao clicar para digitar */
.form-control:focus {
    background-color: #2d2d2d !important;
    color: #ffffff !important;
    border-color: #ecc245 !important;
    box-shadow: 0 0 0 0.25rem rgba(236, 194, 69, 0.2) !important;
}

/* Botão "Entrar" Dourado com texto escuro de alta leitura */
.btn-primary {
    background-color: #ecc245 !important;
    border-color: #ecc245 !important;
    color: #121212 !important;
    font-weight: bold !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-primary:hover {
    background-color: #d1aa35 !important;
    border-color: #d1aa35 !important;
    color: #121212 !important;
}

/* Texto de ajuda "admin / admin" em cinza discreto */
.text-muted {
    color: #b3b3b3 !important;
    text-align: center;
}
</style>
</head>
<body>
<div class="container">
<div class="row justify-content-center">
<div class="col-md-4">
<div class="card shadow">
<div class="card-body">
<h3 class="text-center mb-4">MindTech</h3>
<?php if ($erro): ?><div class="alert alert-danger"><?= $erro ?></div><?php endif; ?>
<form method="post">
<input class="form-control mb-3" name="login" placeholder="Login" required>
<input class="form-control mb-3" type="password" name="senha" placeholder="Senha" required>
<button class="btn btn-primary w-100">Entrar</button>
</form>
<p class="small text-muted mt-3">admin / admin</p>
</div></div></div></div></div>
</body></html>