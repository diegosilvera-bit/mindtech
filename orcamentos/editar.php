<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA: Apenas perfis autorizados gerenciam orçamentos
verificarAcesso(['G', 'A']);

include '../config/conexao.php'; 

$mensagem = ''; 
$tipo_alerta = 'danger';
$id_orcamento = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_orcamento <= 0) {
    header("Location: listar.php");
    exit;
}

// 1. BUSCA OS DADOS ATUAIS DO ORÇAMENTO E DA O.S. VINCULADA
$sql_orc = "SELECT o.*, os.id_os, os.status AS os_status, c.nome AS nome_cliente 
            FROM orcamentos o 
            JOIN ordens_servico os ON o.id_os = os.id_os
            JOIN clientes c ON os.id_cliente = c.id_cliente
            WHERE o.id_orcamento = $id_orcamento";
$res_orc = mysqli_query($conn, $sql_orc);
$orc = mysqli_fetch_assoc($res_orc);

if (!$orc) {
    die("<div class='container mt-5'><div class='alert alert-danger'>Orçamento não encontrado.</div></div>");
}

// 2. PROCESSA O SALVAMENTO DAS ALTERAÇÕES (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Tratamento dos valores numéricos monetários
    $valor_mao_obra = isset($_POST['valor_mao_obra']) ? (float)$_POST['valor_mao_obra'] : 0.00;
    $valor_pecas = isset($_POST['valor_pecas']) ? (float)$_POST['valor_pecas'] : 0.00;
    $valor_total = $valor_mao_obra + $valor_pecas;
    $aprovado = (int)$_POST['aprovado']; // 0 = Pendente, 1 = Aprovado, 2 = Reprovado

    // Captura o técnico/usuário logado que está alterando
    $id_usuario_logado = $_SESSION['usuario']['id_usuario'] ?? $_SESSION['id_usuario'] ?? 'NULL';

    // Executa a atualização do orçamento
    $sql_update = "UPDATE orcamentos SET 
                    valor_pecas = $valor_pecas, 
                    valor_mao_obra = $valor_mao_obra, 
                    valor_total = $valor_total, 
                    aprovado = $aprovado,
                    usuario_responsavel = $id_usuario_logado
                   WHERE id_orcamento = $id_orcamento";
    
    if (mysqli_query($conn, $sql_update)) {
        $id_os_vinculada = (int)$orc['id_os'];
        
        // GATILHO DE AUTOMAÇÃO DE STATUS DA O.S. (O CORAÇÃO DA LOGICA)
        if ($aprovado == 1) {
            // Se aprovou -> O.S. vai direto para a bancada (Em Reparo)
            $sql_sync_os = "UPDATE ordens_servico SET status = 'EM_REPARO' WHERE id_os = $id_os_vinculada";
            mysqli_query($conn, $sql_sync_os);
        } 
        elseif ($aprovado == 2) {
            // Se reprovou -> O.S. é dada como Cancelada automaticamente
            $sql_sync_os = "UPDATE ordens_servico SET status = 'CANCELADO' WHERE id_os = $id_os_vinculada";
            mysqli_query($conn, $sql_sync_os);
        } 
        else {
            // Se voltou para Pendente (0) -> O.S. volta para Em Análise
            $sql_sync_os = "UPDATE ordens_servico SET status = 'EM_ANALISE' WHERE id_os = $id_os_vinculada";
            mysqli_query($conn, $sql_sync_os);
        }

        // Redireciona com mensagem de sucesso
        header("Location: listar.php?msg=orcamento_atualizado");
        exit;
    } else {
        $mensagem = "Erro ao atualizar o orçamento: " . mysqli_error($conn);
    }
}

// Busca a lista de peças cadastradas para alimentar o select
$sql_pecas = "SELECT id_peca, descricao, valor_unitario FROM pecas ORDER BY descricao ASC";
$res_pecas = mysqli_query($conn, $sql_pecas);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="bi bi-file-earmark-text text-white me-2"></i>Editar Orçamento #<?php echo $orc['id_orcamento']; ?></h1>
            <p class="text-white small mb-0 -">Vinculado à <strong>O.S. #<?php echo $orc['id_os']; ?></strong> | Cliente: <strong><?php echo htmlspecialchars($orc['nome_cliente']); ?></strong></p>
        </div>
        <a href="listar.php" class="btn btn-secondary px-3">
             Voltar à Lista
        </a>
    </div>

    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo $mensagem; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 border-start border-4 border-warning">
        <div class="card-body p-4">
            <form method="POST" action="editar.php?id=<?php echo $id_orcamento; ?>">
                
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <label class="form-label fw-bold">Valor da Mão de Obra (R$)*</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">R$</span>
                            <input type="number" step="0.01" min="0" class="form-control" id="valor_mao_obra" name="valor_mao_obra" 
                                   value="<?php echo $orc['valor_mao_obra']; ?>" oninput="calcularTotal()" required style="border-radius: 0 8px 8px 0;">
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">

    <label class="form-label fw-bold">Peça Utilizada / Alocada</label>

    <input
        type="text"
        class="form-control mb-2"
        id="peca_utilizada"
        name="peca_utilizada"
        placeholder="Digite a peça utilizada"
        value="<?php echo htmlspecialchars($orc['peca_utilizada'] ?? ''); ?>"
        style="border-radius:8px;"
    >

    <label class="form-label fw-bold">Valor da Peça (R$)</label>

    <div class="input-group">
        <span class="input-group-text">R$</span>
        <input
            type="number"
            class="form-control"
            id="valor_pecas"
            name="valor_pecas"
            step="0.01"
            min="0"
            value="<?php echo number_format($orc['valor_pecas'],2,'.',''); ?>"
            oninput="calcularTotal()"
            style="border-radius:0 8px 8px 0;"
        >
    </div>

</div>

                    <div class="col-md-4 mb-4">
                        <label class="form-label fw-bold text-dark">Situação do Orçamento</label>
                        <select class="form-select fw-bold text-center" name="aprovado" style="border-radius: 8px;">
                            <option value="0" class="text-warning fw-bold" <?php echo $orc['aprovado'] == 0 ? 'selected' : ''; ?>>⏳ Pendente (O.S. em Análise)</option>
                            <option value="1" class="text-success fw-bold" <?php echo $orc['aprovado'] == 1 ? 'selected' : ''; ?>>✅ APROVADO (O.S. vai para Reparo)</option>
                            <option value="2" class="text-danger fw-bold" <?php echo $orc['aprovado'] == 2 ? 'selected' : ''; ?>>❌ REPROVADO (O.S. será Cancelada)</option>
                        </select>
                    </div>
                </div>

                <div class="row align-items-center mt-2">
                    <div class="col-md-8 text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Alterar a situação aqui atualizará o andamento da Ordem de Serviço vinculada automaticamente no painel principal.
                    </div>
                    <div class="col-md-4 mb-3 text-end">
                        <label class="form-label fw-bold text-success d-block mb-1">Valor Total Geral</label>
                        <input type="text" class="form-control fw-bold text-success text-end fs-4 bg-light border-0" id="valor_total_visual" value="R$ 0,00" readonly style="border-radius: 8px;">
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-20">
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border fw-bold px-4" style="border-radius: 8px;">Cancelar</a>
                    <button class="btn btn-warning text-dark fw-bold px-5 shadow-sm" type="submit" style="border-radius: 8px;">
                        <i class="bi bi-check-circle-fill me-2"></i> Gravar Alterações
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
function calcularTotal() {
    var maoObra = parseFloat(document.getElementById('valor_mao_obra').value) || 0;
    var pecas = parseFloat(document.getElementById('valor_pecas').value) || 0;
    var total = maoObra + pecas;
    
    // Formata o mostrador dinâmico em Reais (R$)
    document.getElementById('valor_total_visual').value = total.toLocaleString('pt-BR', { 
        style: 'currency', 
        currency: 'BRL' 
    });
}

// Dispara o cálculo assim que a página termina de carregar para exibir o valor correto inicial
window.onload = function() {
    calcularTotal();
};
</script>

<?php include '../includes/footer.php'; ?>