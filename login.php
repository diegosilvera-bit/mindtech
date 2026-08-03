<?php require_once __DIR__ . '/includes/functions.php'; ?>
<?php
session_start();
require_once 'config/conexao.php';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE login = ?");
    $stmt->execute([$_POST['login']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    $senha_ok = false;

    if ($usuario) {
        $hash_armazenado = $usuario['senha'];

        // Senha já migrada para bcrypt (password_hash)
        if (password_verify($_POST['senha'], $hash_armazenado)) {
            $senha_ok = true;
        }
        // Senha antiga, ainda em texto puro -> valida e migra na hora
        elseif ($_POST['senha'] === $hash_armazenado) {
            $senha_ok = true;

            $novo_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id_usuario = ?");
            $update->execute([$novo_hash, $usuario['id_usuario']]);
            $usuario['senha'] = $novo_hash;
        }
    }

    if ($senha_ok) {
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

        .login-card {
            border-radius: 12px;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.4);
            background-color: #1e1e1e;
            border: 1px solid #2d2d2d;
            max-width: 450px;
            width: 100%;
        }

        .brand-logo {
            max-height: 180px;
            width: auto;
            object-fit: contain;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        /* Garante que o texto digitado nos campos fique preto e legível */
        .form-control {
            color: #000000 !important;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center p-3">

    <div class="card login-card p-4 p-sm-5">
        <div class="mb-4 text-center">
            <!-- Atualizado para buscar na pasta assets/img/ -->
            <img src="assets/img/logo.png" alt="MindTech Logo" class="brand-logo mb-3 img-fluid">
            <h4 class="fw-bold text-white mb-1">Acesso Restrito</h4>
            <p class="text-white-50 small mb-0">Área exclusiva para funcionários</p>
        </div>

        <?php if (!empty($erro)): ?>
            <!-- MENSAGEM DE ERRO COM LETRA PRETA (text-dark fw-bold) -->
            <div class="alert alert-danger border-0 bg-danger-subtle text-dark fw-bold text-center py-2 small mb-3 rounded">
                <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i><?= $erro ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="login" class="form-label small fw-bold text-white">Usuário / Login</label>
                <input type="text" class="form-control" id="login" name="login" placeholder="Digite seu usuário" required autocomplete="off">
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="senha" class="form-label small fw-bold text-white mb-0">Senha</label>
                    <a href="esqueci_senha.php" class="small text-warning text-decoration-none">Esqueceu a senha?</a>
                </div>
                <input type="password" class="form-control" id="senha" name="senha" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-warning w-100 rounded-3 py-2 fw-bold text-dark">
                <i class="bi bi-box-arrow-in-right me-2"></i>Entrar no Sistema
            </button>
        </form>

        <div class="mt-4 pt-3 border-top border-secondary border-opacity-20 text-center">
            <p class="text-white-50 small mb-0">MindTech &copy; <?= date('Y') ?> Todos os direitos reservados.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>