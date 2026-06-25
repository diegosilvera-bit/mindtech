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
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background-color: #121212 !important;
    color: #ffffff !important;
    height: 100vh;
    display: flex;
    align-items: center;
    overflow: hidden; /* Impede rolagem indesejada durante a animação */
}

/* ANIMAÇÃO DE ENTRADA (FADE E SCALE baseado no botao.html)  */

@keyframes fadeScaleIn {
    0% {
        opacity: 0;
        transform: scale(0.95);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-page {
    /* Aplica a animação com a mesma duração (0.6s) do seu ficheiro original */
    animation: fadeScaleIn 0.6s ease forwards;
}

/* Caixa de Login */
.card {
    background-color: #1e1e1e !important;
    border: 1px solid #2d2d2d !important;
    border-radius: 12px !important;
    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
}

/* Campos de digitação escuros */
.form-control {
    background-color: #252525 !important;
    border: 1px solid #3d3d3d !important;
    color: #ffffff !important;
    padding: 12px !important;
    transition: background-color 0.3s, border-color 0.3s, box-shadow 0.3s;
}

.form-control::placeholder {
    color: #757575 !important;
}

.form-control:focus {
    background-color: #2d2d2d !important;
    color: #ffffff !important;
    border-color: #ecc245 !important;
    box-shadow: 0 0 0 0.25rem rgba(236, 194, 69, 0.2) !important;
}

/* Botão "Entrar" Dourado com a animação de hover */
.btn-primary {
    background-color: #ecc245 !important;
    border-color: #ecc245 !important;
    color: #121212 !important;
    font-weight: bold !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px !important;
    
    /* Transição suave no botão (do arquivo botao.html) */
    transition: background-color 0.3s, transform 0.2s, box-shadow 0.2s !important;
}

.btn-primary:hover {
    background-color: #d1aa35 !important;
    border-color: #d1aa35 !important;
    color: #121212 !important;
    
    /* Move o botão levemente para cima ao passar o mouse */
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(236, 194, 69, 0.25); /* Leve brilho dourado */
}

.btn-primary:active {
    /* Volta à posição normal ao clicar */
    transform: translateY(0);
    box-shadow: 0 2px 5px rgba(236, 194, 69, 0.2);
}

.text-muted {
    color: #b3b3b3 !important;
    text-align: center;
}
</style>
</head>
<body>
<div class="container animate-page">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            
            <div class="text-center mb-4">
                <img src="assets/img/logo.png" alt="Mindtech" class="img-fluid" style="max-width: 220px; filter: drop-shadow(0px 4px 15px rgba(236, 194, 69, 0.15));">
            </div>

            <div class="card p-4">
                <h4 class="text-center fw-bold mb-4 text-uppercase tracking-wide" style="color: #ecc245; font-size: 1.1rem;">Acesso ao Sistema</h4>
                
                <?php if ($erro): ?>
                    <div class="alert alert-danger border-0 bg-danger bg-opacity-20 text-danger text-center py-2 small mb-3 rounded">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $erro ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="login" class="form-label small fw-bold text-muted">Usuário / Login</label>
                        <input type="text" class="form-control" id="login" name="login" placeholder="Digite seu usuário" required autocomplete="off">
                    </div>
                    <div class="mb-4">
                        <label for="senha" class="form-label small fw-bold text-muted">Senha </label>
                        <input type="password" class="form-control" id="senha" name="senha" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Entrar no Sistema
                    </button>
                </form>
                
                <div class="mt-4 pt-2 border-top border-secondary border-opacity-20">
                    <p class="text-muted small mb-0">Mindtech &copy; <?= date('Y') ?>  Todos os direitos reservados.</p>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>