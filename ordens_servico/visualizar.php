<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 
verificarAcesso(['G', 'A', 'T']);
include '../config/conexao.php'; 

$id_os = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_os <= 0) { header("Location: listar.php"); exit; }

$sql = "SELECT os.*, 
               c.nome AS nome_cliente, c.cpf, c.telefone, c.endereco,
               e.tipo AS eq_tipo, e.marca AS eq_marca, e.modelo AS eq_modelo, e.numero_serie,
               u.nome AS nome_tecnico
        FROM ordens_servico os
        JOIN clientes c ON os.id_cliente = c.id_cliente
        JOIN equipamentos e ON os.id_equipamento = e.id_equipamento
        LEFT JOIN usuarios u ON os.id_usuario_responsavel = u.id_usuario
        WHERE os.id_os = $id_os";

$result = mysqli_query($conn, $sql);
$os = mysqli_fetch_assoc($result);

if (!$os) { die("O.S. não encontrada."); }

$statusDisplay = ''; $badgeColor = '';
switch ($os['status']) {
    case 'EM_ANALISE': $statusDisplay = 'Em Análise (Orçamento)'; $badgeColor = 'bg-secondary'; break;
    case 'EM_REPARO': $statusDisplay = 'Em Reparo (Bancada)'; $badgeColor = 'bg-primary'; break;
    case 'AGUARDANDO_PECA': $statusDisplay = 'Aguardando Peça'; $badgeColor = 'bg-warning text-dark'; break;
    case 'FINALIZADO': $statusDisplay = 'Finalizado'; $badgeColor = 'bg-success'; break;
    case 'CANCELADO': $statusDisplay = 'Cancelada'; $badgeColor = 'bg-dark'; break;
}

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">Ordem de Serviço #<?php echo $os['id_os']; ?></h1>
            <p class="text-muted small mb-0">Visualização detalhada da ficha técnica.</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-success"><i class="bi bi-printer me-1"></i> Imprimir</button>
            <a href="editar.php?id=<?php echo $os['id_os']; ?>" class="btn btn-primary"><i class="bi bi-pencil-square me-1"></i> Editar O.S.</a>
            <a href="listar.php" class="btn btn-secondary">Voltar</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 fw-bold"><i class="bi bi-person me-2"></i>Dados do Cliente</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6 mb-2"><strong>Nome:</strong> <?php echo htmlspecialchars($os['nome_cliente']); ?></div>
                        <div class="col-sm-6 mb-2"><strong>CPF:</strong> <?php echo htmlspecialchars($os['cpf']); ?></div>
                        <div class="col-sm-6 mb-2"><strong>Telefone:</strong> <?php echo htmlspecialchars($os['telefone']); ?></div>
                        <div class="col-sm-6 mb-2"><strong>Endereço:</strong> <?php echo htmlspecialchars($os['endereco'] ?: 'Não informado'); ?></div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 fw-bold"><i class="bi bi-laptop me-2"></i>Dados do Aparelho</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4 mb-2"><strong>Tipo:</strong> <?php echo htmlspecialchars($os['eq_tipo']); ?></div>
                        <div class="col-sm-4 mb-2"><strong>Marca:</strong> <?php echo htmlspecialchars($os['eq_marca']); ?></div>
                        <div class="col-sm-4 mb-2"><strong>Modelo:</strong> <?php echo htmlspecialchars($os['eq_modelo']); ?></div>
                        <div class="col-sm-12 mt-2"><strong>Série/IMEI:</strong> <?php echo htmlspecialchars($os['numero_serie'] ?: 'N/A'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 border-top border-4 border-info mb-4">
                <div class="card-body bg-light">
                    
                    <div class="mb-4">
                        <label class="fw-bold text-muted d-block mb-1">Status Atual</label>
                        <span class="badge <?php echo $badgeColor; ?> fs-6 px-3 py-2"><?php echo $statusDisplay; ?></span>
                    </div>

                    <div class="mb-4">
                        <label class="fw-bold text-muted d-block mb-1">Previsão de Entrega</label>
                        <div class="fs-5 mb-1">
                            <?php 
                            if (!empty($os['data_prevista_entrega']) && $os['data_prevista_entrega'] != '0000-00-00 00:00:00') {
                                echo "<strong>" . date('d/m/Y', strtotime($os['data_prevista_entrega'])) . "</strong>";
                            } else {
                                echo "<span class='text-muted'>Não definida</span>";
                            }
                            ?>
                        </div>
                        <?php echo calcularAlertaPrazo($os['data_prevista_entrega'], $os['status']); ?>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold text-muted d-block mb-1">Data de Entrada</label>
                        <p class="text-secondary mb-0"><i class="bi bi-box-arrow-in-right me-1"></i><?php echo date('d/m/Y H:i', strtotime($os['data_entrada'])); ?></p>
                    </div>

                    <div class="mb-0">
                        <label class="fw-bold text-muted d-block mb-1">Técnico Responsável</label>
                        <p class="fs-6 fw-semibold text-dark mb-0"><i class="bi bi-person-badge me-1"></i><?php echo $os['nome_tecnico'] ? htmlspecialchars($os['nome_tecnico']) : '<em>Ainda não alocado</em>'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 fw-bold text-danger"><i class="bi bi-clipboard2-pulse me-2"></i>Problema / Parecer Técnico</div>
        <div class="card-body">
            <div class="p-3 bg-light rounded border text-dark" style="white-space: pre-wrap; min-height: 120px; font-size: 1.05rem;"><?php echo htmlspecialchars($os['observacoes']); ?></div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>