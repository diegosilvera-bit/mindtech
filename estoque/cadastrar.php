<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Segurança: Apenas Gerentes, Estoquistas e Técnicos podem cadastrar peças
verificarAcesso(['G', 'E', 'T']);

include '../config/conexao.php'; 

$mensagem = ''; 
$tipo_alerta = '';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Captura os dados e protege contra injeção de SQL
    $codigo = mysqli_real_escape_string($conn, trim($_POST['codigo']));
    $descricao = mysqli_real_escape_string($conn, trim($_POST['descricao']));
    $quantidade = (int)$_POST['quantidade_disponivel'];
    $nivel_minimo = (int)$_POST['nivel_minimo'];
    
    // Converte a vírgula do valor monetário para ponto (formato da base de dados)
    $valor_unitario = str_replace(',', '.', $_POST['valor_unitario']);
    $valor_unitario = empty($valor_unitario) ? 0.00 : (float)$valor_unitario;

    if (empty($codigo) || empty($descricao)) {
        $mensagem = "Por favor, preencha os campos obrigatórios (Código e Descrição).";
        $tipo_alerta = "warning";
    } else {
        // Insere na tabela correta: pecas
        $sql = "INSERT INTO pecas (codigo, descricao, quantidade_disponivel, nivel_minimo, valor_unitario) 
                VALUES ('$codigo', '$descricao', $quantidade, $nivel_minimo, $valor_unitario)";
        
        if (mysqli_query($conn, $sql)) {
            $mensagem = "Peça cadastrada com sucesso no estoque!";
            $tipo_alerta = "success";
        } else {
            $mensagem = "Erro ao gravar na base de dados: " . mysqli_error($conn);
            $tipo_alerta = "danger";
        }
    }
}

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="bi bi-box-seam text-dark me-2"></i>Cadastrar Nova Peça</h1>
            <p class="text-muted small mb-0">Adicione um novo item ao catálogo de inventário da assistência.</p>
        </div>
        <a href="listar.php" class="btn btn-sm btn-outline-secondary fw-bold px-3">
            <i class="bi bi-arrow-left me-1"></i> Voltar à Lista
        </a>
    </div>

    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi <?php echo ($tipo_alerta == 'success') ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
            <?php echo $mensagem; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 border-start border-4 border-success">
        <div class="card-body p-4">
            <form method="POST" action="cadastrar.php">
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Código SKU *</label>
                        <input type="text" class="form-control" name="codigo" placeholder="Ex: TELA-IPH-11" required style="border-radius: 8px;">
                    </div>
                    
                    <div class="col-md-9 mb-3">
                        <label class="form-label fw-bold">Descrição da Peça *</label>
                        <input type="text" class="form-control" name="descricao" placeholder="Ex: Ecrã Frontal Display iPhone 11 Incell" required style="border-radius: 8px;">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Quantidade Inicial</label>
                        <input type="number" min="0" class="form-control" name="quantidade_disponivel" value="0" required style="border-radius: 8px;">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-danger">Nível Mínimo (Alerta)</label>
                        <input type="number" min="0" class="form-control" name="nivel_minimo" value="2" required style="border-radius: 8px;">
                        <small class="text-muted">Avisa quando o stock estiver baixo.</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Valor Unitário (€ / R$)</label>
                        <input type="text" class="form-control" name="valor_unitario" placeholder="0.00" style="border-radius: 8px;">
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-20">
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border fw-bold px-4" style="border-radius: 8px;">Cancelar</a>
                    <button class="btn btn-success fw-bold px-5 shadow-sm" type="submit" style="border-radius: 8px;">
                        <i class="bi bi-save me-2"></i> Gravar Nova Peça
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>