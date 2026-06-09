<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA
verificarAcesso(['G', 'A', 'E', 'T']);

include '../config/conexao.php'; 

// BUSCA PEÇAS JÁ COM O NOME DO FORNECEDOR
$sql = "SELECT p.*, f.nome AS nome_fornecedor 
        FROM pecas p 
        LEFT JOIN fornecedores f ON p.id_fornecedor = f.id_fornecedor 
        ORDER BY p.descricao ASC";
        
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Erro fatal na busca de peças: " . mysqli_error($conn));
}

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Catálogo de Peças</h1>
        <div>
            <a href="../dashboard/index.php" class="btn btn-secondary me-2">Voltar ao Dashboard</a>
            <a href="cadastrar.php" class="btn btn-success">+ Nova Peça</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 border-start border-4 border-danger">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3" style="width: 10%;">Código</th>
                            <th style="width: 30%;">Descrição</th>
                            <th style="width: 20%;">Fornecedor</th>
                            <th style="width: 15%;">Estoque Atual</th>
                            <th style="width: 15%;">Valor Unitário</th>
                            <th class="text-center pe-3" style="width: 10%;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($result) > 0) {
                            while ($peca = mysqli_fetch_assoc($result)) { 
                        ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-muted"><?php echo htmlspecialchars($peca['codigo']); ?></td>
                                    
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($peca['descricao']); ?></td>
                                    
                                    <td>
                                        <?php echo !empty($peca['nome_fornecedor']) ? htmlspecialchars($peca['nome_fornecedor']) : '<span class="text-muted small">Não informado</span>'; ?>
                                    </td>
                                    
                                    <td>
                                        <?php 
                                        if ($peca['quantidade_disponivel'] <= $peca['nivel_minimo']) {
                                            echo "<span class='badge bg-danger text-white px-2 py-1'><i class='bi bi-exclamation-triangle-fill me-1'></i>" . $peca['quantidade_disponivel'] . " un (Baixo)</span>";
                                        } else {
                                            echo "<span class='badge bg-success bg-opacity-10 text-success border border-success px-2 py-1'>" . $peca['quantidade_disponivel'] . " un</span>";
                                        }
                                        ?>
                                    </td>
                                    
                                    <td class="fw-semibold">R$ <?php echo number_format($peca['valor_unitario'], 2, ',', '.'); ?></td>
                                    
                                    <td class="text-center pe-3">
                                        <a href="editar.php?id=<?php echo $peca['id_peca']; ?>" class="btn btn-sm btn-outline-danger">Editar</a>
                                    </td>
                                </tr>
                        <?php 
                            } 
                        } else { 
                        ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Nenhuma peça cadastrada no catálogo.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>