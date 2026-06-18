<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

verificarAcesso(['G', 'A']);

include '../config/conexao.php'; 

$mensagem = ''; 
$sucesso = false;

$id_orcamento = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_orcamento <= 0) {
    header("Location: listar.php");
    exit;
}

// BUSCA OS DADOS DO ORÇAMENTO E DA O.S. VINCULADA
$sql_orc = "SELECT o.*, os.id_os, c.nome AS nome_cliente 
            FROM orcamentos o 
            JOIN ordens_servico os ON o.id_os = os.id_os
            JOIN clientes c ON os.id_cliente = c.id_cliente
            WHERE o.id_orcamento = $id_orcamento";
$res_orc = mysqli_query($conn, $sql_orc);
$orc = mysqli_fetch_assoc($res_orc);

if (!$orc) {
    header("Location: listar.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $valor_mao_obra = empty($_POST['valor_mao_obra']) ? 0 : (float)$_POST['valor_mao_obra'];
    $valor_pecas = empty($_POST['valor_pecas']) ? 0 : (float)$_POST['valor_pecas'];
    $valor_total = $valor_mao_obra + $valor_pecas;
    $aprovado = (int)$_POST['aprovado'];

    $sql_update = "UPDATE orcamentos SET 
                    valor_mao_obra = $valor_mao_obra, 
                    valor_pecas = $valor_pecas, 
                    valor_total = $valor_total, 
                    aprovado = $aprovado 
                  WHERE id_orcamento = $id_orcamento";

    if (mysqli_query($conn, $sql_update)) {
        $mensagem = "Valores e status do orçamento atualizados com sucesso!";
        $sucesso = true;
        
        // MÁGICA: Se o orçamento foi aprovado, avança a O.S. para "EM_REPARO" automaticamente!
        if ($aprovado == 1) {
            mysqli_query($conn, "UPDATE ordens_servico SET status = 'EM_REPARO' WHERE id_os = {$orc['id_os']} AND status = 'EM_ANALISE'");
        }
        
        // Atualiza a variável para exibir no ecrã sem precisar recarregar a página
        $orc['valor_mao_obra'] = $valor_mao_obra;
        $orc['valor_pecas'] = $valor_pecas;
        $orc['aprovado'] = $aprovado;
    } else {
        $mensagem = "Erro ao atualizar: " . mysqli_error($conn);
    }
}

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Editar Orçamento <span class="text-primary">(O.S. #<?= $orc['id_os'] ?>)</span></h1>
        <a href="listar.php" class="btn btn-secondary">Voltar para Lista</a>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert <?= $sucesso ? 'alert-success' : 'alert-danger' ?> shadow-sm fw-bold"><?= $mensagem ?></div>
    <?php } ?>

    <div class="card shadow-sm border-0 border-start border-4 border-warning">
        <div class="card-header bg-white pt-3 pb-0 border-0">
            <h5 class="fw-bold text-muted">Cliente: <?= htmlspecialchars($orc['nome_cliente']) ?></h5>
        </div>
        <div class="card-body p-4">
            <form method="post" action="editar.php?id=<?= $id_orcamento ?>">
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Resposta do Cliente (Aprovação)</label>
                        <select class="form-select form-select-lg <?= $orc['aprovado'] == 1 ? 'border-success text-success fw-bold' : '' ?>" name="aprovado">
                            <option value="0" <?= $orc['aprovado'] == 0 ? 'selected' : '' ?>>Pendente (Aguardando resposta do cliente)</option>
                            <option value="1" <?= $orc['aprovado'] == 1 ? 'selected' : '' ?>>Aprovado (Pode iniciar o serviço!)</option>
                        </select>
                        <?php if($orc['aprovado'] == 1): ?>
                            <small class="text-success"><i class="bi bi-check-circle-fill"></i> A O.S. vinculada foi movida para "Em Reparo".</small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row bg-light p-3 rounded border mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Mão de Obra (R$)</label>
                        <input type="number" step="0.01" class="form-control" name="valor_mao_obra" id="valor_mao_obra" value="<?= $orc['valor_mao_obra'] ?>" oninput="calcularTotal()">
                    </div>

<div class="col-md-4 mb-3">
    <label class="form-label fw-bold" for="valor_pecas">Peça e Valor (R$)</label>
    <select class="form-control" name="valor_pecas" id="valor_pecas" onchange="calcularTotal()">
        <option value="0.00">Selecione uma peça...</option>
        <?php
        // Busca as peças no banco de dados
        $sql_select_pecas = "SELECT descricao, valor_unitario FROM pecas ORDER BY descricao ASC";
        $result_select_pecas = mysqli_query($conn, $sql_select_pecas);

        if ($result_select_pecas && mysqli_num_rows($result_select_pecas) > 0) {
            while ($peca = mysqli_fetch_assoc($result_select_pecas)) {
                $preco_ponto = $peca['valor_unitario'];
                $preco_virgula = number_format($peca['valor_unitario'], 2, ',', '.');
                
                // MÁGICA DA EDIÇÃO: Verifica se o valor desta peça é igual ao salvo no orçamento
                // Se for, adiciona o atributo 'selected' para já vir pré-selecionado
                $selected = ($preco_ponto == $orc['valor_pecas']) ? 'selected' : '';

                echo "<option value='{$preco_ponto}' {$selected}>{$peca['descricao']} - R$ {$preco_virgula}</option>";
            }
        }
        ?>
    </select>
</div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-success">Valor Total a Cobrar (R$)</label>
                        <input type="text" class="form-control fw-bold text-success fs-5" id="valor_total_visual" readonly>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-warning text-dark fw-bold" type="submit">Salvar Alterações</button>
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
    document.getElementById('valor_total_visual').value = total.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
}
// Calcula ao abrir a página
window.onload = calcularTotal;
</script>

<?php include '../includes/footer.php'; ?>