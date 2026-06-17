<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a ligação simples com a base de dados
include '../config/conexao.php'; 

// Comando SQL ajustado para buscar na sua tabela verdadeira (pecas)
$sql = "SELECT * FROM pecas ORDER BY descricao ASC";
$result = mysqli_query($conn, $sql);

include '../includes/header.php'; 
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Controlo de Estoque</h1>
        <div>
            <a href="../dashboard/index.php" class="btn btn-secondary me-2">Voltar ao Dashboard</a>
            <a href="cadastrar.php" class="btn btn-success">+ Nova Entrada</a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Código</th>
                        <th>Descrição da Peça</th>
                        <th>Qtd. Disponível</th>
                        <th>Valor Unitário</th>
                        <th class="text-center pe-3">Ações</th>
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
                                <td class="ps-3"><span class="badge bg-secondary"><?php echo $item['codigo']; ?></span></td>
                                <td class="fw-bold"><?php echo $item['descricao']; ?></td>
                                
                                <td>
                                    <?php 
                                    // Destaca em vermelho se o estoque for menor ou igual ao nível mínimo
                                    if ($item['quantidade_disponivel'] <= $item['nivel_minimo']) {
                                        echo "<span class='text-danger fw-bold'>" . $item['quantidade_disponivel'] . " un (Baixo)</span>";
                                    } else {
                                        echo $item['quantidade_disponivel'] . " un";
                                    }
                                    ?>
                                </td>
                                
                                <td>R$ <?php echo number_format($item['valor_unitario'], 2, ',', '.'); ?></td>
                                
                                <td class="text-center pe-3">
                                    <a href="#" class="btn btn-sm btn-primary">Entrada/Saída</a>
                                </td>
                            </tr>
                    <?php 
                        }
                    } else { 
                    ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Nenhuma peça encontrada no estoque.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>