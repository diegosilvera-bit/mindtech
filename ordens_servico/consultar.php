<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Tela pública: propositalmente NÃO carrega auth.php, pois o cliente não tem login.
include '../config/conexao.php'; 

$os = null;
$erro = '';
$codigo_digitado = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['codigo'])) {
    // Normaliza o código (maiúsculas, sem espaços) para facilitar a digitação do cliente
    $codigo_digitado = strtoupper(trim($_POST['codigo']));

    // Consulta segura via prepared statement, pois esta tela é pública
    $stmt = mysqli_prepare($conn, "SELECT os.id_os, os.status, os.data_entrada, os.data_previsao_saida, os.observacoes,
                                           e.tipo AS eq_tipo, e.marca AS eq_marca, e.modelo AS eq_modelo
                                    FROM ordens_servico os
                                    JOIN equipamentos e ON os.id_equipamento = e.id_equipamento
                                    WHERE os.codigo_acompanhamento = ?");
    mysqli_stmt_bind_param($stmt, 's', $codigo_digitado);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $os = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$os) {
        $erro = 'Código não encontrado. Verifique se digitou corretamente.';
    }
}

// Formatação amigável do status (sem expor termos internos do sistema)
$statusInfo = [
    'EM_ANALISE'      => ['texto' => 'Em Análise',         'cor' => 'info',     'icone' => 'bi-search',       'passo' => 1],
    'EM_REPARO'       => ['texto' => 'Em Reparo',           'cor' => 'warning',  'icone' => 'bi-tools',        'passo' => 2],
    'AGUARDANDO_PECA' => ['texto' => 'Aguardando Peça',     'cor' => 'secondary','icone' => 'bi-box-seam',     'passo' => 2],
    'FINALIZADO'      => ['texto' => 'Pronto para Retirada','cor' => 'success',  'icone' => 'bi-check-circle', 'passo' => 3],
    'CANCELADO'       => ['texto' => 'Cancelado',           'cor' => 'danger',   'icone' => 'bi-x-circle',     'passo' => 0],
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acompanhar Reparo - MindTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* ==========================================
           MESMA IDENTIDADE VISUAL DO LOGIN.PHP
           ========================================== */
        body {
            background-color: #121212 !important;
            color: #ffffff !important;
            min-height: 100vh;
        }

        .split-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.4);
            background-color: #1e1e1e;
            border: 1px solid #2d2d2d;
        }

        .brand-logo {
            max-height: 60px;
            width: auto;
            object-fit: contain;
        }

        .form-control {
            background-color: #2a2a2a;
            border-color: #3a3a3a;
            color: #ffffff;
        }
        .form-control:focus {
            background-color: #2a2a2a;
            color: #ffffff;
            border-color: #0d6efd;
            box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .25);
        }
        .form-control::placeholder {
            color: #888888;
        }

        .card-consulta {
            max-width: 560px;
            margin: 0 auto;
        }

        .info-box {
            background-color: #2a2a2a;
            border: 1px solid #3a3a3a;
            border-radius: 8px;
        }

        .progress {
            background-color: #2a2a2a;
        }

        hr {
            border-color: #3a3a3a;
            opacity: 1;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">

            <div class="card split-card border-0 card-consulta mb-4">
                <div class="card-body p-4 p-sm-5">

                    <div class="mb-4 text-center">
                        <img src="../assets/img/logo.png" alt="MindTech Logo" class="brand-logo mb-2 img-fluid">
                        <h4 class="fw-bold mt-3 mb-1"><i class="bi bi-wrench-adjustable-circle text-primary me-1"></i> Acompanhe seu Reparo</h4>
                        <p class="text-muted small mb-0">Digite o código que você recebeu ao deixar o aparelho na assistência técnica.</p>
                    </div>

                    <form method="POST" action="consultar.php" class="d-flex gap-2">
                        <input type="text"
                               name="codigo"
                               class="form-control text-center fw-bold text-uppercase"
                               placeholder="XXXX-XXXX"
                               value="<?php echo htmlspecialchars($codigo_digitado); ?>"
                               maxlength="20"
                               required
                               autofocus>
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-search"></i> Consultar
                        </button>
                    </form>

                    <?php if (!empty($erro)): ?>
                        <div class="alert alert-danger border-0 bg-danger bg-opacity-20 text-danger text-center py-2 small mt-3 mb-0 rounded">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($erro); ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <?php if ($os): ?>
                <?php $info = $statusInfo[$os['status']] ?? ['texto' => $os['status'], 'cor' => 'secondary', 'icone' => 'bi-question-circle', 'passo' => 0]; ?>

                <div class="card split-card border-0 card-consulta">
                    <div class="card-body p-4 p-sm-5">

                        <div class="text-center mb-4">
                            <span class="badge bg-<?php echo $info['cor']; ?> fs-6 px-3 py-2">
                                <i class="bi <?php echo $info['icone']; ?> me-1"></i> <?php echo htmlspecialchars($info['texto']); ?>
                            </span>
                        </div>

                        <?php if ($info['passo'] > 0): ?>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-<?php echo $info['cor']; ?>" style="width: <?php echo ($info['passo'] / 3) * 100; ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-4">
                            <span>Entrada</span>
                            <span>Em Reparo</span>
                            <span>Pronto</span>
                        </div>
                        <?php endif; ?>

                        <div class="info-box p-3 mb-2">
                            <p class="mb-1"><strong>Aparelho:</strong> <?php echo htmlspecialchars($os['eq_tipo'] . ' ' . $os['eq_marca'] . ' ' . $os['eq_modelo']); ?></p>
                            <p class="mb-1"><strong>Data de Entrada:</strong> <?php echo date('d/m/Y', strtotime($os['data_entrada'])); ?></p>
                            <?php if (!empty($os['data_previsao_saida'])): ?>
                                <p class="mb-0"><strong>Previsão de Entrega:</strong> <?php echo date('d/m/Y', strtotime($os['data_previsao_saida'])); ?></p>
                            <?php endif; ?>
                        </div>

                        <hr>
                        <p class="text-muted small mb-0 text-center">
                            Em caso de dúvidas sobre o seu reparo, entre em contato com a nossa assistência técnica.
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="../login.php" class="text-muted small text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i> Voltar ao login de funcionários
                </a>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>