<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA
verificarAcesso(['G', 'A', 'T']);

include '../config/conexao.php'; 

$sql = "SELECT os.id_os, os.data_entrada, os.status, 
               c.nome AS nome_cliente, 
               CONCAT(e.marca, ' ', e.modelo) AS equipamento
        FROM ordens_servico os
        JOIN clientes c ON os.id_cliente = c.id_cliente
        JOIN equipamentos e ON os.id_equipamento = e.id_equipamento
        ORDER BY os.id_os DESC";

$result = mysqli_query($conn, $sql);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Ordens de Serviço</h1>
        <div>
        <a href="/mindtech/dashboard/index.php" class="btn btn-secondary me-2">Voltar ao Dashboard</a>            
        <a href="cadastrar.php" class="btn btn-success">+ Nova OS</a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3" style="width: 10%;">N° OS</th>
                        <th style="width: 25%;">Cliente</th>
                        <th style="width: 25%;">Equipamento</th>
                        <th style="width: 15%;">Data de Entrada</th>
                        <th style="width: 15%;">Etapa Atual</th>
                        <th style="width: 10%; text-align: center;" class="pe-3">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) { 
                            
                            // Traduz e colore o status ENUM que vem do banco de dados
                            $badgeColor = 'bg-secondary';
                            $statusTexto = $row['status'];
                            
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
                            }
                    ?>
                        <tr>
                            <td class="ps-3 fw-bold text-muted">#<?php echo $row['id_os']; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['nome_cliente']); ?></td>
                            <td><?php echo htmlspecialchars($row['equipamento']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['data_entrada'])); ?></td>
                            <td>
                                <span class="badge <?php echo $badgeColor; ?> px-2 py-1">
                                    <?php echo $statusTexto; ?>
                                </span>
                            </td>
                            <td class="text-center pe-3">
                                <a href="visualizar.php?id=<?php echo $row['id_os']; ?>" class="btn btn-sm btn-dark">
                                    Visualizar
                                </a>
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

<?php include '../includes/footer.php'; ?>