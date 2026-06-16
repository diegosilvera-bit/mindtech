<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php
require_once __DIR__ . '/../includes/functions.php';
require_once '../includes/auth.php';

// TRAVA DE SEGURANÇA
verificarAcesso(['G', 'A', 'T']);

include '../config/conexao.php';

// Busca todos os clientes
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

    <?php
    $msg = $_GET['msg'] ?? '';
    if ($msg == 'cliente_inativado') {
        echo '<div class="alert alert-warning fw-bold shadow-sm">Cliente inativado com sucesso. Ele não aparecerá mais em novas buscas.</div>';
    } else if ($msg == 'cliente_ativado') {
        echo '<div class="alert alert-success fw-bold shadow-sm">Cliente reativado com sucesso!</div>';
    }
    ?>

    <div class="card shadow-sm border-0 border-start border-4 border-success">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3" style="width: 8%;">ID</th>
                            <th style="width: 30%;">Nome do Cliente</th>
                            <th style="width: 15%;">CPF</th>
                            <th style="width: 15%;">Telefone</th>
                            <th style="width: 12%; text-align: center;">Status</th>
                            <th style="width: 20%; text-align: center;" class="pe-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($cliente = mysqli_fetch_assoc($result)) {
                                // Verifica se a coluna existe (fallback caso esqueça de rodar o SQL)
                                $ativo = isset($cliente['ativo']) ? $cliente['ativo'] : 1;
                                ?>
                                <tr class="<?php echo $ativo == 0 ? 'table-light text-muted' : ''; ?>">
                                    <td class="ps-3 fw-bold">#<?php echo $cliente['id_cliente']; ?></td>

                                    <td class="fw-bold">
                                        <?php echo htmlspecialchars($cliente['nome']); ?>
                                    </td>

                                    <td><?php echo htmlspecialchars($cliente['cpf']); ?></td>

                                    <td>
                                        <?php echo !empty($cliente['telefone']) ? htmlspecialchars($cliente['telefone']) : '-'; ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if ($ativo == 1): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inativo</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center pe-3">
                                        <div class="d-flex justify-content-center gap-2">

                                            <a href="editar.php?id=<?php echo $cliente['id_cliente']; ?>"
                                                class="btn btn-outline-primary" title="Editar Cliente">
                                                <i class="bi bi-pencil-square"></i> Editar
                                            </a>

                                            <?php if ($ativo == 1): ?>
                                                <a href="status.php?id=<?php echo $cliente['id_cliente']; ?>"
                                                    class="btn btn-outline-danger" title="Inativar Cliente"
                                                    onclick="return confirm('Deseja inativar este cliente?');">
                                                    <i class="bi bi-dash-circle-fill"></i> Inativar
                                                </a>
                                            <?php else: ?>
                                                <a href="status.php?id=<?php echo $cliente['id_cliente']; ?>"
                                                    class="btn btn-outline-success" title="Reativar Cliente"
                                                    onclick="return confirm('Deseja reativar este cliente?');">
                                                    <i class="bi bi-check-circle-fill"></i> Ativar
                                                </a>
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
                                    Nenhum cliente cadastrado no sistema.
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