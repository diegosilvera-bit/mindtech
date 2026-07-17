<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 
verificarAcesso(['G', 'A']);
include '../config/conexao.php'; 

$mensagem = ''; 
$tipo_alerta = 'danger';
$id_orcamento = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_orcamento <= 0) { header("Location: listar.php"); exit; }

$sql_orc = "SELECT o.*, os.id_os, os.status AS os_status, c.nome AS nome_cliente FROM orcamentos o JOIN ordens_servico os ON o.id_os = os.id_os JOIN clientes c ON os.id_cliente = c.id_cliente WHERE o.id_orcamento = $id_orcamento";
$res_orc = mysqli_query($conn, $sql_orc);
$orc = mysqli_fetch_assoc($res_orc);

if (!$orc) { die("<div class='container mt-5'><div class='alert alert-danger'>Orçamento não encontrado.</div></div>"); }

$id_os_vinculada = (int)$orc['id_os'];

// --- PROCESSA ADIÇÃO DE PEÇA NO ORÇAMENTO / O.S. ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adicionar_peca'])) {
    $id_peca = (int)$_POST['id_peca'];
    $quantidade = (int)$_POST['quantidade'];

    if ($id_peca > 0 && $quantidade > 0) {
        // Insere na O.S.
        mysqli_query($conn, "INSERT INTO os_peca (id_os, id_peca, quantidade_usada) VALUES ($id_os_vinculada, $id_peca, $quantidade)");
        
        // Recalcula o total das peças
        $calc = mysqli_query($conn, "SELECT SUM(p.valor_unitario * op.quantidade_usada) AS total FROM os_peca op JOIN pecas p ON op.id_peca = p.id_peca WHERE op.id_os = $id_os_vinculada");
        $row_calc = mysqli_fetch_assoc($calc);
        $novo_total_pecas = (float)$row_calc['total'];
        
        // Atualiza o orçamento com o novo valor
        $mao_obra_atual = (float)$orc['valor_mao_obra'];
        $novo_total_geral = $novo_total_pecas + $mao_obra_atual;
        mysqli_query($conn, "UPDATE orcamentos SET valor_pecas = $novo_total_pecas, valor_total = $novo_total_geral WHERE id_orcamento = $id_orcamento");
        
        header("Location: editar.php?id=$id_orcamento&msg=peca_adicionada");
        exit;
    }
}

// --- PROCESSA EDIÇÃO PRINCIPAL (MÃO DE OBRA E STATUS) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_orcamento'])) {
    $valor_mao_obra = isset($_POST['valor_mao_obra']) ? (float)$_POST['valor_mao_obra'] : 0.00;
    $valor_pecas = (float)$orc['valor_pecas']; // Mantém o que já está salvo via peças
    $valor_total = $valor_mao_obra + $valor_pecas;
    $aprovado = (int)$_POST['aprovado']; 
    $id_usuario_logado = $_SESSION['usuario']['id_usuario'] ?? $_SESSION['id_usuario'] ?? 'NULL';

    $sql_update = "UPDATE orcamentos SET valor_mao_obra = $valor_mao_obra, valor_total = $valor_total, aprovado = $aprovado, usuario_responsavel = $id_usuario_logado WHERE id_orcamento = $id_orcamento";
    
    if (mysqli_query($conn, $sql_update)) {
        if ($aprovado == 1) {
            mysqli_query($conn, "UPDATE ordens_servico SET status = 'EM_REPARO' WHERE id_os = $id_os_vinculada");
        } elseif ($aprovado == 2) {
            mysqli_query($conn, "UPDATE ordens_servico SET status = 'CANCELADO' WHERE id_os = $id_os_vinculada");
        } else {
            mysqli_query($conn, "UPDATE ordens_servico SET status = 'EM_ANALISE' WHERE id_os = $id_os_vinculada");
        }
        header("Location: listar.php?msg=orcamento_atualizado");
        exit;
    } else {
        $mensagem = "Erro ao atualizar: " . mysqli_error($conn);
    }
}

// Busca as peças disponíveis no estoque
$res_pecas = mysqli_query($conn, "SELECT id_peca, descricao, valor_unitario FROM pecas ORDER BY descricao ASC");

// Busca as peças já vinculadas à O.S. deste orçamento
$sql_os_pecas = "SELECT p.descricao, p.valor_unitario, SUM(op.quantidade_usada) AS total_quantidade, (SUM(op.quantidade_usada) * p.valor_unitario) AS subtotal FROM os_peca op JOIN pecas p ON op.id_peca = p.id_peca WHERE op.id_os = $id_os_vinculada GROUP BY p.id_peca, p.descricao, p.valor_unitario";
$res_os_pecas = mysqli_query($conn, $sql_os_pecas);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="bi bi-file-earmark-text text-warning me-2"></i>Editar Orçamento #<?php echo $orc['id_orcamento']; ?></h1>
            <p class="text-white small mb-0">Vinculado à <strong>O.S. #<?php echo $orc['id_os']; ?></strong> | Cliente: <strong><?php echo htmlspecialchars($orc['nome_cliente']); ?></strong></p>
        </div>
        <a href="listar.php" class="btn btn-secondary px-3">Voltar à Lista</a>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'peca_adicionada'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">Peça alocada com sucesso! <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row">
        <!-- PAINEL DE PEÇAS (Independente) -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 border-start border-4 border-success h-100">
                <div class="card-header bg-white py-3 fw-bold"><i class="bi bi-tools me-2"></i>Peças e Componentes</div>
                <div class="card-body">
                    
                    <form method="POST" class="row gx-2 gy-2 align-items-end mb-4 bg-light p-2 rounded">
                        <input type="hidden" name="adicionar_peca" value="1">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold">Adicionar Peça</label>
                            <select name="id_peca" class="form-select form-select-sm" required>
                                <option value="" disabled selected>Escolha...</option>
                                <?php while ($p = mysqli_fetch_assoc($res_pecas)): ?>
                                    <option value="<?php echo $p['id_peca']; ?>"><?php echo htmlspecialchars($p['descricao']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Qtd.</label>
                            <input type="number" name="quantidade" class="form-control form-control-sm" value="1" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-success btn-sm w-100 fw-bold">+ Add</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr><th>Descrição</th><th class="text-center">Qtd</th><th class="text-end">Subtotal</th></tr>
                            </thead>
                            <tbody>
                                <?php if ($res_os_pecas && mysqli_num_rows($res_os_pecas) > 0): ?>
                                    <?php while ($row_p = mysqli_fetch_assoc($res_os_pecas)): ?>
                                        <tr>
                                            <td class="small fw-bold"><?php echo htmlspecialchars($row_p['descricao']); ?></td>
                                            <td class="text-center small"><?php echo $row_p['total_quantidade']; ?></td>
                                            <td class="text-end small fw-bold text-success">R$ <?php echo number_format($row_p['subtotal'], 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center py-2 text-muted small">Nenhuma peça nesta O.S.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAINEL PRINCIPAL DO ORÇAMENTO -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 border-start border-4 border-warning h-100">
                <div class="card-header bg-white py-3 fw-bold"><i class="bi bi-cash-coin me-2"></i>Fechamento e Status</div>
                <div class="card-body p-4">
                    <form method="POST" action="editar.php?id=<?php echo $id_orcamento; ?>">
                        <input type="hidden" name="editar_orcamento" value="1">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Total em Peças (R$)</label>
                            <input type="number" class="form-control bg-light" id="valor_pecas" value="<?php echo number_format($orc['valor_pecas'], 2, '.', ''); ?>" readonly>
                            <small class="text-muted">Calculado automaticamente com base nas peças ao lado.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Valor da Mão de Obra (R$)*</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="valor_mao_obra" name="valor_mao_obra" value="<?php echo $orc['valor_mao_obra']; ?>" oninput="calcularTotal()" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Situação do Orçamento</label>
                            <select class="form-select fw-bold" name="aprovado">
                                <option value="0" class="text-warning" <?php echo $orc['aprovado'] == 0 ? 'selected' : ''; ?>>⏳ Pendente (O.S. em Análise)</option>
                                <option value="1" class="text-success" <?php echo $orc['aprovado'] == 1 ? 'selected' : ''; ?>>✅ APROVADO (O.S. vai para Reparo)</option>
                                <option value="2" class="text-danger" <?php echo $orc['aprovado'] == 2 ? 'selected' : ''; ?>>❌ REPROVADO (O.S. será Cancelada)</option>
                            </select>
                        </div>

                        <div class="p-3 bg-light rounded text-end mb-4">
                            <label class="form-label fw-bold text-success d-block mb-1">Valor Total Geral</label>
                            <input type="text" class="form-control fw-bold text-success text-end fs-4 border-0 bg-transparent p-0" id="valor_total_visual" value="R$ 0,00" readonly>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="listar.php" class="btn btn-light border fw-bold px-4">Cancelar</a>
                            <button class="btn btn-warning text-dark fw-bold px-5 shadow-sm" type="submit"><i class="bi bi-check-circle-fill me-2"></i> Gravar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calcularTotal() {
    var maoObra = parseFloat(document.getElementById('valor_mao_obra').value) || 0;
    var pecas = parseFloat(document.getElementById('valor_pecas').value) || 0;
    var total = maoObra + pecas;
    
    document.getElementById('valor_total_visual').value = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}
window.onload = calcularTotal;
</script>
<?php include '../includes/footer.php'; ?>