<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

// Consulta SQL inteligente: traz a OS e faz JOIN para buscar os nomes reais de Clientes e Equipamentos
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
        <a href="/mindtech/dashboard/index.php" class="btn btn-secondary me-2">Voltar</a>            
        <a href="cadastrar.php" class="btn btn-success">+ Nova OS</a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 10%;">N° OS</th>
                        <th style="width: 25%;">ID Cliente</th>
                        <th style="width: 25%;">ID Equip.</th>
                        <th style="width: 15%;">Data de Entrada</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 15%; text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) { 
                            
                            // Define a cor da etiqueta de status dinamicamente baseada no seu ENUM
                            $badgeColor = 'bg-secondary';
                            $statusTexto = $row['status'];
                            
                            if ($row['status'] == 'EM_ANDAMENTO') {
                                $badgeColor = 'bg-warning text-dark';
                                $statusTexto = 'Em Andamento';
                            }
                    ?>
                        <tr>
                            <td class="fw-bold">#<?php echo $row['id_os']; ?></td>
                            <td><?php echo htmlspecialchars($row['nome_cliente']); ?></td>
                            <td><?php echo htmlspecialchars($row['equipamento']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['data_entrada'])); ?></td>
                            <td>
                                <span class="badge <?php echo $badgeColor; ?> px-2 py-1">
                                    <?php echo $statusTexto; ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="visualizar.php?id=<?php echo $row['id_os']; ?>" class="btn btn-sm btn-outline-info">
                                        Ver Detalhes
                                    </a>
                                    
                                    <a href="../orcamentos/cadastrar.php?id=<?php echo $row['id_os']; ?>" class="btn btn-sm btn-outline-primary">
                                        Editar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        } 
                    } else { 
                    ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Nenhuma Ordem de Serviço encontrada.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>