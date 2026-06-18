<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php
// LIGA O MODO DE DEPURAÇÃO
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php';
require_once '../includes/auth.php';

// TRAVA DE SEGURANÇA
verificarAcesso(['G', 'A', 'T']);

// Inclui a conexão com o banco de dados
include '../config/conexao.php';

// Busca os equipamentos com o nome do dono
$sql = "SELECT e.*, c.nome AS nome_cliente 
        FROM equipamentos e 
        LEFT JOIN clientes c ON e.id_cliente = c.id_cliente 
        ORDER BY e.id_equipamento DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Erro fatal no banco de dados: " . mysqli_error($conn));
}

// Pega o perfil logado para ocultar os botões de Inativar para os Técnicos
$perfil_logado = $_SESSION['usuario']['perfil'] ?? '';

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

    <?php
    $msg = $_GET['msg'] ?? '';
    if ($msg == 'equip_inativado') {
        echo '<div class="alert alert-warning fw-bold shadow-sm">Equipamento inativado com sucesso.</div>';
    } else if ($msg == 'equip_ativado') {
        echo '<div class="alert alert-success fw-bold shadow-sm">Equipamento reativado no sistema!</div>';
    }
    ?>

    <div class="card shadow-sm border-0 border-start border-4 border-info">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3" style="width: 8%;">ID</th>
                            <th style="width: 25%;">Dono do Equipamento</th>
                            <th style="width: 25%;">Marca / Modelo</th>
                            <th style="width: 15%;">Nº de Série</th>
                            <th style="width: 10%; text-align: center;">Status</th>
                            <th style="width: 17%; text-align: center;" class="pe-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($equip = mysqli_fetch_assoc($result)) {
                                // Verifica se a coluna ativo existe
                                $ativo = isset($equip['ativo']) ? $equip['ativo'] : 1;
                                ?>
                                <tr class="<?php echo $ativo == 0 ? 'table-light text-muted opacity-75' : ''; ?>">
                                    <td class="ps-3 fw-bold text-muted">
                                        #<?php echo htmlspecialchars($equip['id_equipamento']); ?></td>

                                    <td class="fw-bold text-dark">
                                        <?php echo !empty($equip['nome_cliente']) ? htmlspecialchars($equip['nome_cliente']) : '<span class="text-danger">Sem dono</span>'; ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1 me-1">
                                            <?php echo htmlspecialchars($equip['tipo'] ?? 'N/A'); ?>
                                        </span>
                                        <?php
                                        $marca = $equip['marca'] ?? '';
                                        $modelo = $equip['modelo'] ?? '';
                                        echo htmlspecialchars(trim($marca . ' ' . $modelo));
                                        ?>
                                    </td>

                                    <td class="text-secondary fw-mono">
                                        <?php echo !empty($equip['numero_serie']) ? htmlspecialchars($equip['numero_serie']) : '-'; ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if ($ativo == 1): ?>
                                            <span class="badge bg-info text-dark">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inativo</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center pe-3">
                                        <div class="d-flex justify-content-center gap-2">

                                            <a href="editar.php?id=<?php echo $equip['id_equipamento']; ?>"
                                                class="btn btn-outline-primary" title="Editar Equipamento">
                                                <i class="bi bi-pencil-square"></i> Editar
                                            </a>

                                            <?php if (in_array($perfil_logado, ['G', 'A'])): ?>

                                                <?php if ($ativo == 1): ?>
                                                    <a href="status.php?id=<?php echo $equip['id_equipamento']; ?>"
                                                        class="btn btn-outline-danger" title="Inativar Equipamento"
                                                        onclick="return confirm('Deseja inativar este equipamento?');">
                                                        <i class="bi bi-dash-circle-fill"></i> Inativar
                                                    </a>
                                                <?php else: ?>
                                                    <a href="status.php?id=<?php echo $equip['id_equipamento']; ?>"
                                                        class="btn btn-outline-success" title="Reativar Equipamento"
                                                        onclick="return confirm('Deseja reativar este equipamento?');">
                                                        <i class="bi bi-check-circle-fill"></i> Ativar
                                                    </a>
                                                <?php endif; ?>

                                            <?php endif; ?>

                                        </div>
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