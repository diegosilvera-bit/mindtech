<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui o ficheiro de conexão
include '../config/conexao.php'; 

$orcamentos = [];
$erro = '';

// =========================================================================
// BUSCA REAL NA BASE DE DADOS (Usando MySQLi)
// =========================================================================
$sql = "SELECT id_orcamento, id_os, valor_pecas, valor_mao_obra, valor_total, aprovado FROM orcamentos ORDER BY id_orcamento DESC";
$result = mysqli_query($conn, $sql);

if ($result) {
    // Transforma o resultado num array para podermos listar no HTML
    while ($row = mysqli_fetch_assoc($result)) {
        $orcamentos[] = $row;
    }
} else {
    // Se der erro no SQL, captura a mensagem
    $erro = "Erro ao carregar dados da base de dados: " . mysqli_error($conn);
}

// Fechar a conexão
mysqli_close($conn);

include '../includes/header.php'; 
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Orçamentos</h1>
        <div>
            <a href="../dashboard/index.php" class="btn btn-outline-secondary me-2">Voltar ao Dashboard</a>
            
            <a href="cadastrar.php" class="btn btn-success">+ Novo Orçamento</a>
        </div>
    </div>

    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger shadow-sm">
            <i class="bi bi-exclamation-triangle"></i> <?= $erro ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID Orç.</th>
                        <th>OS Vinculada</th>
                        <th>Peças</th>
                        <th>Mão de Obra</th>
                        <th class="fw-bold">Total</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orcamentos) && empty($erro)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Nenhum orçamento gerado ainda.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orcamentos as $o): ?>
                            <tr>
                                <td class="fw-bold text-muted">#<?= $o['id_orcamento'] ?></td>
                                <td><span class="badge bg-primary">OS <?= $o['id_os'] ?></span></td>
                                <td>R$ <?= number_format($o['valor_pecas'], 2, ',', '.') ?></td>
                                <td>R$ <?= number_format($o['valor_mao_obra'], 2, ',', '.') ?></td>
                                <td class="fw-bold text-success">R$ <?= number_format($o['valor_total'], 2, ',', '.') ?></td>
                                <td>
                                    <?php if ($o['aprovado'] == 1): ?>
                                        <span class="badge bg-success">Aprovado</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pendente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="editar.php?id=<?= $o['id_orcamento'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>