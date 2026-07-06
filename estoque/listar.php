<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a ligação simples com a base de dados
include '../config/conexao.php'; 

// Comando SQL ajustado para buscar na tabela verdadeira (pecas)
$sql = "SELECT * FROM pecas ORDER BY descricao ASC";
$result = mysqli_query($conn, $sql);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="bi bi-boxes text-white me-2"></i>Controlo de Estoque</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="../dashboard/index.php" class="btn btn-secondary shadow-sm"> Dashboard</a>
            <a href="cadastrar.php" class="btn btn-success shadow-sm"><i class="bi bi-plus-circle me-1"></i> Nova Peça</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 border-start border-4 border-dark">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Código</th>
                            <th>Descrição da Peça</th>
                            <th>Qtd. Disponível</th>
                            <th>Valor Unitário</th>
                            <th class="text-center pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Verifica se a consulta funcionou e se tem pelo menos 1 peça
                        if ($result && mysqli_num_rows($result) > 0) {
                            
                            // Laço de repetição simples
                            while ($item = mysqli_fetch_assoc($result)) { 
                        ?>
                                <tr>
                                    <td class="ps-4"><span class="badge bg-secondary"><?php echo htmlspecialchars($item['codigo']); ?></span></td>
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($item['descricao']); ?></td>
                                    
                                    <td>
                                        <?php 
                                        // Destaca em vermelho se o estoque for menor ou igual ao nível mínimo
                                        if ($item['quantidade_disponivel'] <= $item['nivel_minimo']) {
                                            echo "<span class='text-danger fw-bold'><i class='bi bi-exclamation-triangle-fill me-1'></i>" . $item['quantidade_disponivel'] . " un (Baixo)</span>";
                                        } else {
                                            echo "<span class='fw-bold'>" . $item['quantidade_disponivel'] . " un</span>";
                                        }
                                        ?>
                                    </td>
                                    
                                    <td class="text-muted">R$ <?php echo number_format($item['valor_unitario'], 2, ',', '.'); ?></td>
                                    
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="movimentar.php?id=<?php echo $item['id_peca']; ?>" class="btn btn-sm btn-warning fw-bold text-dark" title="Entrada/Saída de Estoque">
                                                <i class="bi bi-arrow-left-right"></i> Entrada/Saída
                                            </a>
                                            <a href="editar.php?id=<?php echo $item['id_peca']; ?>" class="btn btn-sm btn-primary fw-bold shadow-sm text-white" title="Editar Peça">
                                                <i class="bi bi-pencil-square"></i> Editar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                        <?php 
                            }
                        } else { 
                        ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-box fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    Nenhuma peça encontrada no inventário.
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