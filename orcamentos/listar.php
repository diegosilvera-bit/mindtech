<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA
verificarAcesso(['G', 'A']);

include '../config/conexao.php'; 

// Filtro de pesquisa por nome do cliente
$busca = trim($_GET['busca'] ?? '');
$whereBusca = '';
if ($busca !== '') {
    $buscaEsc = mysqli_real_escape_string($conn, $busca);
    $whereBusca = "WHERE c.nome LIKE '%$buscaEsc%'";
}

// BUSCA INTELIGENTE: Traz os orçamentos cruzados com a O.S. e o Nome do Cliente
$sql = "SELECT o.id_orcamento, o.id_os, o.valor_pecas, o.valor_mao_obra, o.valor_total, o.aprovado, 
               c.nome AS nome_cliente
        FROM orcamentos o
        INNER JOIN ordens_servico os ON o.id_os = os.id_os
        INNER JOIN clientes c ON os.id_cliente = c.id_cliente
        $whereBusca
        ORDER BY o.id_os ASC";
        
$result = mysqli_query($conn, $sql);

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
        #tabelaOrcamentos thead { display: none; }
        #tabelaOrcamentos, #tabelaOrcamentos tbody, #tabelaOrcamentos tr, #tabelaOrcamentos td { display: block; width: 100%; }
        #tabelaOrcamentos tr { margin-bottom: 0.85rem; border: 1px solid #dee2e6; border-radius: 0.5rem; padding: 0.75rem 1rem; }
        #tabelaOrcamentos td { border: none; padding: 0.3rem 0; text-align: left !important; }
        #tabelaOrcamentos td::before {
            content: attr(data-label);
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 0.15rem;
        }
    }
</style>

<div class="container mt-4 mb-5">
    <div class="topo-pagina">
        <h1 class="fw-bold mb-0"><i class="bi bi-cash-coin"></i> Gestão de Orçamentos</h1>
        <div class="topo-pagina__acoes">
            <div class="campo-busca-wrap">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="busca" id="campoBusca" class="form-control" placeholder="Pesquisar por cliente..." value="<?php echo htmlspecialchars($busca); ?>" autocomplete="off">
                        <?php if ($busca !== ''): ?>
                            <a href="listar.php" class="btn btn-outline-secondary" title="Limpar"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </div>
            </div>
            <a href="../dashboard/index.php" class="btn btn-secondary">Voltar ao Dashboard</a>
            <a href="cadastrar.php" class="btn btn-success">+ Gerar Novo Orçamento</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 border-start border-4 border-warning">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tabelaOrcamentos" class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4" style="width: 15%;">Nº O.S.</th>
                            <th style="width: 30%;">Cliente</th>
                            <th style="width: 15%;">Peças</th>
                            <th style="width: 15%;">Mão de Obra</th>
                            <th style="width: 15%;">Total</th>
                            <th style="width: 10%; text-align: center;">Status</th>
                            <th class="text-center pe-4" style="width: 10%;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="corpoTabela">
                        <?php 
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($o = mysqli_fetch_assoc($result)) { 
                        ?>
                            <tr data-nome="<?php echo htmlspecialchars(mb_strtolower($o['nome_cliente'])); ?>">
                                <td class="ps-4 fw-bold text-primary fs-5" data-label="Nº O.S.">#<?= $o['id_os'] ?></td>
                                
                                <td class="fw-bold text-dark" data-label="Cliente"><?= htmlspecialchars($o['nome_cliente']) ?></td>
                                
                                <td data-label="Peças">R$ <?= number_format($o['valor_pecas'], 2, ',', '.') ?></td>
                                <td data-label="Mão de Obra">R$ <?= number_format($o['valor_mao_obra'], 2, ',', '.') ?></td>
                                <td class="fw-bold text-success" data-label="Total">R$ <?= number_format($o['valor_total'], 2, ',', '.') ?></td>
                                
                                <td class="text-center" data-label="Status">
                                    <?php if ($o['aprovado'] == 1): ?>
                                        <span class="badge bg-success">Aprovado</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pendente</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="text-center pe-4" data-label="Ações">
                                    <a href="editar.php?id=<?= $o['id_orcamento'] ?>" class="btn btn-sm btn-primary">Ver / Editar</a>
                                </td>
                            </tr>
                        <?php 
                            }
                        } else { 
                        ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted"><?php echo $busca !== '' ? 'Nenhum orçamento encontrado para "' . htmlspecialchars($busca) . '".' : 'Nenhum orçamento gerado até ao momento.'; ?></td>
                            </tr>
                        <?php } ?>
                        <tr id="semResultadoBusca" style="display:none;">
                            <td colspan="7" class="text-center py-4 text-muted"><i class="bi bi-search me-1"></i> Nenhum orçamento encontrado.</td>
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