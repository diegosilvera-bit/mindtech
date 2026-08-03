<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<!-- Importando o SweetAlert2 para os Pop-ups -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

verificarAcesso(['G', 'A']);
include '../config/conexao.php'; 

$id_os_url = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_os_url > 0) {
    $check = mysqli_query($conn, "SELECT id_orcamento FROM orcamentos WHERE id_os = $id_os_url");
    if ($check && mysqli_num_rows($check) > 0) {
        $orc = mysqli_fetch_assoc($check);
        header("Location: editar.php?id=" . $orc['id_orcamento']);
        exit;
    }
}

$mensagem = ''; 
$tipo_alerta = 'danger';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $id_os_selecionada = (int)$_POST['id_os'];
    $valor_mao_obra = isset($_POST['valor_mao_obra']) ? (float)str_replace(['.', ','], ['', '.'], $_POST['valor_mao_obra']) : 0.00;
    $valor_pecas = isset($_POST['valor_pecas']) ? (float)$_POST['valor_pecas'] : 0.00;
    $valor_total = $valor_mao_obra + $valor_pecas;
    $id_usuario_logado = $_SESSION['usuario']['id_usuario'] ?? $_SESSION['id_usuario'] ?? 'NULL';

    if ($id_os_selecionada <= 0) {
        $mensagem = "Por favor, selecione uma Ordem de Serviço válida.";
    } else {
        $valida_duplicado = mysqli_query($conn, "SELECT id_orcamento FROM orcamentos WHERE id_os = $id_os_selecionada");
        if (mysqli_num_rows($valida_duplicado) > 0) {
            $mensagem = "Já existe um orçamento cadastrado para esta Ordem de Serviço.";
        } else {

            $pecas_agrupadas = [];
            if (!empty($_POST['pecas_id']) && is_array($_POST['pecas_id'])) {
                foreach ($_POST['pecas_id'] as $index => $id_peca) {
                    $id_peca = (int)$id_peca;
                    $qtd = (int)($_POST['pecas_qtd'][$index] ?? 0);
                    if ($id_peca > 0 && $qtd > 0) {
                        $pecas_agrupadas[$id_peca] = ($pecas_agrupadas[$id_peca] ?? 0) + $qtd;
                    }
                }
            }

            // NOVA LÓGICA DE ESTOQUE E STATUS DA OS
            $status_os_final = 'EM_ANALISE'; // Status padrão se tiver tudo no estoque

            foreach ($pecas_agrupadas as $id_peca => $qtd) {
                $res_check = mysqli_query($conn, "SELECT quantidade_disponivel FROM pecas WHERE id_peca = $id_peca");
                $peca_check = mysqli_fetch_assoc($res_check);
                
                // Se a quantidade solicitada for maior que o estoque, NÃO gera erro.
                // Apenas automatiza o sistema para "Aguardando Peça".
                if (!$peca_check || $qtd > $peca_check['quantidade_disponivel']) {
                    $status_os_final = 'AGUARDANDO_PECA';
                }
            }

            $sql_insert = "INSERT INTO orcamentos (id_os, valor_pecas, valor_mao_obra, valor_total, aprovado, usuario_responsavel) 
                           VALUES ($id_os_selecionada, $valor_pecas, $valor_mao_obra, $valor_total, 0, $id_usuario_logado)";

            if (mysqli_query($conn, $sql_insert)) {
                $id_gerado = mysqli_insert_id($conn);
                
                foreach ($pecas_agrupadas as $id_peca => $qtd) {
                    mysqli_query($conn, "INSERT INTO os_peca (id_os, id_peca, quantidade_usada) VALUES ($id_os_selecionada, $id_peca, $qtd)");
                }

                // Automatiza o status da O.S. baseando-se na disponibilidade do estoque
                mysqli_query($conn, "UPDATE ordens_servico SET status = '$status_os_final' WHERE id_os = $id_os_selecionada");
                
                header("Location: editar.php?id=" . $id_gerado . "&msg=criado");
                exit;
            } else {
                $mensagem = "Erro ao gerar orçamento: " . mysqli_error($conn);
            }
        }
    }
}

if ($id_os_url > 0) {
    $sql_os = "SELECT os.id_os, c.nome AS nome_cliente, e.modelo FROM ordens_servico os JOIN clientes c ON os.id_cliente = c.id_cliente JOIN equipamentos e ON os.id_equipamento = e.id_equipamento WHERE os.id_os = $id_os_url";
} else {
    $sql_os = "SELECT os.id_os, c.nome AS nome_cliente, e.modelo FROM ordens_servico os JOIN clientes c ON os.id_cliente = c.id_cliente JOIN equipamentos e ON os.id_equipamento = e.id_equipamento WHERE os.status != 'CANCELADO' AND os.id_os NOT IN (SELECT id_os FROM orcamentos WHERE id_os IS NOT NULL) ORDER BY os.id_os DESC";
}
$res_os = mysqli_query($conn, $sql_os);

// ATUALIZADO: Buscando também a quantidade disponível no banco
$sql_pecas = "SELECT id_peca, descricao, valor_unitario, quantidade_disponivel FROM pecas ORDER BY descricao ASC";
$res_pecas = mysqli_query($conn, $sql_pecas);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="bi bi-calculator text-white me-2"></i>Gerar Novo Orçamento</h1>
        </div>
        <a href="listar.php" class="btn btn-secondary px-3">Voltar à Lista</a>
    </div>

    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $mensagem; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 border-start border-4 border-success">
        <div class="card-body p-4">
            <form method="POST" action="cadastrar.php">
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Ordem de Serviço / Cliente *</label>
                    <select class="form-select border-2" name="id_os" required style="border-radius: 8px;">
                        <option value="" disabled selected>Selecione a O.S. em aberto...</option>
                        <?php 
                        if ($res_os && mysqli_num_rows($res_os) > 0) {
                            while ($row_os = mysqli_fetch_assoc($res_os)) {
                                $selecionado = ($row_os['id_os'] == $id_os_url) ? 'selected' : '';
                                echo "<option value='{$row_os['id_os']}' {$selecionado}>O.S. #{$row_os['id_os']} - Cliente: " . htmlspecialchars($row_os['nome_cliente']) . " ({$row_os['modelo']})</option>";
                            }
                        } else {
                            echo "<option value='' disabled>Nenhuma O.S. aguardando orçamento.</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-4 border rounded p-3 bg-light">
                        <label class="form-label fw-bold text-success"><i class="bi bi-tools"></i> Peças e Componentes Utilizados</label>
                        <div class="d-flex gap-2 mb-3">
                            <select id="select_peca" class="form-select form-select-sm">
                                <option value="" disabled selected>Selecione uma peça...</option>
                                <?php while ($p = mysqli_fetch_assoc($res_pecas)): ?>
                                    <!-- ATUALIZADO: Inserido o data-estoque no HTML -->
                                    <option value="<?= $p['id_peca'] ?>" 
                                            data-nome="<?= htmlspecialchars($p['descricao']) ?>" 
                                            data-valor="<?= $p['valor_unitario'] ?>"
                                            data-estoque="<?= $p['quantidade_disponivel'] ?>">
                                        <?= htmlspecialchars($p['descricao']) ?> - R$ <?= number_format($p['valor_unitario'], 2, ',', '.') ?> (Estoque: <?= $p['quantidade_disponivel'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <input type="number" id="qtd_peca" class="form-control form-control-sm" value="1" min="1" placeholder="Qtd." style="width: 80px;">
                            <button type="button" class="btn btn-success btn-sm fw-bold px-3" onclick="adicionarPeca()">+ Add</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover bg-white mb-0 border">
                                <thead class="table-light">
                                    <tr>
                                        <th>Peça</th>
                                        <th class="text-center">Qtd</th>
                                        <th class="text-end">Vlr. Unit.</th>
                                        <th class="text-end">Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_pecas">
                                    <tr><td colspan="5" class="text-center text-muted small py-2">Nenhuma peça adicionada.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="hidden_pecas"></div>
                        
                        <div class="mt-3 text-end">
                            <span class="fw-bold text-muted me-2">Total em Peças:</span>
                            <div class="d-inline-flex align-items-center">
                                <span class="fw-bold text-success me-1">R$</span>
                                <input type="text" class="form-control form-control-sm border-0 bg-transparent text-end fw-bold text-success fs-5 p-0" id="valor_pecas" name="valor_pecas" value="0.00" readonly style="width: 100px;">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Valor da Mão de Obra (R$)*</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">R$</span>
                                <input type="text" class="form-control" id="valor_mao_obra" name="valor_mao_obra" value="0,00" oninput="mascaraMoeda(this); calcularTotal()" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-success">Valor Total Calculado</label>
                            <input type="text" class="form-control fw-bold text-success fs-4 bg-light border-0 text-end" id="valor_total_visual" value="R$ 0,00" readonly style="border-radius: 8px;">
                        </div>
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-20">
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-danger border fw-bold px-4">Cancelar</a>
                    <button class="btn btn-success fw-bold px-5 shadow-sm" type="submit">
                        <i class="bi bi-check-lg me-2"></i> Gravar Orçamento
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- INÍCIO DO MODAL DE PEDIDO DE COMPRA -->
<div class="modal fade" id="modalPedidoPeca" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title fw-bold text-dark"><i class="bi bi-cart-plus me-2"></i>Solicitar Reposição de Peça</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="form-pedido-peca">
            <div class="mb-3">
                <label class="fw-bold text-muted">Peça Necessária:</label>
                <input type="text" class="form-control" id="nome-peca-modal" readonly>
                <input type="hidden" id="id-peca-modal">
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted">Quantidade a Solicitar:</label>
                <input type="number" class="form-control" id="qtd-peca-modal" min="1" value="1">
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted">Observações para o setor de compras:</label>
                <textarea class="form-control" id="obs-peca-modal" rows="2" placeholder="Ex: Peça urgente para orçamento aguardando..."></textarea>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn-enviar-pedido" onclick="enviarPedidoReposicao()">
            <i class="bi bi-send me-1"></i> Enviar Pedido
        </button>
      </div>
    </div>
  </div>
</div>
<!-- FIM DO MODAL -->

<script>
const pecasSelecionadas = [];

// ATUALIZADO: Nova lógica da função que adiciona peça para ler o estoque e gerar os popups
function adicionarPeca() {
    const select = document.getElementById('select_peca');
    const qtdInput = document.getElementById('qtd_peca');
    
    if (select.value === "" || qtdInput.value <= 0) return;

    const option = select.options[select.selectedIndex];
    const estoque = parseInt(option.getAttribute('data-estoque'));
    const qtdDesejada = parseInt(qtdInput.value);

    // LÓGICA ESTOQUE ZERO OU INSUFICIENTE
    if (estoque === 0 || qtdDesejada > estoque) {
        Swal.fire({
            title: 'Estoque Insuficiente!',
            text: 'Não contem mais peças no estoque (ou a quantia pedida excede o saldo). Se prosseguir, a O.S. mudará para "Aguardando Peça".',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="bi bi-cart"></i> Fazer Pedido da Peça',
            cancelButtonText: 'Continuar Mesmo Assim'
        }).then((result) => {
            if (result.isConfirmed) {
                abrirModalPedido(option.getAttribute('data-nome'), option.value, qtdDesejada);
            } else {
                // Se o usuário clicar em Continuar, a peça vai pra tabela
                efetivarAdicaoNaTabela(option, qtdDesejada);
            }
        });
        return; 
    
    // LÓGICA ESTOQUE BAIXO (RESTAM 2 OU MENOS)
    } else if (estoque <= 2) {
        Swal.fire({
            title: 'Atenção: Estoque Baixo!',
            text: `Restam apenas ${estoque} unidades desta peça no estoque.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="bi bi-cart"></i> Pedir Reposição',
            cancelButtonText: 'Apenas Adicionar'
        }).then((result) => {
            if (result.isConfirmed) {
                abrirModalPedido(option.getAttribute('data-nome'), option.value, qtdDesejada);
            }
            // Independente se pediu reposição ou não, a peça é adicionada à tabela pois ainda há estoque
            efetivarAdicaoNaTabela(option, qtdDesejada);
        });
        return;
    }

    // Se tem bastante no estoque (mais de 2), segue normal
    efetivarAdicaoNaTabela(option, qtdDesejada);
}

// Função de apoio que efetivamente joga a peça na tabela do HTML
function efetivarAdicaoNaTabela(option, qtd) {
    pecasSelecionadas.push({
        id: option.value,
        nome: option.getAttribute('data-nome'),
        valor: parseFloat(option.getAttribute('data-valor')),
        qtd: qtd
    });
    
    document.getElementById('qtd_peca').value = 1;
    document.getElementById('select_peca').value = "";
    atualizarTabelaPecas();
}

// Função de apoio para abrir o modal de pedido do Bootstrap
function abrirModalPedido(nomePeca, idPeca, qtdSugerida) {
    document.getElementById('nome-peca-modal').value = nomePeca;
    document.getElementById('id-peca-modal').value = idPeca;
    document.getElementById('qtd-peca-modal').value = qtdSugerida && qtdSugerida > 0 ? qtdSugerida : 1;
    document.getElementById('obs-peca-modal').value = '';
    var modalJS = new bootstrap.Modal(document.getElementById('modalPedidoPeca'));
    modalJS.show();
}

// Envia o pedido de reposição de fato para o backend (salvar_pedido_peca.php)
function enviarPedidoReposicao() {
    const idPeca = document.getElementById('id-peca-modal').value;
    const qtd = document.getElementById('qtd-peca-modal').value;
    const obs = document.getElementById('obs-peca-modal').value;
    const btn = document.getElementById('btn-enviar-pedido');

    if (!idPeca || !qtd || qtd <= 0) {
        Swal.fire('Atenção', 'Informe uma quantidade válida para o pedido.', 'warning');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando...';

    const formData = new FormData();
    formData.append('id_peca', idPeca);
    formData.append('quantidade', qtd);
    formData.append('observacoes', obs);

    fetch('salvar_pedido_peca.php', {
        method: 'POST',
        body: formData
    })
    .then(resp => resp.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-1"></i> Enviar Pedido';

        var modalEl = document.getElementById('modalPedidoPeca');
        var modalJS = bootstrap.Modal.getInstance(modalEl);

        if (data.sucesso) {
            modalJS.hide();
            Swal.fire({
                title: 'Pedido Enviado!',
                text: 'O pedido de reposição foi registrado e já aparecerá no Dashboard, na área de Chamados para Reposição de Estoque.',
                icon: 'success',
                confirmButtonColor: '#3085d6'
            });
        } else {
            Swal.fire('Erro ao enviar pedido', data.mensagem || 'Tente novamente.', 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-1"></i> Enviar Pedido';
        Swal.fire('Erro de Conexão', 'Não foi possível enviar o pedido. Verifique sua conexão e tente novamente.', 'error');
    });
}

function atualizarTabelaPecas() {
    const tbody = document.getElementById('tbody_pecas');
    const containerHidden = document.getElementById('hidden_pecas');
    let html = '', htmlHidden = '', totalPecas = 0;

    pecasSelecionadas.forEach((p, index) => {
        const sub = p.valor * p.qtd;
        totalPecas += sub;
        html += `<tr>
                    <td class="small fw-bold">${p.nome}</td>
                    <td class="text-center small">${p.qtd}</td>
                    <td class="text-end small">R$ ${p.valor.toFixed(2)}</td>
                    <td class="text-end fw-bold text-success small">R$ ${sub.toFixed(2)}</td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 border-0" onclick="removerPeca(${index})"><i class="bi bi-trash"></i></button></td>
                 </tr>`;
        htmlHidden += `<input type="hidden" name="pecas_id[]" value="${p.id}"><input type="hidden" name="pecas_qtd[]" value="${p.qtd}">`;
    });

    if (pecasSelecionadas.length === 0) html = `<tr><td colspan="5" class="text-center text-muted small py-2">Nenhuma peça adicionada.</td></tr>`;

    tbody.innerHTML = html;
    containerHidden.innerHTML = htmlHidden;
    document.getElementById('valor_pecas').value = totalPecas.toFixed(2);
    calcularTotal();
}

function removerPeca(index) {
    pecasSelecionadas.splice(index, 1);
    atualizarTabelaPecas();
}

function mascaraMoeda(input) {
    let v = input.value.replace(/\D/g, "");
    if (v === "") v = "0";
    v = (parseInt(v, 10) / 100).toFixed(2) + "";
    v = v.replace(".", ",");
    v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
    input.value = v;
}

function calcularTotal() {
    let maoObraStr = document.getElementById('valor_mao_obra').value || "0";
    let maoObra = parseFloat(maoObraStr.replace(/\./g, '').replace(',', '.')) || 0;
    let pecas = parseFloat(document.getElementById('valor_pecas').value) || 0;
    let total = maoObra + pecas;
    
    document.getElementById('valor_total_visual').value = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}
</script>
<?php include '../includes/footer.php'; ?>