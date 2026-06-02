<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui o seu ficheiro de conexão na pasta config
include '../config/conexao.php'; 

$fornecedores = [];
$erro = '';

// =========================================================================
// BUSCA REAL NA BASE DE DADOS (Usando MySQLi)
// =========================================================================
$sql = "SELECT id_fornecedor, nome, cnpj, email, telefone FROM fornecedores ORDER BY nome ASC";
$result = mysqli_query($conn, $sql);

if ($result) {
    // Transforma o resultado num array para podermos listar no HTML
    while ($row = mysqli_fetch_assoc($result)) {
        $fornecedores[] = $row;
    }
} else {
    // Se der erro no SQL, captura a mensagem
    $erro = "Erro ao carregar dados da base de dados: " . mysqli_error($conn);
}

// Fechar a conexão após a consulta
mysqli_close($conn);

include '../includes/header.php'; 
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Fornecedores</h1>
        <div>
            <a href="../dashboard/index.php" class="btn btn-secondary me-2">Voltar ao Dashboard</a>
            
            <a href="cadastrar.php" class="btn btn-success">+ Novo Fornecedor</a>
        </div>
    </div>

    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger shadow-sm">
            <i class="bi bi-exclamation-triangle"></i> <?= $erro ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Nome / CNPJ</th>
                            <th>Contatos</th>
                            <th class="text-center pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($fornecedores) && empty($erro)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    Nenhum fornecedor cadastrado na base de dados ainda.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($fornecedores as $f): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">#<?= $f['id_fornecedor'] ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($f['nome']) ?></div>
                                        <div class="text-muted" style="font-size: 0.85em;">CNPJ: <?= htmlspecialchars($f['cnpj'] ?? 'Não informado') ?></div>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($f['telefone'] ?? 'Sem telefone') ?></div>
                                        <div class="text-muted" style="font-size: 0.85em;"><?= htmlspecialchars($f['email'] ?? 'Sem e-mail') ?></div>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="btn-group">
                                            <a href="editar.php?id=<?= $f['id_fornecedor'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                            <a href="deletar.php?id=<?= $f['id_fornecedor'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja excluir?');">Excluir</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>