<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

verificarAcesso(['G', 'A', 'T']);
include '../config/conexao.php'; 

// Filtro de pesquisa por nome do cliente
$busca = trim($_GET['busca'] ?? '');
$whereBusca = '';
if ($busca !== '') {
    $buscaEsc = mysqli_real_escape_string($conn, $busca);
    $whereBusca = "WHERE c.nome LIKE '%$buscaEsc%'";
}

// Adicionado o os.data_prevista_entrega no SELECT
$sql = "SELECT os.id_os, os.data_entrada, os.status, os.data_prevista_entrega,
               c.nome AS nome_cliente, 
               CONCAT(e.marca, ' ', e.modelo) AS equipamento,
               u.nome AS tecnico_responsavel
        FROM ordens_servico os
        JOIN clientes c ON os.id_cliente = c.id_cliente
        JOIN equipamentos e ON os.id_equipamento = e.id_equipamento
        LEFT JOIN usuarios u ON os.id_usuario_responsavel = u.id_usuario
        $whereBusca
        ORDER BY os.id_os DESC";

$result = mysqli_query($conn, $sql);
$perfil_logado = $_SESSION['usuario']['perfil'] ?? '';

include '../includes/header.php'; 
?>


<style>
    /* Cabeçalho: Flexbox no desktop */
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

    /* Mobile: busca em largura total + botões em Grid 2 colunas */
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

    /* Mobile: tabela vira cards */
    @media (max-width: 768px) {
        #tabelaOS thead { display: none; }
        #tabelaOS, #tabelaOS tbody, #tabelaOS tr, #tabelaOS td { display: block; width: 100%; }
        #tabelaOS tr { margin-bottom: 0.85rem; border: 1px solid #dee2e6; border-radius: 0.5rem; padding: 0.75rem 1rem; }
        #tabelaOS td { border: none; padding: 0.3rem 0; text-align: left !important; }
        #tabelaOS td::before {
            content: attr(data-label);
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 0.15rem;
        }
        #tabelaOS td[data-label="Ações"] .d-flex { flex-wrap: wrap; justify-content: flex-start !important; }
    }
</style>

<div class="container mt-4 mb-5">
    <div class="topo-pagina">
        <h1 class="h3 mb-0 text-gray-800 fw-bold"><i class="bi bi-tools text-white mb-0"></i>Ordens de Serviço</h1>
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
            <a href="../dashboard/index.php" class="btn btn-secondary">Dashboard</a>
            <a href="gerar_codigo.php" class="btn btn-info"><i class="bi bi-qr-code"></i> Código de Acompanhamento</a>
            <a href="cadastrar.php" class="btn btn-success"><i class="bi bi-plus-circle"></i> Nova O.S.</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 border-start border-4 border-dark">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tabelaOS" class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Nº OS</th>
                            <th>Data Entrada</th>
                            <th>Cliente / Aparelho</th>
                            <th>Status</th>
                            <th class="text-center">Prazo / Alerta</th>
                            <th class="text-center pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="corpoTabela">
                        <?php 
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) { 
                                $isCancelada = ($row['status'] === 'CANCELADO');
                        ?>
                            <tr class="<?php echo $isCancelada ? 'table-light text-muted opacity-75' : ''; ?>" data-nome="<?php echo htmlspecialchars(mb_strtolower($row['nome_cliente'])); ?>">
                                <td class="ps-4 fw-bold" data-label="Nº OS">#<?php echo $row['id_os']; ?></td>
                                <td data-label="Data Entrada"><?php echo date('d/m/Y', strtotime($row['data_entrada'])); ?></td>
                                <td data-label="Cliente / Aparelho">
                                    <strong><?php echo htmlspecialchars($row['nome_cliente']); ?></strong><br>
                                    <small class="text-secondary"><?php echo htmlspecialchars($row['equipamento']); ?></small>
                                </td>
                                <td data-label="Status">
                                    <?php 
                                        if ($row['status'] == 'EM_ANALISE') echo '<span class="badge bg-secondary">Em Análise</span>';
                                        elseif ($row['status'] == 'EM_REPARO') echo '<span class="badge bg-primary">Em Reparo</span>';
                                        elseif ($row['status'] == 'AGUARDANDO_PECA') echo '<span class="badge bg-warning text-dark">Aguarda Peça</span>';
                                        elseif ($row['status'] == 'FINALIZADO') echo '<span class="badge bg-success">Finalizado</span>';
                                        elseif ($row['status'] == 'CANCELADO') echo '<span class="badge bg-dark">Cancelado</span>';
                                    ?>
                                </td>
                                
                                <td class="text-center" data-label="Prazo / Alerta">
                                    <?php echo calcularAlertaPrazo($row['data_prevista_entrega'], $row['status']); ?>
                                </td>
                                
                                <td class="text-center pe-4" data-label="Ações">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="visualizar.php?id=<?php echo $row['id_os']; ?>" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i></a>
                                        <?php if(!$isCancelada): ?>
                                            <a href="editar.php?id=<?php echo $row['id_os']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
                                        <?php endif; ?>
                                        <?php if(!$isCancelada && $row['status'] !== 'FINALIZADO' && in_array($perfil_logado, ['G', 'A'])): ?>
                                            <a href="cancelar.php?id=<?php echo $row['id_os']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem a certeza que deseja cancelar a O.S. #<?php echo $row['id_os']; ?>?');"><i class="bi bi-x-circle"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                            } 
                        } else { 
                        ?>
                            <tr><td colspan="6" class="text-center text-muted py-4"><?php echo $busca !== '' ? 'Nenhuma Ordem de Serviço encontrada para "' . htmlspecialchars($busca) . '".' : 'Nenhuma Ordem de Serviço registada.'; ?></td></tr>
                        <?php } ?>
                        <tr id="semResultadoBusca" style="display:none;">
                            <td colspan="6" class="text-center text-muted py-4"><i class="bi bi-search me-1"></i> Nenhuma Ordem de Serviço encontrada.</td>
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