<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

// Busca todos os clientes cadastrados em ordem alfabética
$sql = "SELECT * FROM clientes ORDER BY nome ASC";
$result = mysqli_query($conn, $sql);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Meus Clientes</h1>
        <div>
            <a href="../dashboard/index.php" class="btn btn-secondary me-2">Voltar ao Dashboard</a>
            <a href="cadastrar.php" class="btn btn-success">+ Novo Cliente</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 border-start border-4 border-success">
        <div class="card-body p-0">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Nome do Cliente</th>
                        <th>CPF</th>
                        <th>Telefone / WhatsApp</th>
                        <th class="text-center pe-3">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Verifica se encontrou algum cliente cadastrado
                    if ($result && mysqli_num_rows($result) > 0) {
                        
                        // Passa linha por linha dos resultados
                        while ($cliente = mysqli_fetch_assoc($result)) { 
                    ?>
                            <tr>
                                <td class="ps-3 text-muted">#<?php echo $cliente['id_cliente']; ?></td>
                                
                                <td class="fw-bold"><?php echo $cliente['nome']; ?></td>
                                
                                <td><?php echo $cliente['cpf']; ?></td>
                                
                                <td>
                                    <?php 
                                    // Se o telefone estiver vazio, mostra um tracinho para ficar elegante
                                    echo $cliente['telefone'] != '' ? $cliente['telefone'] : '-'; 
                                    ?>
                                </td>
                                
                                <td class="text-center pe-3">
                                    <a href="editar.php?id=<?php echo $cliente['id_cliente']; ?>" class="btn btn-sm btn-outline-success">Editar</a>
                                </td>
                            </tr>
                    <?php 
                        } 
                    } else { 
                    ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Nenhum cliente cadastrado no sistema.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>