<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

$mensagem = ''; 

// SE O FORMULÁRIO FOR ENVIADO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $id_os = (int)$_POST['id_os'];
    $usuario_responsavel = $_POST['usuario_responsavel'] != '' ? (int)$_POST['usuario_responsavel'] : 'NULL';
    $valor_mao_obra = (float)$_POST['valor_mao_obra'];
    $valor_pecas = (float)$_POST['valor_pecas'];
    
    // Calcula o valor total automaticamente
    $valor_total = $valor_mao_obra + $valor_pecas;

    if ($id_os <= 0) {
        $mensagem = "Erro: É necessário selecionar uma Ordem de Serviço válida.";
    } else {
        
        // Comando para inserir o orçamento
        $sql = "INSERT INTO orcamentos (id_os, usuario_responsavel, valor_mao_obra, valor_pecas, valor_total, aprovado) 
                VALUES ($id_os, $usuario_responsavel, $valor_mao_obra, $valor_pecas, $valor_total, 0)";
        
        // Usamos um bloco try/catch para capturar o erro do MySQL de forma elegante
        try {
            if (mysqli_query($conn, $sql)) {
                $mensagem = "Orçamento cadastrado com sucesso! Valor Total: R$ " . number_format($valor_total, 2, ',', '.');
            }
        } catch (mysqli_sql_exception $e) {
            // Se o código do erro for 1062 (Duplicate entry), exibimos uma mensagem amigável
            if ($e->getCode() == 1062 || mysqli_errno($conn) == 1062) {
                $mensagem = "Aviso: Esta Ordem de Serviço já possui um orçamento cadastrado.";
            } else {
                $mensagem = "Erro ao cadastrar orçamento: " . $e->getMessage();
            }
        }
    }
}

// BUSCA APENAS AS ORDENS DE SERVIÇO QUE NÃO TÊM ORÇAMENTO AINDA (Usando LEFT JOIN + IS NULL)
$sql_os = "SELECT os.id_os, c.nome AS nome_cliente, e.modelo 
           FROM ordens_servico os
           JOIN clientes c ON os.id_cliente = c.id_cliente
           JOIN equipamentos e ON os.id_equipamento = e.id_equipamento
           LEFT JOIN orcamentos o ON os.id_os = o.id_os
           WHERE o.id_os IS NULL
           ORDER BY os.id_os DESC";
$result_os = mysqli_query($conn, $sql_os);

// BUSCA OS USUÁRIOS (TÉCNICOS E GERENTES) PARA VINCULAR
$sql_usuarios = "SELECT id_usuario, nome FROM usuarios WHERE perfil IN ('T', 'G') ORDER BY nome ASC";
$result_usuarios = mysqli_query($conn, $sql_usuarios);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Cadastrar Novo Orçamento</h1>
        <a href="listar.php" class="btn btn-outline-secondary">Voltar</a>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert alert-info shadow-sm">
            <?php echo $mensagem; ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0 border-start border-4 border-primary">
        <div class="card-body p-4">
            <form method="post" action="">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Selecione a Ordem de Serviço *</label>
                        <select class="form-select" name="id_os" required>
                            <option value="">-- Escolha a OS do Cliente --</option>
                            <?php 
                            if ($result_os && mysqli_num_rows($result_os) > 0) {
                                while ($os = mysqli_fetch_assoc($result_os)) {
                                    echo "<option value='{$os['id_os']}'>OS #{$os['id_os']} - {$os['nome_cliente']} ({$os['modelo']})</option>";
                                }
                            } else {
                                echo "<option value=''>Nenhuma OS pendente de orçamento disponível</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Usuário Responsável *</label>
                        <select class="form-select" name="usuario_responsavel" required>
                            <option value="">-- Selecione o Técnico/Gerente --</option>
                            <?php 
                            if ($result_usuarios && mysqli_num_rows($result_usuarios) > 0) {
                                while ($user = mysqli_fetch_assoc($result_usuarios)) {
                                    echo "<option value='{$user['id_usuario']}'>{$user['nome']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Valor da Mão de Obra (R$) *</label>
                        <input type="number" step="0.01" class="form-control" name="valor_mao_obra" id="valor_mao_obra" value="0.00" required oninput="calcularTotal()">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Valor das Peças (R$) *</label>
                        <input type="number" step="0.01" class="form-control" name="valor_pecas" id="valor_pecas" value="0.00" required oninput="calcularTotal()">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-primary">Valor Total Estimado (R$)</label>
                        <input type="text" class="form-control fw-bold bg-light text-primary" id="valor_total_visual" value="0,00" readonly>
                    </div>
                </div>

                <hr class="mt-4">
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-primary" type="submit">Salvar Orçamento</button>
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
    
    document.getElementById('valor_total_visual').value = total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

<?php include '../includes/footer.php'; ?>