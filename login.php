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
        }
        
       
        .split-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.4);
            background-color: #1e1e1e; /* Fundo do cartão escuro para combinar com o seu body */
            border: 1px solid #2d2d2d;
        }

        /* Se a sua marca não for azul, altere as cores HEX aqui! */
        .bg-cliente {
            background: linear-gradient(150deg,rgb(0, 0, 0),rgb(260, 200, 0));
            color: white;
        }

        .brand-logo {
    max-height: 200px;
    width: auto;
    object-fit: contain;
    display: block;
    margin-left: auto;
    margin-right: auto;
}
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            
            <div class="card split-card border-0">
                <div class="row g-0">
                    
                    <div class="col-md-6 p-4 p-sm-5 d-flex flex-column justify-content-between">
                        <div>
                            <div class="mb-4 text-start">
                                <img src="assets/img/logo.png" alt="MindTech Logo" class="brand-logo mb-2 img-fluid">
                                <p class="text-white">Acesso restrito para funcionários</p>
                            </div>

                            <?php if (!empty($erro)): ?>
                                <div class="alert alert-danger border-0 bg-danger bg-opacity-20 text-danger text-center py-2 small mb-3 rounded">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $erro ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="login" class="form-label small fw-bold text-white">Usuário / Login</label>
                                    <input type="text-white" class="form-control" id="login" name="login" placeholder="Digite seu usuário" required autocomplete="off">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="senha" class="form-label small fw-bold text-white">Senha</label>
                                    <input type="password" class="form-control" id="senha" name="senha" placeholder="••••••••" required>
                                </div>
                                
                                <button type="submit" class="btn btn-warning w-100 rounded-3 py-2 fw-bold">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Entrar no Sistema
                                </button>
                            </form>
                        </div>
                        
                        <div class="mt-4 pt-3 border-top border-secondary border-opacity-20">
                            <p class="text-white small mb-0">Mindtech &copy; <?= date('Y') ?> Todos os direitos reservados.</p>
                        </div>
                    </div>

                    <div class="col-md-6 bg-cliente p-4 p-sm-5 d-flex flex-column justify-content-center align-items-center text-center">
                        
                        <div class="mb-4">
                            <i class="bi bi-search border border-2 border-white rounded-circle p-3 fs-1 opacity-75"></i>
                        </div>
                        
                        <h3 class="fw-bold mb-3">Sou Cliente</h3>
                        <p class="mb-4 text-white-50 small px-2">
                            Deixou o seu aparelho connosco? Consulte agora o andamento do seu reparo de forma rápida, segura e sem complicação.
                        </p>
                        
                        <a href="cliente/index.php" class="btn btn-light btn-lg fw-bold text-primary w-100 shadow rounded-3 fs-6 py-2">
                            Acompanhar Meu Reparo <i class="bi bi-arrow-right-short fs-5 align-middle ms-1"></i>
                        </a>

                        <div class="mt-4 small text-white-50 opacity-75">
                            <i class="bi bi-shield-check me-1"></i> Consulta 100% segura
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>