<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

// Busca todas as peças cadastradas em ordem alfabética pela descrição
$sql = "SELECT * FROM pecas ORDER BY descricao ASC";
$result = mysqli_query($conn, $sql);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Catálogo de Peças</h1>
        <div>
            <a href="../dashboard/index.php" class="btn btn-secondary me-2">Voltar</a>
            <a href="cadastrar.php" class="btn btn-danger">+ Nova Peça</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 border-start border-4 border-danger">
        <div class="card-body p-0">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Código</th>
                        <th>Descrição</th>
                        <th>Estoque Atual</th>
                        <th>Valor Unitário</th>
                        <th class="text-center pe-3">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Verifica se encontrou alguma peça cadastrada
                    if ($result && mysqli_num_rows($result) > 0) {
                        
                        // Passa linha por linha dos resultados
                        while ($peca = mysqli_fetch_assoc($result)) { 
                    ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-secondary"><?php echo $peca['codigo']; ?></span>
                                </td>
                                
                                <td class="fw-bold"><?php echo $peca['descricao']; ?></td>
                                
                                <td>
                                    <?php 
                                    // Se a quantidade estiver abaixo ou igual ao mínimo, fica vermelho com aviso
                                    if ($peca['quantidade_disponivel'] <= $peca['nivel_minimo']) {
                                        echo "<span class='text-danger fw-bold'><i class='bi bi-exclamation-triangle-fill me-1'></i>" . $peca['quantidade_disponivel'] . " un (Baixo)</span>";
                                    } else {
                                        echo "<span class='text-success fw-bold'>" . $peca['quantidade_disponivel'] . " un</span>";
                                    }
                                    ?>
                                </td>
                                
                                <td>R$ <?php echo number_format($peca['valor_unitario'], 2, ',', '.'); ?></td>
                                
                                <td class="text-center pe-3">
                                <a href="editar.php?id=<?php echo $peca['id_peca']; ?>" class="btn btn-sm btn-outline-danger">Editar</a>                                </td>
                            </tr>
                    <?php 
                        } 
                    } else { 
                    ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Nenhuma peça cadastrada no catálogo.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>