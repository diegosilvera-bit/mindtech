<?php
session_start();
require_once 'config/conexao.php'; // Usa o seu arquivo que tem o $pdo

// Mostrar erros na tela para facilitar testes (quando o sistema for ao ar, pode apagar estas 3 linhas)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Usa as classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Inclui os ficheiros do PHPMailer
require 'libs/PHPMailer/Exception.php';
require 'libs/PHPMailer/PHPMailer.php';
require 'libs/PHPMailer/SMTP.php';

$mensagem = '';
$tipo_alerta = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    
    // 1. Verifica se o e-mail existe no Banco de Dados (Usando SELECT *)
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // 2. Gera um Token Único e Seguro e a data de expiração (1 hora a partir de agora)
        $token = bin2hex(random_bytes(50));
        $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // 3. Salva o token no banco de dados do utilizador
        $stmt_update = $pdo->prepare("UPDATE usuarios SET token_recuperacao = ?, token_expiracao = ? WHERE email = ?");
        $stmt_update->execute([$token, $expiracao, $email]);

        // 4. Prepara o link de recuperação
        $link_recuperacao = "http://localhost:8080/mindtech/redefinir.php?token=" . $token;
        
        // 5. Configura e dispara o E-mail com PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Configurações do Servidor SMTP do Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'themindtechservices@gmail.com';
            $mail->Password   = 'pbltfrlpgbrwsudf'; // Senha de App do Google
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Remetente e Destinatário
            $mail->setFrom('themindtechservices@gmail.com', 'Sistema MindTech');
            $mail->addAddress($email); 

            // Conteúdo do E-mail
            $mail->isHTML(true);
            $mail->Subject = 'Recuperacao de Palavra-passe - MindTech';
            $mail->Body    = "
                <h3>Olá!</h3>
                <p>Recebemos um pedido para redefinir a palavra-passe da sua conta no sistema MindTech.</p>
                <p>Para criar uma nova senha, clique no link abaixo:</p>
                <p><a href='{$link_recuperacao}' style='padding: 10px 15px; background-color: #ecc245; color: #000; text-decoration: none; border-radius: 5px; font-weight: bold;'>Redefinir Minha Senha</a></p>
                <br>
                <p><i>Se o botão não funcionar, copie e cole o link abaixo no seu navegador:</i><br>
                {$link_recuperacao}</p>
                <br>
                <p><small>Este link é válido por 1 hora. Se não foi você que pediu, pode ignorar este e-mail.</small></p>
            ";

            $mail->send();
            $mensagem = "Enviámos um e-mail com as instruções para redefinir a sua senha. Verifique também a pasta de SPAM.";
            $tipo_alerta = "success";
            
        } catch (Exception $e) {
            $mensagem = "Não foi possível enviar o e-mail de recuperação. Erro do Servidor: {$mail->ErrorInfo}";
            $tipo_alerta = "danger";
        }
    } else {
        // Por motivos de segurança, se o e-mail não existir, mostramos a mesma mensagem de sucesso
        $mensagem = "Se este e-mail estiver registado, receberá as instruções em breve.";
        $tipo_alerta = "success";
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
        
        /* Correção global para focar com dourado em vez de azul */
        .form-control:focus, 
        .form-select:focus, 
        .btn:focus {
            border-color: #ecc245 !important;
            box-shadow: 0 0 0 0.25rem rgba(236, 194, 69, 0.25) !important;
            outline: none !important;
        }

        /* Correção específica para campos com ícone (Input Groups) */
        .input-group:focus-within .form-control {
            border-color: #ecc245 !important;
            box-shadow: none !important;
        }
        .input-group:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(236, 194, 69, 0.25) !important;
            border-radius: 0.375rem;
        }
        .input-group:focus-within .input-group-text {
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
                    <h2 class="text-brand fw-bold mb-3"><i class="bi bi-cpu-fill me-2"></i>MINDTECH</h2>
                    <h4 class="mb-2 text-white">Recuperar Palavra-passe</h4>
                    <p class="text-white small">Digite o e-mail associado à sua conta e enviar-lhe-emos instruções para redefinir a sua senha.</p>
                </div>

                <?php if (!empty($mensagem)): ?>
                    <div class="alert alert-<?= $tipo_alerta ?> bg-<?= $tipo_alerta ?> bg-opacity-10 border-<?= $tipo_alerta ?> border-opacity-50 text-<?= $tipo_alerta ?> py-2 small">
                        <?= $mensagem ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-4 text-start">
                        <label for="email" class="form-label text-white small fw-bold">E-mail de Registo</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-white"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email " placeholder= "exemplo@email.com" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-brand w-100 py-2 mb-3">
                        <i class="bi bi-send me-2"></i>Enviar Link de Recuperação
                    </button>
                    
                    <a href="login.php" class="text-white small text-decoration-none hover-white">
                        <i class="bi bi-arrow-left me-1"></i>Voltar ao Login
                    </a>
                </form>
            </div>

        </div>
    </div>
</div>

</body>
</html>