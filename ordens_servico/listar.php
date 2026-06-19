<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
// LIGA O MODO DE DEPURAÇÃO
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA
verificarAcesso(['G', 'A', 'T']);

include '../config/conexao.php'; 

// Consulta SQL para buscar os dados
$sql = "SELECT os.id_os, os.data_entrada, os.status, 
               c.nome AS nome_cliente, 
               CONCAT(e.marca, ' ', e.modelo) AS equipamento
        FROM ordens_servico os
        JOIN clientes c ON os.id_cliente = c.id_cliente
        JOIN equipamentos e ON os.id_equipamento = e.id_equipamento
        ORDER BY os.id_os DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Erro fatal no banco de dados: " . mysqli_error($conn));
}

$perfil_logado = $_SESSION['usuario']['perfil'] ?? '';

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold"><i class="bi bi-wrench"></i> Ordens de Serviço</h1>
        <div>
            <a href="/mindtech/dashboard/index.php" class="btn btn-secondary me-2"> Dashboard</a>            
            <a href="cadastrar.php" class="btn btn-success"> Nova OS</a>
        </div>
    </div>

    <?php 
    $msg = $_GET['msg'] ?? '';
    if ($msg == 'os_cancelada') {
        echo '<div class="alert alert-warning fw-bold shadow-sm">Ordem de Serviço cancelada com sucesso.</div>';
    }
    ?>

    <div class="card shadow-sm border-0 border-start border-4 border-primary">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3" style="width: 10%;">N° OS</th>
                            <th style="width: 25%;">Cliente</th>
                            <th style="width: 20%;">Equipamento</th>
                            <th style="width: 15%;">Data de Entrada</th>
                            <th style="width: 15%; text-align: center;">Etapa Atual</th>
                            <th style="width: 15%; text-align: center;" class="pe-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) { 
                                
                                // Traduz e colore o status ENUM
                                $badgeColor = 'bg-secondary';
                                $statusTexto = $row['status'];
                                $isCancelada = false;
                                
                                if ($statusTexto === 'EM_ANALISE') {
                                    $badgeColor = 'bg-info text-dark';
                                    $statusTexto = 'Em Análise';
                                } elseif ($statusTexto === 'EM_REPARO') {
                                    $badgeColor = 'bg-warning text-dark';
                                    $statusTexto = 'Em Reparo';
                                } elseif ($statusTexto === 'AGUARDANDO_PECA') {
                                    $badgeColor = 'bg-secondary';
                                    $statusTexto = 'Aguarda Peça';
                                } elseif ($statusTexto === 'FINALIZADO') {
                                    $badgeColor = 'bg-success';
                                    $statusTexto = 'Finalizado';
                                } elseif ($statusTexto === 'CANCELADO') {
                                    $badgeColor = 'bg-danger';
                                    $statusTexto = 'Cancelado';
                                    $isCancelada = true; // Marca para deixar a linha opaca
                                }
                        ?>
                            <tr class="<?php echo $isCancelada ? 'table-light text-muted opacity-75' : ''; ?>">
                                <td class="ps-3 fw-bold text-muted">#<?php echo $row['id_os']; ?></td>
                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['nome_cliente']); ?></td>
                                <td><?php echo htmlspecialchars($row['equipamento']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['data_entrada'])); ?></td>
                                <td class="text-center">
                                    <span class="badge <?php echo $badgeColor; ?> px-2 py-1">
                                        <?php echo $statusTexto; ?>
                                    </span>
                                </td>
                                <td class="pe-3">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        
                                        <a href="visualizar.php?id=<?php echo $row['id_os']; ?>" class="btn btn-sm btn-dark">Ver</a>
                                        
                                        <?php if (!$isCancelada && $row['status'] !== 'FINALIZADO'): ?>
                                            <a href="editar.php?id=<?php echo $row['id_os']; ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <?php endif; ?>
                                        
                                        <?php if(!$isCancelada && $row['status'] !== 'FINALIZADO' && in_array($perfil_logado, ['G', 'A'])): ?>
                                            <a href="cancelar.php?id=<?php echo $row['id_os']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Tem a certeza que deseja cancelar a O.S. #<?php echo $row['id_os']; ?>?');">
                                               Cancelar
                                            </a>
                                        <?php endif; ?>
                                        
                                    </div>
                                </td>
                            </tr>
                        <?php 
                            } 
                        } else { 
                        ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Nenhuma Ordem de Serviço encontrada no sistema.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>