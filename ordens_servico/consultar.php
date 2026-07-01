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
    'EM_ANALISE'      => ['texto' => 'Em Análise',        'cor' => 'info',    'icone' => 'bi-search',        'passo' => 1],
    'EM_REPARO'       => ['texto' => 'Em Reparo',          'cor' => 'warning', 'icone' => 'bi-tools',         'passo' => 2],
    'AGUARDANDO_PECA' => ['texto' => 'Aguardando Peça',    'cor' => 'secondary','icone' => 'bi-box-seam',     'passo' => 2],
    'FINALIZADO'      => ['texto' => 'Pronto para Retirada','cor' => 'success','icone' => 'bi-check-circle', 'passo' => 3],
    'CANCELADO'       => ['texto' => 'Cancelado',          'cor' => 'danger',  'icone' => 'bi-x-circle',     'passo' => 0],
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acompanhar Reparo - MindTech</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f4f6f9; }
        .card-consulta { max-width: 520px; margin: 0 auto; }
    </style>
</head>
<body>

<div class="container py-5">

    <div class="text-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-wrench-adjustable-circle text-primary"></i> Acompanhe seu Reparo</h2>
        <p class="text-muted">Digite o código que você recebeu ao deixar o aparelho na assistência técnica.</p>
    </div>

    <div class="card shadow-sm border-0 card-consulta mb-4">
        <div class="card-body p-4">
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
        </div>
    </div>

    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger shadow-sm border-0 card-consulta mx-auto text-center">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($erro); ?>
        </div>
    <?php endif; ?>

    <?php if ($os): ?>
        <?php $info = $statusInfo[$os['status']] ?? ['texto' => $os['status'], 'cor' => 'secondary', 'icone' => 'bi-question-circle', 'passo' => 0]; ?>

        <div class="card shadow-sm border-0 card-consulta">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <span class="badge bg-<?php echo $info['cor']; ?> fs-6 px-3 py-2">
                        <i class="bi <?php echo $info['icone']; ?> me-1"></i> <?php echo htmlspecialchars($info['texto']); ?>
                    </span>
                </div>

                <?php if ($info['passo'] > 0): ?>
                <div class="progress mb-4" style="height: 8px;">
                    <div class="progress-bar bg-<?php echo $info['cor']; ?>" style="width: <?php echo ($info['passo'] / 3) * 100; ?>%"></div>
                </div>
                <div class="d-flex justify-content-between small text-muted mb-4">
                    <span>Entrada</span>
                    <span>Em Reparo</span>
                    <span>Pronto</span>
                </div>
                <?php endif; ?>

                <p class="mb-1"><strong>Aparelho:</strong> <?php echo htmlspecialchars($os['eq_tipo'] . ' ' . $os['eq_marca'] . ' ' . $os['eq_modelo']); ?></p>
                <p class="mb-1"><strong>Data de Entrada:</strong> <?php echo date('d/m/Y', strtotime($os['data_entrada'])); ?></p>
                <?php if (!empty($os['data_previsao_saida'])): ?>
                    <p class="mb-1"><strong>Previsão de Entrega:</strong> <?php echo date('d/m/Y', strtotime($os['data_previsao_saida'])); ?></p>
                <?php endif; ?>

                <hr>
                <p class="text-muted small mb-0">
                    Em caso de dúvidas sobre o seu reparo, entre em contato com a nossa assistência técnica.
                </p>
            </div>
        </div>
    <?php endif; ?>

</div>

</body>
</html>