<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

$mensagem = ''; 
$tipo_alerta = '';

// Pega o ID do equipamento que veio pela URL (ex: editar.php?id=5)
$id_equipamento = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_equipamento <= 0) {
    header("Location: listar.php");
    exit;
}

// -------------------------------------------------------------------------
// PROCESSA O FORMULÁRIO QUANDO FOR ENVIADO (POST)
// -------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Captura e limpa os dados digitados
    $id_cliente = (int)$_POST['id_cliente'];
    $tipo = mysqli_real_escape_string($conn, trim($_POST['tipo']));
    $marca = mysqli_real_escape_string($conn, trim($_POST['marca']));
    $modelo = mysqli_real_escape_string($conn, trim($_POST['modelo']));
    $numero_serie = mysqli_real_escape_string($conn, trim($_POST['numero_serie']));

    // Validação dos campos obrigatórios
    if ($id_cliente <= 0 || empty($tipo) || empty($marca) || empty($modelo)) {
        $mensagem = "Por favor, preencha todos os campos obrigatórios (*).";
        $tipo_alerta = "warning";
    } else {
        // Atualiza os dados do equipamento no banco de dados
        $sql_update = "UPDATE equipamentos SET 
                        id_cliente = $id_cliente, 
                        tipo = '$tipo', 
                        marca = '$marca', 
                        modelo = '$modelo', 
                        numero_serie = '$numero_serie' 
                      WHERE id_equipamento = $id_equipamento";

        if (mysqli_query($conn, $sql_update)) {
            $mensagem = "Equipamento atualizado com sucesso!";
            $tipo_alerta = "success";
        } else {
            $mensagem = "Erro ao atualizar o equipamento: " . mysqli_error($conn);
            $tipo_alerta = "danger";
        }
    }
}

// -------------------------------------------------------------------------
// BUSCA OS DADOS ATUAIS DO EQUIPAMENTO PARA PREENCHER O FORMULÁRIO
// -------------------------------------------------------------------------
$sql_equipamento = "SELECT * FROM equipamentos WHERE id_equipamento = $id_equipamento";
$result_equipamento = mysqli_query($conn, $sql_equipamento);
$equipamento = mysqli_fetch_assoc($result_equipamento);

// Se o equipamento não existir, volta para a listagem
if (!$equipamento) {
    header("Location: listar.php");
    exit;
}

// Busca todos os clientes para a caixa de seleção (Select)
$sql_clientes = "SELECT id_cliente, nome FROM clientes ORDER BY nome ASC";
$result_clientes = mysqli_query($conn, $sql_clientes);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">
                <i class="bi bi-laptop text-warning me-2"></i>Editar Equipamento
            </h1>
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

    <div class="card shadow-sm border-0 border-start border-4 border-warning">
        <div class="card-body p-4">
            
            <form method="POST" action="editar.php?id=<?php echo $id_equipamento; ?>">
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold text-dark">Proprietário / Cliente *</label>
                        <select class="form-select" name="id_cliente" required style="border-radius: 8px;">
                            <option value="" disabled>Selecione o proprietário do dispositivo...</option>
                            <?php 
                            if ($result_clientes && mysqli_num_rows($result_clientes) > 0) {
                                while ($cliente = mysqli_fetch_assoc($result_clientes)) {
                                    $selecionado = ($cliente['id_cliente'] == $equipamento['id_cliente']) ? 'selected' : '';
                                    echo "<option value='{$cliente['id_cliente']}' {$selecionado}>" . htmlspecialchars($cliente['nome']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-dark">Tipo de Dispositivo *</label>
                        <input type="text" class="form-control" name="tipo" 
                               value="<?php echo htmlspecialchars($equipamento['tipo']); ?>" 
                               placeholder="Ex: Notebook, Smartphone, Consola" required style="border-radius: 8px;">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-dark">Marca *</label>
                        <input type="text" class="form-control" name="marca" 
                               value="<?php echo htmlspecialchars($equipamento['marca']); ?>" 
                               placeholder="Ex: Asus, Apple, Samsung" required style="border-radius: 8px;">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-dark">Modelo *</label>
                        <input type="text" class="form-control" name="modelo" 
                               value="<?php echo htmlspecialchars($equipamento['modelo']); ?>" 
                               placeholder="Ex: Rog Strix G15, iPhone 14 Pro" required style="border-radius: 8px;">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold text-dark">Número de Série / IMEI (Opcional)</label>
                        <input type="text" class="form-control" name="numero_serie" 
                               value="<?php echo htmlspecialchars($equipamento['numero_serie'] ?? ''); ?>" 
                               placeholder="Ex: NS-98234149812-X" style="border-radius: 8px;">
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-20">
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border fw-bold px-4" style="border-radius: 8px;">Cancelar</a>
                    <button class="btn btn-warning text-dark fw-bold px-5 shadow-sm" type="submit" style="border-radius: 8px;">
                        <i class="bi bi-save me-2"></i> Salvar Alterações
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>