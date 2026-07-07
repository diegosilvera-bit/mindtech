<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a ligação simples com a base de dados
include '../config/conexao.php'; 

// Filtro de pesquisa por nome/descrição da peça
$busca = trim($_GET['busca'] ?? '');
$whereBusca = '';
if ($busca !== '') {
    $buscaEsc = mysqli_real_escape_string($conn, $busca);
    $whereBusca = "WHERE descricao LIKE '%$buscaEsc%'";
}

// Comando SQL ajustado para buscar na tabela verdadeira (pecas)
$sql = "SELECT * FROM pecas $whereBusca ORDER BY descricao ASC";
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
        #tabelaEstoque thead { display: none; }
        #tabelaEstoque, #tabelaEstoque tbody, #tabelaEstoque tr, #tabelaEstoque td { display: block; width: 100%; }
        #tabelaEstoque tr { margin-bottom: 0.85rem; border: 1px solid #dee2e6; border-radius: 0.5rem; padding: 0.75rem 1rem; }
        #tabelaEstoque td { border: none; padding: 0.3rem 0; text-align: left !important; }
        #tabelaEstoque td::before {
            content: attr(data-label);
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 0.15rem;
        }
        #tabelaEstoque td[data-label="Ações"] .d-flex { flex-wrap: wrap; justify-content: flex-start !important; }
    }
</style>

<div class="container mt-4 mb-5">
    
    <div class="topo-pagina">
        <div>

            <h1 class="h3 mb-0 text-gray-800 fw-bold"><i class="bi bi-boxes text-white mb-0"></i>Controle de Estoque</h1>

        </div>

        <div class="topo-pagina__acoes"> <div class="campo-busca-wrap">
            <div class="input-group">

                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="busca" id="campoBusca" class="form-control" placeholder="Pesquisar por peça..." value="<?php echo htmlspecialchars($busca); ?>" autocomplete="off">
                        <?php if ($busca !== ''): ?>
                            <a href="listar.php" class="btn btn-outline-secondary" title="Limpar"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </div>

            </div>

            <a href="../dashboard/index.php" class="btn btn-secondary shadow-sm"> Dashboard</a>
            
            <a href="cadastrar.php" class="btn btn-success shadow-sm"><i class="bi bi-plus-circle me-1"></i> Nova Peça</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 border-start border-4 border-dark">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tabelaEstoque" class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Código</th>
                            <th>Descrição da Peça</th>
                            <th>Qtd. Disponível</th>
                            <th>Valor Unitário</th>
                            <th class="text-center pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="corpoTabela">
                        <?php 
                        // Verifica se a consulta funcionou e se tem pelo menos 1 peça
                        if ($result && mysqli_num_rows($result) > 0) {
                            
                            // Laço de repetição simples
                            while ($item = mysqli_fetch_assoc($result)) { 
                        ?>
                                <tr data-nome="<?php echo htmlspecialchars(mb_strtolower($item['descricao'])); ?>">
                                    <td class="ps-4" data-label="Código"><span class="badge bg-secondary"><?php echo htmlspecialchars($item['codigo']); ?></span></td>
                                    <td class="fw-bold text-dark" data-label="Descrição da Peça"><?php echo htmlspecialchars($item['descricao']); ?></td>
                                    
                                    <td data-label="Qtd. Disponível">
                                        <?php 
                                        // Destaca em vermelho se o estoque for menor ou igual ao nível mínimo
                                        if ($item['quantidade_disponivel'] <= $item['nivel_minimo']) {
                                            echo "<span class='text-danger fw-bold'><i class='bi bi-exclamation-triangle-fill me-1'></i>" . $item['quantidade_disponivel'] . " un (Baixo)</span>";
                                        } else {
                                            echo "<span class='fw-bold'>" . $item['quantidade_disponivel'] . " un</span>";
                                        }
                                        ?>
                                    </td>
                                    
                                    <td class="text-muted" data-label="Valor Unitário">R$ <?php echo number_format($item['valor_unitario'], 2, ',', '.'); ?></td>
                                    
                                    <td class="text-center pe-4" data-label="Ações">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="movimentar.php?id=<?php echo $item['id_peca']; ?>" class="btn btn-sm btn-warning fw-bold text-dark" title="Entrada/Saída de Estoque">
                                                <i class="bi bi-arrow-left-right"></i> Entrada/Saída
                                            </a>
                                            <a href="editar.php?id=<?php echo $item['id_peca']; ?>" class="btn btn-sm btn-primary fw-bold shadow-sm text-white" title="Editar Peça">
                                                <i class="bi bi-pencil-square"></i> Editar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                        <?php 
                            }
                        } else { 
                        ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-box fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    <?php echo $busca !== '' ? 'Nenhuma peça encontrada para "' . htmlspecialchars($busca) . '".' : 'Nenhuma peça encontrada no inventário.'; ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr id="semResultadoBusca" style="display:none;">
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-search fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                Nenhuma peça encontrada.
                            </td>
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