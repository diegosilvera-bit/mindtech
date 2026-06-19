<?php
require_once __DIR__ . '/../includes/functions.php';
require_once '../includes/auth.php';

// Inclui o seu ficheiro de conexão na pasta config
include '../config/conexao.php';

$fornecedores = [];
$erro = '';

// =========================================================================
// BUSCA REAL COM CONTAGEM DE PEÇAS (Une fornecedores às suas peças vinculadas)
// =========================================================================
$sql = "SELECT f.id_fornecedor, f.nome, f.cnpj, f.email, f.telefone, 
               COUNT(p.id_peca) AS total_pecas
        FROM fornecedores f
        LEFT JOIN pecas p ON f.id_fornecedor = p.id_fornecedor
        GROUP BY f.id_fornecedor
        ORDER BY f.nome ASC";

$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $fornecedores[] = $row;
    }
} else {
    $erro = "Erro ao carregar dados da base de dados: " . mysqli_error($conn);
}

// Fechar a conexão após a consulta
mysqli_close($conn);

include '../includes/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">
            <i class="bi bi-truck text-success me-2"></i>
            Fornecedores Homologados
    </h1>
        <div>
            <a href="../dashboard/index.php" class="btn btn-secondary me-2">Voltar ao Dashboard</a>
            <a href="cadastrar.php" class="btn btn-success">+ Novo Fornecedor</a>
        </div>
    </div>

    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger fw-bold shadow-sm"><?= $erro ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 border-start border-4 border-success">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4" style="width: 40%;">Empresa / Fornecedor</th>
                            <th style="width: 35%;">Contato Comercial</th>
                            <th class="text-center pe-4" style="width: 25%;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($fornecedores)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">
                                    Nenhum fornecedor cadastrado no sistema.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($fornecedores as $f): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark fs-5"><?= htmlspecialchars($f['nome']) ?></div>
                                        <div class="d-flex gap-2 mt-1 align-items-center">
                                            <span class="text-muted small">CNPJ:
                                                <?= htmlspecialchars($f['cnpj'] ?? 'Não informado') ?></span>

                                            <?php if ($f['total_pecas'] > 0): ?>
                                                <span
                                                    class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2">
                                                    <?= $f['total_pecas'] ?>
                                                    <?= $f['total_pecas'] == 1 ? 'peça vinculada' : 'peças no estoque' ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border rounded-pill px-2">
                                                    Nenhuma peça vinculada
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-secondary">
                                            <?= htmlspecialchars($f['telefone'] ?? 'Sem telefone') ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($f['email'] ?? 'Sem e-mail') ?></div>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">

                                            <a href="editar.php?id=<?= $f['id_fornecedor'] ?>" class="btn btn-sm btn-primary"
                                                title="Editar Fornecedor">
                                                <i class="bi bi-pencil-square"></i> Editar
                                            </a>

                                            <a href="deletar.php?id=<?= $f['id_fornecedor'] ?>" class="btn btn-sm btn-danger"
                                                title="Excluir Fornecedor"
                                                onclick="return confirm('Tem certeza que deseja excluir este fornecedor?');">
                                                <i class="bi bi-trash3-fill"></i> Excluir
                                            </a>

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