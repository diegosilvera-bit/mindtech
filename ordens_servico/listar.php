<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

verificarAcesso(['G', 'A', 'T']);
include '../config/conexao.php'; 

// Adicionado o os.data_prevista_entrega no SELECT
$sql = "SELECT os.id_os, os.data_entrada, os.status, os.data_prevista_entrega,
               c.nome AS nome_cliente, 
               CONCAT(e.marca, ' ', e.modelo) AS equipamento,
               u.nome AS tecnico_responsavel
        FROM ordens_servico os
        JOIN clientes c ON os.id_cliente = c.id_cliente
        JOIN equipamentos e ON os.id_equipamento = e.id_equipamento
        LEFT JOIN usuarios u ON os.id_usuario_responsavel = u.id_usuario
        ORDER BY os.id_os DESC";

$result = mysqli_query($conn, $sql);
$perfil_logado = $_SESSION['usuario']['perfil'] ?? '';

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="bi bi-tools text-dark me-2"></i>Ordens de Serviço</h1>
        <div>
             <a href="../dashboard/index.php" class="btn btn-secondary me-2">Dashboard</a>
            <a href="gerar_codigo.php" class="btn btn-info me-2"><i class="bi bi-qr-code"></i> Código de Acompanhamento</a>
             <a href="cadastrar.php" class="btn btn-success"><i class=\"bi bi-plus-circle\"></i> Nova O.S.</a>
         </div>

    </div>

    <div class="card shadow-sm border-0 border-start border-4 border-dark">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Nº OS</th>
                            <th>Data Entrada</th>
                            <th>Cliente / Aparelho</th>
                            <th>Status</th>
                            <th class="text-center">Prazo / Alerta</th>
                            <th class="text-center pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) { 
                                $isCancelada = ($row['status'] === 'CANCELADO');
                        ?>
                            <tr class="<?php echo $isCancelada ? 'table-light text-muted opacity-75' : ''; ?>">
                                <td class="ps-4 fw-bold">#<?php echo $row['id_os']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['data_entrada'])); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['nome_cliente']); ?></strong><br>
                                    <small class="text-secondary"><?php echo htmlspecialchars($row['equipamento']); ?></small>
                                </td>
                                <td>
                                    <?php 
                                        if ($row['status'] == 'EM_ANALISE') echo '<span class="badge bg-secondary">Em Análise</span>';
                                        elseif ($row['status'] == 'EM_REPARO') echo '<span class="badge bg-primary">Em Reparo</span>';
                                        elseif ($row['status'] == 'AGUARDANDO_PECA') echo '<span class="badge bg-warning text-dark">Aguarda Peça</span>';
                                        elseif ($row['status'] == 'FINALIZADO') echo '<span class="badge bg-success">Finalizado</span>';
                                        elseif ($row['status'] == 'CANCELADO') echo '<span class="badge bg-dark">Cancelado</span>';
                                    ?>
                                </td>
                                
                                <td class="text-center">
                                    <?php echo calcularAlertaPrazo($row['data_prevista_entrega'], $row['status']); ?>
                                </td>
                                
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="visualizar.php?id=<?php echo $row['id_os']; ?>" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i></a>
                                        <?php if(!$isCancelada): ?>
                                            <a href="editar.php?id=<?php echo $row['id_os']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
                                        <?php endif; ?>
                                        <?php if(!$isCancelada && $row['status'] !== 'FINALIZADO' && in_array($perfil_logado, ['G', 'A'])): ?>
                                            <a href="cancelar.php?id=<?php echo $row['id_os']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem a certeza que deseja cancelar a O.S. #<?php echo $row['id_os']; ?>?');"><i class="bi bi-x-circle"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                            } 
                        } else { 
                        ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma Ordem de Serviço registada.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>