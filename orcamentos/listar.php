<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA
verificarAcesso(['G', 'A']);

include '../config/conexao.php'; 

// BUSCA INTELIGENTE: Traz os orçamentos cruzados com a O.S. e o Nome do Cliente
$sql = "SELECT o.id_orcamento, o.id_os, o.valor_pecas, o.valor_mao_obra, o.valor_total, o.aprovado, 
               c.nome AS nome_cliente
        FROM orcamentos o
        INNER JOIN ordens_servico os ON o.id_os = os.id_os
        INNER JOIN clientes c ON os.id_cliente = c.id_cliente
        ORDER BY o.id_orcamento DESC";
        
$result = mysqli_query($conn, $sql);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Gestão de Orçamentos</h1>
        <div>
            <a href="../dashboard/index.php" class="btn btn-secondary me-2">Voltar ao Dashboard</a>
            <a href="cadastrar.php" class="btn btn-success">+ Gerar Novo Orçamento</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 border-start border-4 border-warning">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4" style="width: 15%;">Nº O.S.</th>
                            <th style="width: 30%;">Cliente</th>
                            <th style="width: 15%;">Peças</th>
                            <th style="width: 15%;">Mão de Obra</th>
                            <th style="width: 15%;">Total</th>
                            <th style="width: 10%; text-align: center;">Status</th>
                            <th class="text-center pe-4" style="width: 10%;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($o = mysqli_fetch_assoc($result)) { 
                        ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary fs-5">#<?= $o['id_os'] ?></td>
                                
                                <td class="fw-bold text-dark"><?= htmlspecialchars($o['nome_cliente']) ?></td>
                                
                                <td>R$ <?= number_format($o['valor_pecas'], 2, ',', '.') ?></td>
                                <td>R$ <?= number_format($o['valor_mao_obra'], 2, ',', '.') ?></td>
                                <td class="fw-bold text-success">R$ <?= number_format($o['valor_total'], 2, ',', '.') ?></td>
                                
                                <td class="text-center">
                                    <?php if ($o['aprovado'] == 1): ?>
                                        <span class="badge bg-success">Aprovado</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pendente</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="text-center pe-4">
                                    <a href="editar.php?id=<?= $o['id_orcamento'] ?>" class="btn btn-sm btn-primary">Ver / Editar</a>
                                </td>
                            </tr>
                        <?php 
                            }
                        } else { 
                        ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Nenhum orçamento gerado até ao momento.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>