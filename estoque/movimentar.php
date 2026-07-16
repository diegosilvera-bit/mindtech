<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Segurança: Apenas Gerentes, Estoquistas e Técnicos podem movimentar peças
verificarAcesso(['G', 'E', 'T']);

include '../config/conexao.php'; 

$mensagem = ''; 
$tipo_alerta = '';

// Verifica se o ID foi passado via GET e é válido
$id_peca = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_peca <= 0) {
    header("Location: listar.php");
    exit;
}

// Verifica se o formulário foi enviado (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $tipo_movimentacao = mysqli_real_escape_string($conn, $_POST['tipo_movimentacao']);
    $quantidade_movimento = (int)$_POST['quantidade'];
    $observacao = mysqli_real_escape_string($conn, trim($_POST['observacao']));
    
    // Pega o ID do usuário logado na sessão (Ajuste a variável conforme seu auth.php)
    // Se a sessão não existir no momento dos testes, gravamos como NULL
    $usuario_responsavel = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : 'NULL';

    if ($quantidade_movimento <= 0) {
        $mensagem = "A quantidade deve ser maior que zero.";
        $tipo_alerta = "warning";
    } else {
        // Busca a quantidade atual para validação de saída
        $sql_check = "SELECT quantidade_disponivel FROM pecas WHERE id_peca = $id_peca";
        $resultado_check = mysqli_query($conn, $sql_check);
        $linha_check = mysqli_fetch_assoc($resultado_check);
        $estoque_atual = $linha_check['quantidade_disponivel'];

        // Se for SAÍDA, valida se há estoque suficiente para não negativar
        if ($tipo_movimentacao == 'SAIDA' && $quantidade_movimento > $estoque_atual) {
            $mensagem = "Operação cancelada: Você está a tentar retirar mais peças do que o disponível no estoque atual ($estoque_atual un).";
            $tipo_alerta = "danger";
        } else {
            // Define o operador matemático com base no tipo
            $operador = ($tipo_movimentacao == 'ENTRADA') ? '+' : '-';
            
            // 1. Atualiza o saldo na tabela peças
            $sql_update = "UPDATE pecas SET quantidade_disponivel = quantidade_disponivel $operador $quantidade_movimento WHERE id_peca = $id_peca";
            
            if (mysqli_query($conn, $sql_update)) {
                // 2. Grava o histórico na tabela de movimentações
                $sql_insert = "INSERT INTO movimentacoes_estoque (id_peca, tipo_movimentacao, quantidade, usuario_responsavel, observacao) 
                               VALUES ($id_peca, '$tipo_movimentacao', $quantidade_movimento, $usuario_responsavel, '$observacao')";
                
                mysqli_query($conn, $sql_insert);

                $mensagem = "Movimentação de $tipo_movimentacao registada com sucesso!";
                $tipo_alerta = "success";
            } else {
                $mensagem = "Erro ao atualizar o estoque: " . mysqli_error($conn);
                $tipo_alerta = "danger";
            }
        }
    }
}

// Busca os dados atualizados da peça para exibir no ecrã
$sql_peca = "SELECT codigo, descricao, quantidade_disponivel, nivel_minimo FROM pecas WHERE id_peca = $id_peca";
$resultado_peca = mysqli_query($conn, $sql_peca);

if (!$resultado_peca || mysqli_num_rows($resultado_peca) == 0) {
    echo "Peça não encontrada.";
    exit;
}
$peca = mysqli_fetch_assoc($resultado_peca);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="bi bi-arrow-left-right text-white me-2"></i>Movimentar Estoque</h1>
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

    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body d-flex justify-content-between align-items-center p-4">
            <div>
                <span class="badge bg-secondary mb-2 fs-6"><?php echo htmlspecialchars($peca['codigo']); ?></span>
                <h5 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($peca['descricao']); ?></h5>
            </div>
            <div class="text-end">
                <p class="text-muted small mb-1">Estoque Atual</p>
                <h3 class="mb-0 fw-bold <?php echo ($peca['quantidade_disponivel'] <= $peca['nivel_minimo']) ? 'text-danger' : 'text-success'; ?>">
                    <?php echo $peca['quantidade_disponivel']; ?> un
                </h3>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 border-start border-4 border-primary">
        <div class="card-body p-4">
            <form method="POST" action="movimentar.php?id=<?php echo $id_peca; ?>">
                
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <label class="form-label fw-bold">Tipo de Movimento *</label>
                        <select class="form-select" name="tipo_movimentacao" required style="border-radius: 8px;">
                            <option value="" disabled selected>Selecione...</option>
                            <option value="ENTRADA">Entrada (+ Aumentar Estoque)</option>
                            <option value="SAIDA">Saída (- Reduzir Estoque)</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-4">
                        <label class="form-label fw-bold">Quantidade *</label>
                        <input type="number" min="1" class="form-control" name="quantidade" placeholder="Ex: 5" required style="border-radius: 8px;">
                    </div>
                    
                    <div class="col-md-5 mb-4">
                        <label class="form-label fw-bold">Motivo / Observação</label>
                        <input type="text" class="form-control" name="observacao" placeholder="Ex: Ajuste de inventário, quebra..." maxlength="255" style="border-radius: 8px;">
                    </div>
                </div>

                <hr class="my-3 text-muted opacity-20">
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border fw-bold px-4" style="border-radius: 8px;">Cancelar</a>
                    <button class="btn btn-primary fw-bold px-5 shadow-sm" type="submit" style="border-radius: 8px;">
                        <i class="bi bi-check-lg me-2"></i> Confirmar Movimentação
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>