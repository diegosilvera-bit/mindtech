<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php';
require_once '../includes/auth.php';

verificarAcesso(['G', 'A', 'T']);

include '../config/conexao.php';

$perfil_logado = $_SESSION['usuario']['perfil'] ?? '';
$mensagem = '';
$tipo_alerta = '';

// ============================================================
// AÇÃO: INATIVAR / ATIVAR CLIENTE
// ============================================================
if (isset($_GET['status_cliente'])) {
    $id = (int) $_GET['status_cliente'];
    $res = mysqli_query($conn, "SELECT ativo FROM clientes WHERE id_cliente = $id");
    if ($row = mysqli_fetch_assoc($res)) {
        $novo = $row['ativo'] == 1 ? 0 : 1;
        mysqli_query($conn, "UPDATE clientes SET ativo = $novo WHERE id_cliente = $id");
        $mensagem = $novo == 1 ? "Cliente reativado com sucesso!" : "Cliente inativado com sucesso!";
        $tipo_alerta = $novo == 1 ? 'success' : 'warning';
    }
}

// ============================================================
// AÇÃO: CADASTRAR EQUIPAMENTO (via modal)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'cadastrar_equip') {
    $id_cliente = (int) $_POST['id_cliente'];
    $tipo = mysqli_real_escape_string($conn, trim($_POST['tipo']));
    $marca = mysqli_real_escape_string($conn, trim($_POST['marca']));
    $modelo = mysqli_real_escape_string($conn, trim($_POST['modelo']));
    $numero_serie = mysqli_real_escape_string($conn, trim($_POST['numero_serie']));
    $observacoes = mysqli_real_escape_string($conn, trim($_POST['observacoes']));

    if ($id_cliente <= 0 || empty($tipo)) {
        $mensagem = "Cliente e Tipo do equipamento são obrigatórios.";
        $tipo_alerta = 'danger';
    } else {
        $sql = "INSERT INTO equipamentos (id_cliente, tipo, marca, modelo, numero_serie, observacoes)
                VALUES ($id_cliente, '$tipo', '$marca', '$modelo', '$numero_serie', '$observacoes')";
        if (mysqli_query($conn, $sql)) {
            $mensagem = "Equipamento cadastrado com sucesso!";
            $tipo_alerta = 'success';
        } else {
            $mensagem = "Erro ao cadastrar equipamento: " . mysqli_error($conn);
            $tipo_alerta = 'danger';
        }
    }
}

// AÇÃO: EDITAR EQUIPAMENTO (via modal)

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'editar_equip') {
    $id_equipamento = (int) $_POST['id_equipamento'];
    $id_cliente = (int) $_POST['id_cliente'];
    $tipo = mysqli_real_escape_string($conn, trim($_POST['tipo']));
    $marca = mysqli_real_escape_string($conn, trim($_POST['marca']));
    $modelo = mysqli_real_escape_string($conn, trim($_POST['modelo']));
    $numero_serie = mysqli_real_escape_string($conn, trim($_POST['numero_serie']));
    $observacoes = mysqli_real_escape_string($conn, trim($_POST['observacoes']));

    $sql = "UPDATE equipamentos SET
                id_cliente = $id_cliente,
                tipo = '$tipo',
                marca = '$marca',
                modelo = '$modelo',
                numero_serie = '$numero_serie',
                observacoes = '$observacoes'
            WHERE id_equipamento = $id_equipamento";
    if (mysqli_query($conn, $sql)) {
        $mensagem = "Equipamento atualizado com sucesso!";
        $tipo_alerta = 'success';
    } else {
        $mensagem = "Erro ao atualizar equipamento: " . mysqli_error($conn);
        $tipo_alerta = 'danger';
    }
}

// AÇÃO: INATIVAR / ATIVAR EQUIPAMENTO

if (isset($_GET['status_equip'])) {
    $id = (int) $_GET['status_equip'];
    $res = mysqli_query($conn, "SELECT ativo FROM equipamentos WHERE id_equipamento = $id");
    if ($row = mysqli_fetch_assoc($res)) {
        $novo = $row['ativo'] == 1 ? 0 : 1;
        mysqli_query($conn, "UPDATE equipamentos SET ativo = $novo WHERE id_equipamento = $id");
        $mensagem = $novo == 1 ? "Equipamento reativado com sucesso!" : "Equipamento inativado com sucesso!";
        $tipo_alerta = $novo == 1 ? 'success' : 'warning';
    }
}

// Filtro de pesquisa por nome do cliente (fallback para quando o JS estiver desativado)
$busca = trim($_GET['busca'] ?? '');
$whereBusca = '';
if ($busca !== '') {
    $buscaEsc = mysqli_real_escape_string($conn, $busca);
    $whereBusca = "WHERE nome LIKE '%$buscaEsc%'";
}

// BUSCA: CLIENTES
$sql_clientes = "SELECT * FROM clientes $whereBusca ORDER BY id_cliente ASC";
$result_clientes = mysqli_query($conn, $sql_clientes);

include '../includes/header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>
    /* =========================================================
       CABEÇALHO: título, busca e ações — Flexbox no desktop
    ========================================================= */
    .clientes-topo {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .clientes-topo__acoes {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .clientes-busca .input-group {
        min-width: 220px;
    }

    /* =========================================================
       MOBILE (até 768px): Grid para os botões + busca em largura total
    ========================================================= */
    @media (max-width: 768px) {
        .clientes-topo {
            flex-direction: column;
            align-items: stretch;
        }

        .clientes-busca {
            order: -1;
            width: 100%;
        }

        .clientes-busca .input-group {
            width: 100%;
            min-width: 0;
        }

        .clientes-topo__acoes {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            width: 100%;
        }

        .clientes-topo__acoes .btn {
            width: 100%;
        }
    }

    /* =========================================================
       MOBILE (até 768px): Tabela vira "cards" — CSS puro, sem alterar o HTML
    ========================================================= */
    @media (max-width: 768px) {
        #tabelaClientes thead {
            display: none;
        }

        #tabelaClientes,
        #tabelaClientes tbody,
        #tabelaClientes tr,
        #tabelaClientes td {
            display: block;
            width: 100%;
        }

        #tabelaClientes tr {
            margin-bottom: 0.85rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
        }

        #tabelaClientes td {
            border: none;
            padding: 0.3rem 0;
            text-align: left !important;
        }

        #tabelaClientes td::before {
            content: attr(data-label);
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 0.15rem;
        }

        #tabelaClientes td[data-label="Ações"] .d-flex {
            flex-wrap: wrap;
            justify-content: flex-start !important;
        }
    }
</style>

<div class="container mt-4 mb-5">
    <div class="clientes-topo">
        <h1 class="fw-bold mb-0"><i class="bi bi-people"></i> Meus Clientes</h1>
        <div class="clientes-topo__acoes">
            <div class="clientes-busca">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="campoBusca" class="form-control" placeholder="Pesquisar por cliente..." value="<?php echo htmlspecialchars($busca); ?>" autocomplete="off">
                    <?php if ($busca !== ''): ?>
                        <a href="listar.php" class="btn btn-outline-secondary" title="Limpar"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <a href="../dashboard/index.php" class="btn btn-secondary">Dashboard</a>
            <a href="cadastrar.php" class="btn btn-success">+ Novo Cliente</a>
        </div>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> shadow-sm fw-bold alert-dismissible fade show">
            <?php echo $mensagem; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <!-- TABELA DE CLIENTES -->
    <div class="card shadow-sm border-0 border-start border-4 border-success">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tabelaClientes" class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3" style="width: 6%;">ID</th>
                            <th style="width: 28%;">Nome do Cliente</th>
                            <th style="width: 14%;">CPF</th>
                            <th style="width: 14%;">Telefone</th>
                            <th style="width: 10%; text-align: center;">Status</th>
                            <th style="width: 28%; text-align: center;" class="pe-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="corpoTabela">
                        <?php
                        if ($result_clientes && mysqli_num_rows($result_clientes) > 0) {
                            while ($cliente = mysqli_fetch_assoc($result_clientes)) {
                                $ativo = isset($cliente['ativo']) ? $cliente['ativo'] : 1;
                                ?>
                                <tr class="<?php echo $ativo == 0 ? 'table-light text-muted opacity-75' : ''; ?>" data-nome="<?php echo htmlspecialchars(mb_strtolower($cliente['nome'])); ?>">
                                    <td class="ps-3 fw-bold" data-label="ID">#<?php echo $cliente['id_cliente']; ?></td>

                                    <td class="fw-bold" data-label="Nome do Cliente"><?php echo htmlspecialchars($cliente['nome']); ?></td>

                                    <td data-label="CPF"><?php echo htmlspecialchars($cliente['cpf']); ?></td>

                                    <td data-label="Telefone"><?php echo !empty($cliente['telefone']) ? htmlspecialchars($cliente['telefone']) : '-'; ?>
                                    </td>

                                    <td class="text-center" data-label="Status">
                                        <?php if ($ativo == 1): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inativo</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center pe-3" data-label="Ações" style="white-space: nowrap;">
                                        <div class="d-flex flex-nowrap justify-content-center align-items-center gap-2">

                                            <a href="#" class="btn btn-sm btn-info text-white d-inline-flex align-items-center"
                                                onclick="abrirEquipamentos(<?php echo $cliente['id_cliente']; ?>, '<?php echo htmlspecialchars(addslashes($cliente['nome'])); ?>'); return false;"
                                                title="Equipamentos do Cliente">
                                                <i class="bi bi-laptop me-1"></i> Equipamentos
                                            </a>

                                            <a href="editar.php?id=<?php echo $cliente['id_cliente']; ?>"
                                                class="btn btn-sm btn-primary d-inline-flex align-items-center"
                                                title="Editar Cliente">
                                                <i class="bi bi-pencil-square me-1"></i> Editar
                                            </a>

                                            <?php if (in_array($perfil_logado, ['G', 'A'])): ?>
                                                <?php if ($ativo == 1): ?>
                                                    <a href="?status_cliente=<?php echo $cliente['id_cliente']; ?>"
                                                        class="btn btn-sm btn-danger d-inline-flex align-items-center"
                                                        onclick="return confirm('Deseja inativar este cliente?');">
                                                        <i class="bi bi-dash-circle-fill me-1"></i> Inativar
                                                    </a>
                                                <?php else: ?>
                                                    <a href="?status_cliente=<?php echo $cliente['id_cliente']; ?>"
                                                        class="btn btn-sm btn-success d-inline-flex align-items-center"
                                                        onclick="return confirm('Deseja reativar este cliente?');">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Ativar
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else { ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Nenhum cliente cadastrado no sistema.
                                </td>
                            </tr>
                        <?php } ?>
                        <tr id="semResultadoBusca" style="display:none;">
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-search me-1"></i> Nenhum cliente encontrado.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- MODAL PRINCIPAL: GERENCIAR EQUIPAMENTOS DO CLIENTE           -->

<div class="modal fade" id="modalEquipamentos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header bg-info text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-laptop me-2"></i>Equipamentos de: <span id="nomeClienteModal"
                        class="fw-bold"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                <!-- BOTÃO: Adicionar novo equipamento para este cliente -->
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-success btn-sm" onclick="abrirModalCadastrarEquip()">
                        <i class="bi bi-plus-circle me-1"></i> Novo Equipamento
                    </button>
                </div>

                <!-- TABELA DE EQUIPAMENTOS DO CLIENTE -->
                <div id="tabelaEquipamentosContainer">
                    <p class="text-muted text-center">Carregando equipamentos...</p>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>


<!-- MODAL: CADASTRAR NOVO EQUIPAMENTO-->

<div class="modal fade" id="modalCadastrarEquip" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Cadastrar Equipamento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="">
                <input type="hidden" name="acao" value="cadastrar_equip">
                <input type="hidden" name="id_cliente" id="cadastrar_id_cliente">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Tipo *</label>
                            <select class="form-select" name="tipo" required>
                                <option value="">Selecione...</option>
                                <option value="Notebook">Notebook</option>
                                <option value="Desktop (PC)">Desktop (PC)</option>
                                <option value="Smartphone">Smartphone</option>
                                <option value="Tablet">Tablet</option>
                                <option value="Monitor">Monitor</option>
                                <option value="Impressora">Impressora</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Marca</label>
                            <input type="text" class="form-control" name="marca" placeholder="Ex: Dell, Apple...">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Modelo</label>
                            <input type="text" class="form-control" name="modelo" placeholder="Ex: iPhone 14 Pro">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Número de Série / IMEI</label>
                            <input type="text" class="form-control" name="numero_serie" placeholder="Ex: BR-123456789">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Observações / Estado Físico</label>
                        <textarea class="form-control" name="observacoes" rows="2"
                            placeholder="Ex: Tela trincada..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border"
                        onclick="voltarParaEquipamentos()">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">Salvar Equipamento</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- MODAL: EDITAR EQUIPAMENTO                                    -->

<div class="modal fade" id="modalEditarEquip" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-fill me-2"></i>Editar Equipamento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="">
                <input type="hidden" name="acao" value="editar_equip">
                <input type="hidden" name="id_equipamento" id="edit_equip_id">
                <input type="hidden" name="id_cliente" id="edit_equip_id_cliente">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Tipo *</label>
                            <select class="form-select" name="tipo" id="edit_equip_tipo" required>
                                <option value="">Selecione...</option>
                                <option value="Notebook">Notebook</option>
                                <option value="Desktop (PC)">Desktop (PC)</option>
                                <option value="Smartphone">Smartphone</option>
                                <option value="Tablet">Tablet</option>
                                <option value="Monitor">Monitor</option>
                                <option value="Impressora">Impressora</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Marca</label>
                            <input type="text" class="form-control" name="marca" id="edit_equip_marca">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Modelo</label>
                            <input type="text" class="form-control" name="modelo" id="edit_equip_modelo">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Número de Série / IMEI</label>
                            <input type="text" class="form-control" name="numero_serie" id="edit_equip_serie"
                                placeholder="Ex: BR-123456789">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Observações / Estado Físico</label>
                        <textarea class="form-control" name="observacoes" id="edit_equip_observacoes" rows="2"
                            placeholder="Ex: Tela trincada..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border"
                        onclick="voltarParaEquipamentos()">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- DADOS DOS EQUIPAMENTOS (JSON embutido para o JS)             -->
<?php
// Busca TODOS os equipamentos de uma vez para evitar queries dentro de loop
$sql_equips = "SELECT e.*, c.nome AS nome_cliente 
               FROM equipamentos e 
               LEFT JOIN clientes c ON e.id_cliente = c.id_cliente 
               ORDER BY e.id_equipamento DESC";
$res_equips = mysqli_query($conn, $sql_equips);
$todos_equipamentos = [];
while ($eq = mysqli_fetch_assoc($res_equips)) {
    $todos_equipamentos[] = $eq;
}
?>

<script>
    // ================================================================
    // PESQUISA AO VIVO: filtra a tabela de clientes a cada letra digitada
    // ================================================================
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

    // Dados de todos os equipamentos já carregados do PHP
    const todosEquipamentos = <?php echo json_encode($todos_equipamentos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

    let idClienteAtivo = null;

    // Abre o modal de equipamentos filtrando pelo cliente
    function abrirEquipamentos(idCliente, nomeCliente) {
        idClienteAtivo = idCliente;
        document.getElementById('nomeClienteModal').textContent = nomeCliente;
        renderizarTabela(idCliente);
        new bootstrap.Modal(document.getElementById('modalEquipamentos')).show();
    }

    // Renderiza a tabela de equipamentos do cliente dentro do modal
    function renderizarTabela(idCliente) {
        const equips = todosEquipamentos.filter(e => parseInt(e.id_cliente) === parseInt(idCliente));
        const perfil = '<?php echo $perfil_logado; ?>';
        let html = '';

        if (equips.length === 0) {
            html = '<p class="text-center text-muted py-3">Nenhum equipamento cadastrado para este cliente.</p>';
        } else {
            html = `
        <div class="table-responsive">
        <table class="table table-hover table-striped align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th class="ps-3" style="width:8%">ID</th>
                    <th style="width:15%">Tipo</th>
                    <th style="width:25%">Marca / Modelo</th>
                    <th style="width:20%">Nº Série</th>
                    <th style="width:10%; text-align:center">Status</th>
                    <th style="width:22%; text-align:center">Ações</th>
                </tr>
            </thead>
            <tbody>`;

            equips.forEach(function (eq) {
                const ativo = parseInt(eq.ativo) === 1 || eq.ativo == null;
                const badgeStatus = ativo
                    ? `<span class="badge bg-info text-dark">Ativo</span>`
                    : `<span class="badge bg-secondary">Inativo</span>`;

                const btnStatus = (perfil === 'G' || perfil === 'A')
                    ? (ativo
                        ? `<a href="?status_equip=${eq.id_equipamento}" class="btn btn-sm btn-danger"
                          onclick="return confirm('Inativar este equipamento?')" title="Inativar">
                           <i class="bi bi-dash-circle-fill"></i> Inativar
                       </a>`
                        : `<a href="?status_equip=${eq.id_equipamento}" class="btn btn-sm btn-success"
                          onclick="return confirm('Reativar este equipamento?')" title="Ativar">
                           <i class="bi bi-check-circle-fill"></i> Ativar
                       </a>`)
                    : '';

                html += `
            <tr class="${!ativo ? 'table-light text-muted opacity-75' : ''}">
                <td class="ps-3 fw-bold text-muted">#${eq.id_equipamento}</td>
                <td><span class="badge bg-light text-dark border">${eq.tipo ?? '-'}</span></td>
                <td>${(eq.marca ?? '') + ' ' + (eq.modelo ?? '')}</td>
                <td class="text-secondary">${eq.numero_serie ?? '-'}</td>
                <td class="text-center">${badgeStatus}</td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                        <button class="btn btn-sm btn-outline-primary"
                                onclick="abrirModalEditarEquip(${JSON.stringify(eq).replace(/"/g, '&quot;')})">
                            <i class="bi bi-pencil-fill"></i> Editar
                        </button>
                        ${btnStatus}
                    </div>
                </td>
            </tr>`;
            });

            html += '</tbody></table></div>';
        }

        document.getElementById('tabelaEquipamentosContainer').innerHTML = html;
    }

    // Abre o modal de cadastrar equipamento, já com o id_cliente preenchido
    function abrirModalCadastrarEquip() {
        document.getElementById('cadastrar_id_cliente').value = idClienteAtivo;
        bootstrap.Modal.getInstance(document.getElementById('modalEquipamentos')).hide();
        new bootstrap.Modal(document.getElementById('modalCadastrarEquip')).show();
    }

    // Abre o modal de editar equipamento preenchido com os dados
    function abrirModalEditarEquip(eq) {
        document.getElementById('edit_equip_id').value = eq.id_equipamento;
        document.getElementById('edit_equip_id_cliente').value = eq.id_cliente;
        document.getElementById('edit_equip_marca').value = eq.marca ?? '';
        document.getElementById('edit_equip_modelo').value = eq.modelo ?? '';
        document.getElementById('edit_equip_serie').value = eq.numero_serie ?? '';
        document.getElementById('edit_equip_observacoes').value = eq.observacoes ?? '';

        // Seleciona o tipo correto no select
        const sel = document.getElementById('edit_equip_tipo');
        for (let i = 0; i < sel.options.length; i++) {
            if (sel.options[i].value === eq.tipo) {
                sel.selectedIndex = i;
                break;
            }
        }

        bootstrap.Modal.getInstance(document.getElementById('modalEquipamentos')).hide();
        new bootstrap.Modal(document.getElementById('modalEditarEquip')).show();
    }

    // Volta para o modal de equipamentos ao cancelar cadastro/edição
    function voltarParaEquipamentos() {
        const modalAtivo = document.querySelector('.modal.show');
        if (modalAtivo) bootstrap.Modal.getInstance(modalAtivo).hide();
        new bootstrap.Modal(document.getElementById('modalEquipamentos')).show();
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<?php include '../includes/footer.php'; ?>