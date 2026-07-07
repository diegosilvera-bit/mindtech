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

// Verifica se veio de dentro de uma O.S. específica via URL
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
    $valor_mao_obra = isset($_POST['valor_mao_obra']) ? (float)$_POST['valor_mao_obra'] : 0.00;
    $valor_pecas = isset($_POST['valor_pecas']) ? (float)$_POST['valor_pecas'] : 0.00;
    $valor_total = $valor_mao_obra + $valor_pecas;
    
    $id_usuario_logado = $_SESSION['usuario']['id_usuario'] ?? $_SESSION['id_usuario'] ?? 'NULL';

    if ($id_os_selecionada <= 0) {
        $mensagem = "Por favor, selecione uma Ordem de Serviço válida.";
    } else {
        // Trava de segurança no banco para não duplicar orçamento na mesma O.S.
        $valida_duplicado = mysqli_query($conn, "SELECT id_orcamento FROM orcamentos WHERE id_os = $id_os_selecionada");
        
        if (mysqli_num_rows($valida_duplicado) > 0) {
            $mensagem = "Já existe um orçamento cadastrado para esta Ordem de Serviço.";
        } else {
            $sql_insert = "INSERT INTO orcamentos (id_os, valor_pecas, valor_mao_obra, valor_total, aprovado, usuario_responsavel) 
                           VALUES ($id_os_selecionada, $valor_pecas, $valor_mao_obra, $valor_total, 0, $id_usuario_logado)";

            if (mysqli_query($conn, $sql_insert)) {
                $id_gerado = mysqli_insert_id($conn);
                header("Location: editar.php?id=" . $id_gerado . "&msg=criado");
                exit;
            } else {
                $mensagem = "Erro ao gerar orçamento: " . mysqli_error($conn);
            }
        }
    }
}

// BUSCA INTELIGENTE DE O.S. DISPONÍVEIS PARA ORÇAMENTO
if ($id_os_url > 0) {
    // Caso A: Veio clicado de dentro de uma O.S. -> Traz APENAS ela pré-selecionada
    $sql_os = "SELECT os.id_os, c.nome AS nome_cliente, e.modelo 
               FROM ordens_servico os
               JOIN clientes c ON os.id_cliente = c.id_cliente
               JOIN equipamentos e ON os.id_equipamento = e.id_equipamento
               WHERE os.id_os = $id_os_url";
} else {
    // Caso B: Clicou em "Novo Orçamento" no menu -> Traz todas as O.S. que ainda NÃO têm orçamento
    $sql_os = "SELECT os.id_os, c.nome AS nome_cliente, e.modelo 
               FROM ordens_servico os
               JOIN clientes c ON os.id_cliente = c.id_cliente
               JOIN equipamentos e ON os.id_equipamento = e.id_equipamento
               WHERE os.status != 'CANCELADO' 
               AND os.id_os NOT IN (SELECT id_os FROM orcamentos WHERE id_os IS NOT NULL)
               ORDER BY os.id_os DESC";
}

$res_os = mysqli_query($conn, $sql_os);

// Busca Peças
$sql_pecas = "SELECT id_peca, descricao, valor_unitario FROM pecas ORDER BY descricao ASC";
$res_pecas = mysqli_query($conn, $sql_pecas);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="bi bi-calculator text-success me-2"></i>Gerar Novo Orçamento</h1>
        </div>
        <a href="listar.php" class="btn btn-sm btn-outline-secondary fw-bold px-3">
            <i class="bi bi-arrow-left me-1"></i> Voltar à Lista
        </a>
    </div>

    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo $mensagem; ?>
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
                            echo "<option value='' disabled>Nenhuma Ordem de Serviço aguardando orçamento no momento.</option>";
                        }
                        ?>
                    </select>
                    <small class="text-muted">Apenas Ordens de Serviço que ainda não possuem orçamento aparecem nesta lista.</small>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-4">
                        <label class="form-label fw-bold">Valor da Mão de Obra (R$)*</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">R$</span>
                            <input type="number" step="0.01" min="0" class="form-control" id="valor_mao_obra" name="valor_mao_obra" value="0.00" oninput="calcularTotal()" required>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <label class="form-label fw-bold">Peça Utilizada (Opcional)</label>
                        <select class="form-select" id="valor_pecas" name="valor_pecas" onchange="calcularTotal()" style="border-radius: 8px;">
                            <option value="0.00">Nenhuma peça (R$ 0,00)</option>
                            <?php 
                            if ($res_pecas && mysqli_num_rows($res_pecas) > 0) {
                                while ($peca = mysqli_fetch_assoc($res_pecas)) {
                                    $preco_ponto = $peca['valor_unitario'];
                                    $preco_virgula = number_format($peca['valor_unitario'], 2, ',', '.');
                                    echo "<option value='{$preco_ponto}'>" . htmlspecialchars($peca['descricao']) . " - R$ {$preco_virgula}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-4 mb-4">
                        <label class="form-label fw-bold text-success">Valor Total Calculado</label>
                        <input type="text" class="form-control fw-bold text-success fs-4 bg-light border-0 text-end" id="valor_total_visual" value="R$ 0,00" readonly style="border-radius: 8px;">
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-20">
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border fw-bold px-4" style="border-radius: 8px;">Cancelar</a>
                    <button class="btn btn-success fw-bold px-5 shadow-sm" type="submit" style="border-radius: 8px;">
                        <i class="bi bi-check-lg me-2"></i> Gravar Orçamento
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
    
    document.getElementById('valor_total_visual').value = total.toLocaleString('pt-BR', { 
        style: 'currency', 
        currency: 'BRL' 
    });
}
</script>

<?php include '../includes/footer.php'; ?>