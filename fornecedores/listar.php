<?php
require_once __DIR__ . '/../includes/functions.php';
require_once '../includes/auth.php';

// Inclui o seu ficheiro de conexão na pasta config
include '../config/conexao.php';

$fornecedores = [];
$erro = '';

// Filtro de pesquisa por nome do fornecedor
$busca = trim($_GET['busca'] ?? '');
$whereBusca = '';
if ($busca !== '') {
    $buscaEsc = mysqli_real_escape_string($conn, $busca);
    $whereBusca = "WHERE f.nome LIKE '%$buscaEsc%'";
}

// =========================================================================
// BUSCA REAL COM CONTAGEM DE PEÇAS (Une fornecedores às suas peças vinculadas)
// =========================================================================
$sql = "SELECT f.id_fornecedor, f.nome, f.cnpj, f.email, f.telefone, 
               COUNT(p.id_peca) AS total_pecas
        FROM fornecedores f
        LEFT JOIN pecas p ON f.id_fornecedor = p.id_fornecedor
        $whereBusca
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

<style>
    .topo-pagina {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .topo-pagina__acoes {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .campo-busca-wrap .input-group {
        min-width: 220px;
    }

    @media (max-width: 768px) {
        .topo-pagina {
            flex-direction: column;
            align-items: stretch;
        }
        .campo-busca-wrap {
            order: -1;
            width: 100%;
        }
        .campo-busca-wrap .input-group,
        .campo-busca-wrap form {
            width: 100%;
            min-width: 0;
        }
        .topo-pagina__acoes {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            width: 100%;
        }
        .topo-pagina__acoes .btn {
            width: 100%;
        }
    }

    @media (max-width: 768px) {
        #tabelaFornecedores thead { display: none; }
        #tabelaFornecedores, #tabelaFornecedores tbody, #tabelaFornecedores tr, #tabelaFornecedores td { display: block; width: 100%; }
        #tabelaFornecedores tr { margin-bottom: 0.85rem; border: 1px solid #dee2e6; border-radius: 0.5rem; padding: 0.75rem 1rem; }
        #tabelaFornecedores td { border: none; padding: 0.3rem 0; text-align: left !important; }
        #tabelaFornecedores td::before {
            content: attr(data-label);
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 0.15rem;
        }
        #tabelaFornecedores td[data-label="Ações"] .d-flex { flex-wrap: wrap; justify-content: flex-start !important; }
    }
</style>

<div class="container mt-4 mb-5">
    <div class="topo-pagina">
        <h1 class="fw-bold mb-0">
            <i class="bi bi-truck text-white me-2"></i>
            Fornecedores Homologados
        </h1>
        <div class="topo-pagina__acoes">
            <div class="campo-busca-wrap">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="busca" id="campoBusca" class="form-control" placeholder="Pesquisar por fornecedor..." value="<?php echo htmlspecialchars($busca); ?>" autocomplete="off">
                        <?php if ($busca !== ''): ?>
                            <a href="listar.php" class="btn btn-outline-secondary" title="Limpar"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </div>
            </div>
            <a href="../dashboard/index.php" class="btn btn-secondary"> Dashboard</a>
            <a href="cadastrar.php" class="btn btn-success">+ Novo Fornecedor</a>
        </div>
    </div>

    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger fw-bold shadow-sm"><?= $erro ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 border-start border-4 border-success">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tabelaFornecedores" class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4" style="width: 40%;">Empresa / Fornecedor</th>
                            <th style="width: 35%;">Contato Comercial</th>
                            <th class="text-center pe-4" style="width: 25%;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="corpoTabela">
                        <?php if (empty($fornecedores)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">
                                    <?php echo $busca !== '' ? 'Nenhum fornecedor encontrado para "' . htmlspecialchars($busca) . '".' : 'Nenhum fornecedor cadastrado no sistema.'; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($fornecedores as $f): ?>
                                <tr data-nome="<?php echo htmlspecialchars(mb_strtolower($f['nome'])); ?>">
                                    <td class="ps-4" data-label="Empresa / Fornecedor">
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
                                    <td data-label="Contato Comercial">
                                        <div class="fw-semibold text-secondary">
                                            <?= htmlspecialchars($f['telefone'] ?? 'Sem telefone') ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($f['email'] ?? 'Sem e-mail') ?></div>
                                    </td>
                                    <td class="text-center pe-4" data-label="Ações">
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
                        <tr id="semResultadoBusca" style="display:none;">
                            <td colspan="3" class="text-center py-4 text-muted"><i class="bi bi-search me-1"></i> Nenhum fornecedor encontrado.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Pesquisa ao vivo: filtra a tabela a cada letra digitada
    (function () {
        const campoBusca = document.getElementById('campoBusca');
        const linhas = document.querySelectorAll('#corpoTabela tr[data-nome]');
        const semResultado = document.getElementById('semResultadoBusca');
        if (!campoBusca) return;

        campoBusca.addEventListener('input', function () {
            const termo = this.value.toLowerCase().trim();
            let encontrados = 0;
            linhas.forEach(function (linha) {
                const bate = linha.dataset.nome.includes(termo);
                linha.style.display = bate ? '' : 'none';
                if (bate) encontrados++;
            });
            if (semResultado) {
                semResultado.style.display = encontrados === 0 ? '' : 'none';
            }
        });
    })();
</script>

<?php include '../includes/footer.php'; ?>