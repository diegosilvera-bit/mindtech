<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA
verificarAcesso(['G', 'A']);

include '../config/conexao.php'; 

// SISTEMA ANTI-DUPLICAÇÃO: Se clicar no botão de gerar orçamento lá dentro da O.S., ele verifica se já existe.
$id_os_url = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_os_url > 0) {
    $check = mysqli_query($conn, "SELECT id_orcamento FROM orcamentos WHERE id_os = $id_os_url");
    if (mysqli_num_rows($check) > 0) {
        $orc = mysqli_fetch_assoc($check);
        // Se já existe, manda direto para a página de edição desse orçamento
        header("Location: editar.php?id=" . $orc['id_orcamento']);
        exit;
    }
}

$mensagem = ''; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $id_os = (int)$_POST['id_os'];
    // Tratamento seguro para os valores financeiros
    $valor_mao_obra = empty($_POST['valor_mao_obra']) ? 0 : (float)$_POST['valor_mao_obra'];
    $valor_pecas = empty($_POST['valor_pecas']) ? 0 : (float)$_POST['valor_pecas'];
    
    // Calcula o total pelo PHP para segurança
    $valor_total = $valor_mao_obra + $valor_pecas;

    if ($id_os <= 0) {
        $mensagem = "Selecione uma Ordem de Serviço válida.";
    } else {
        $sql = "INSERT INTO orcamentos (id_os, valor_mao_obra, valor_pecas, valor_total, aprovado) 
                VALUES ($id_os, $valor_mao_obra, $valor_pecas, $valor_total, 0)";
        
        if (mysqli_query($conn, $sql)) {
            $mensagem = "Orçamento gerado com sucesso! Aguardando aprovação do cliente.";
        } else {
            $mensagem = "Erro ao gerar orçamento: " . mysqli_error($conn);
        }
    }
}

// BUSCA APENAS AS O.S. QUE AINDA NÃO TÊM ORÇAMENTO GERADO E NÃO ESTÃO CANCELADAS
$sql_pendentes = "SELECT os.id_os, c.nome, e.modelo
                  FROM ordens_servico os
                  JOIN clientes c ON os.id_cliente = c.id_cliente
                  JOIN equipamentos e ON os.id_equipamento = e.id_equipamento
                  LEFT JOIN orcamentos o ON os.id_os = o.id_os
                  WHERE o.id_orcamento IS NULL AND os.status != 'CANCELADO'
                  ORDER BY os.id_os ASC";
$res_pendentes = mysqli_query($conn, $sql_pendentes);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Gerar Novo Orçamento</h1>
        <a href="listar.php" class="btn btn-secondary">Voltar para Lista</a>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert alert-info shadow-sm fw-bold"><?php echo $mensagem; ?></div>
    <?php } ?>

    <div class="card shadow-sm border-0 border-start border-4 border-warning">
        <div class="card-body p-4">
            <form method="post" action="cadastrar.php">
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Selecione a Ordem de Serviço *</label>
                    <select class="form-select form-select-lg" name="id_os" required>
                        <option value="">-- O.S. pendentes de orçamento --</option>
                        <?php 
                        if ($res_pendentes && mysqli_num_rows($res_pendentes) > 0) {
                            while($p = mysqli_fetch_assoc($res_pendentes)) {
                                $selected = ($id_os_url == $p['id_os']) ? 'selected' : '';
                                echo "<option value='{$p['id_os']}' $selected>O.S. #{$p['id_os']} — Cliente: {$p['nome']} ({$p['modelo']})</option>";
                            }
                        } else {
                            echo "<option value='' disabled>Nenhuma O.S. aguardando orçamento no momento.</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="row bg-light p-3 rounded border mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Mão de Obra (R$)</label>
                        <input type="number" step="0.01" class="form-control" name="valor_mao_obra" id="valor_mao_obra" value="0.00" oninput="calcularTotal()">
                    </div>

                    <div class="col-md-4 mb-3">
    <label class="form-label fw-bold" for="valor_pecas">Peça e Valor (R$)</label>
    <select class="form-control" name="valor_pecas" id="valor_pecas" onchange="calcularTotal()">
        <option value="0.00">Selecione uma peça...</option>
        <?php
        // Cria a consulta no banco (buscando a descrição e o valor)
        $sql_select_pecas = "SELECT descricao, valor_unitario FROM pecas ORDER BY descricao ASC";
        $result_select_pecas = mysqli_query($conn, $sql_select_pecas);

        // Verifica se existem peças cadastradas
        if ($result_select_pecas && mysqli_num_rows($result_select_pecas) > 0) {
            while ($peca = mysqli_fetch_assoc($result_select_pecas)) {
                // O 'value' da option recebe o preço, para que o seu JS calcularTotal() continue funcionando
                $preco_ponto = $peca['valor_unitario'];
                $preco_virgula = number_format($peca['valor_unitario'], 2, ',', '.');
                
                echo "<option value='{$preco_ponto}'>{$peca['descricao']} - R$ {$preco_virgula}</option>";
            }
        }
        ?>
    </select>
</div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-success">Valor Total a Cobrar (R$)</label>
                        <input type="text" class="form-control fw-bold text-success fs-5" id="valor_total_visual" value="R$ 0,00" readonly>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-warning text-dark fw-bold" type="submit">Gravar Orçamento</button>
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
</script>

<?php include '../includes/footer.php'; ?>