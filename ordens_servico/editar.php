<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>

<style>
    .ts-dropdown .highlight {
        background: transparent !important;
        color: inherit !important;
        text-decoration: none !important;
        font-weight: bold !important;
    }
</style>

<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

verificarAcesso(['G', 'A', 'T']);
include '../config/conexao.php'; 

$mensagem = ''; 
$sucesso = false;
$id_os = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_os <= 0) { header("Location: listar.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_tecnico = !empty($_POST['id_usuario_responsavel']) ? (int)$_POST['id_usuario_responsavel'] : 'NULL';
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $observacoes = mysqli_real_escape_string($conn, trim($_POST['observacoes']));
    
    // Captação da Data Atualizada
    $data_prevista = !empty($_POST['data_prevista_entrega']) ? "'" . mysqli_real_escape_string($conn, $_POST['data_prevista_entrega']) . " 23:59:59'" : "NULL";

    $sql_update = "UPDATE ordens_servico SET 
                    id_usuario_responsavel = $id_tecnico,
                    status = '$status',
                    observacoes = '$observacoes',
                    data_prevista_entrega = $data_prevista
                   WHERE id_os = $id_os";

    if (mysqli_query($conn, $sql_update)) {
        $mensagem = "Ordem de Serviço atualizada com sucesso!";
        $sucesso = true;
    } else {
        $mensagem = "Erro ao atualizar O.S: " . mysqli_error($conn);
    }
}

$sql_os = "SELECT os.*, c.nome AS nome_cliente, e.marca, e.modelo 
           FROM ordens_servico os 
           JOIN clientes c ON os.id_cliente = c.id_cliente
           JOIN equipamentos e ON os.id_equipamento = e.id_equipamento
           WHERE os.id_os = $id_os";
$res_os = mysqli_query($conn, $sql_os);
$os = mysqli_fetch_assoc($res_os);

if (!$os) { die("O.S. não encontrada."); }

$data_prevista_input = '';
if (!empty($os['data_prevista_entrega']) && $os['data_prevista_entrega'] != '0000-00-00 00:00:00') {
    $data_prevista_input = date('Y-m-d', strtotime($os['data_prevista_entrega']));
}

$sql_tecnicos = "SELECT id_usuario, nome FROM usuarios WHERE perfil IN ('T', 'G') ORDER BY nome ASC";
$res_tecnicos = mysqli_query($conn, $sql_tecnicos);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Editar O.S. #<?php echo $id_os; ?></h1>
        <a href="listar.php" class="btn btn-sm btn-outline-secondary fw-bold"><i class="bi bi-arrow-left"></i> Voltar</a>
    </div>

    <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $sucesso ? 'success' : 'danger'; ?> shadow-sm">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 border-start border-4 border-primary">
        <div class="card-body p-4">
            <form method="POST">
                <div class="row bg-light p-3 rounded mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Cliente</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($os['nome_cliente']); ?>" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Equipamento</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($os['marca'] . ' ' . $os['modelo']); ?>" disabled>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Técnico Responsável</label>
                        <select class="form-select" name="id_usuario_responsavel" id="id_usuario_responsavel">
                            <option value="" disabled <?php echo empty($os['id_usuario_responsavel']) ? 'selected' : ''; ?>>-- Não alocado --</option>
                            <?php 
                            if ($res_tecnicos) {
                                while ($tec = mysqli_fetch_assoc($res_tecnicos)) {
                                    $sel = ($os['id_usuario_responsavel'] == $tec['id_usuario']) ? 'selected' : '';
                                    echo "<option value='{$tec['id_usuario']}' $sel>{$tec['nome']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Status Atual</label>
                        <select class="form-select fw-bold" name="status">
                            <option value="EM_ANALISE" <?php echo $os['status'] == 'EM_ANALISE' ? 'selected' : ''; ?>>Em Análise</option>
                            <option value="EM_REPARO" <?php echo $os['status'] == 'EM_REPARO' ? 'selected' : ''; ?>>Em Reparo</option>
                            <option value="AGUARDANDO_PECA" <?php echo $os['status'] == 'AGUARDANDO_PECA' ? 'selected' : ''; ?>>Aguardando Peça</option>
                            <option value="FINALIZADO" <?php echo $os['status'] == 'FINALIZADO' ? 'selected' : ''; ?>>Finalizado</option>
                            <option value="CANCELADO" <?php echo $os['status'] == 'CANCELADO' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-primary">Previsão de Entrega</label>
                        <input type="date" class="form-control border-primary" name="data_prevista_entrega" value="<?php echo $data_prevista_input; ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Problema Relatado / Observações Técnicas</label>
                    <textarea class="form-control" name="observacoes" rows="5"><?php echo htmlspecialchars($os['observacoes']); ?></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-primary fw-bold" type="submit">Guardar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Regra de busca estrita: o item deve obrigatoriamente INICIAR com o termo pesquisado
    function funcaoBuscaEstrita(search) {
        const query = search.trim().toLowerCase();
        return function(item) {
            if (!query) return 1;
            return item.text.toLowerCase().startsWith(query) ? 1 : 0;
        };
    }

    // Inicializa o Buscador de Técnicos
    const buscadorTecnico = new TomSelect("#id_usuario_responsavel", {
        create: false,
        placeholder: "-- Não alocado --",
        allowEmptyOption: false, // Impede a seleção da opção em branco
        score: funcaoBuscaEstrita
    });
</script>

<?php include '../includes/footer.php'; ?>