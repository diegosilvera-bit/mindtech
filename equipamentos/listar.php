<?php 
// LIGA O MODO DE DEPURAÇÃO: Vai mostrar o erro exato na tela se houver problema
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA
verificarAcesso(['G', 'A', 'T']);

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

// Utiliza LEFT JOIN para evitar falhas se houver um equipamento sem cliente associado
$sql = "SELECT e.*, c.nome AS nome_cliente 
        FROM equipamentos e 
        LEFT JOIN clientes c ON e.id_cliente = c.id_cliente 
        ORDER BY e.id_equipamento DESC"; 

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Erro fatal no banco de dados: " . mysqli_error($conn));
}

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Equipamentos Cadastrados</h1>
        <div>
            <a href="../dashboard/index.php" class="btn btn-secondary me-2">Voltar ao Dashboard</a>
            <a href="cadastrar.php" class="btn btn-success">+ Novo Equipamento</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 border-start border-4 border-info">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3" style="width: 8%;">ID</th>
                            <th style="width: 27%;">Dono do Equipamento</th>
                            <th style="width: 15%;">Tipo</th>
                            <th style="width: 25%;">Marca / Modelo</th>
                            <th style="width: 15%;">Nº de Série</th>
                            <th style="width: 10%; text-align: center;" class="pe-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($equip = mysqli_fetch_assoc($result)) { 
                        ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-muted">#<?php echo htmlspecialchars($equip['id_equipamento'] ?? ''); ?></td>
                                    
                                    <td class="fw-bold text-dark">
                                        <?php echo !empty($equip['nome_cliente']) ? htmlspecialchars($equip['nome_cliente']) : '<span class="text-danger">Sem dono</span>'; ?>
                                    </td>
                                    
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1">
                                            <?php echo htmlspecialchars($equip['tipo'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <?php 
                                        // Tratamento seguro para marca e modelo
                                        $marca = $equip['marca'] ?? '';
                                        $modelo = $equip['modelo'] ?? '';
                                        $marca_modelo = trim($marca . ' ' . $modelo);
                                        echo $marca_modelo !== '' ? htmlspecialchars($marca_modelo) : '-'; 
                                        ?>
                                    </td>
                                    
                                    <td class="text-secondary fw-mono">
                                        <?php echo !empty($equip['numero_serie']) ? htmlspecialchars($equip['numero_serie']) : '-'; ?>
                                    </td>
                                    
                                    <td class="text-center pe-3">
                                        <a href="editar.php?id=<?php echo htmlspecialchars($equip['id_equipamento'] ?? ''); ?>" class="btn btn-sm btn-outline-info">Editar</a>
                                    </td>
                                </tr>
                        <?php 
                            } 
                        } else { 
                        ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Nenhum equipamento cadastrado na assistência ainda.
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