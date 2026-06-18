<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA: Gerente (G), Atendimento (A) e Técnico (T) podem editar O.S.
verificarAcesso(['G', 'A', 'T']);

include '../config/conexao.php'; 

$mensagem = ''; 
$sucesso = false;

// Pega o ID da OS vindo da URL
$id_os = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_os <= 0) {
    header("Location: listar.php");
    exit;
}

// Se o formulário foi enviado, processa a atualização
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cliente = (int)$_POST['id_cliente'];
    $id_equipamento = (int)$_POST['id_equipamento'];
    $id_tecnico = !empty($_POST['id_usuario_responsavel']) ? (int)$_POST['id_usuario_responsavel'] : 'NULL';
    $status = $_POST['status'];
    $observacoes = mysqli_real_escape_string($conn, $_POST['observacoes']);

    if ($id_cliente <= 0 || $id_equipamento <= 0) {
        $mensagem = "Os campos Cliente e Equipamento são obrigatórios.";
    } else {
        // Monta o SQL de atualização
        $sql_update = "UPDATE ordens_servico SET 
                        id_cliente = $id_cliente, 
                        id_equipamento = $id_equipamento, 
                        id_usuario_responsavel = $id_tecnico, 
                        status = '$status', 
                        observacoes = '$observacoes' 
                      WHERE id_os = $id_os";

        if (mysqli_query($conn, $sql_update)) {
            $mensagem = "Ordem de Serviço atualizada com sucesso!";
            $sucesso = true;
        } else {
            $mensagem = "Erro ao atualizar a O.S.: " . mysqli_error($conn);
        }
    }
}

// Busca os dados atuais da OS
$sql_os = "SELECT * FROM ordens_servico WHERE id_os = $id_os";
$res_os = mysqli_query($conn, $sql_os);
$os = mysqli_fetch_assoc($res_os);

if (!$os) {
    header("Location: listar.php");
    exit;
}

// Busca dados auxiliares para preencher os selects do formulário
$clientes = mysqli_query($conn, "SELECT id_cliente, nome FROM clientes WHERE ativo = 1 OR id_cliente = {$os['id_cliente']} ORDER BY nome ASC");
$equipamentos = mysqli_query($conn, "SELECT id_equipamento, marca, modelo FROM equipamentos WHERE ativo = 1 OR id_equipamento = {$os['id_equipamento']} ORDER BY marca ASC");
$tecnicos = mysqli_query($conn, "SELECT id_usuario, nome FROM usuarios WHERE perfil IN ('T', 'G') ORDER BY nome ASC");

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Editar Ordem de Serviço #<?php echo $id_os; ?></h1>
        <a href="listar.php" class="btn btn-secondary">Voltar para Lista</a>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert <?php echo $sucesso ? 'alert-success' : 'alert-danger'; ?> shadow-sm fw-bold">
            <?php echo $mensagem; ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0 border-start border-4 border-primary">
        <div class="card-body p-4">
            <form method="post" action="editar.php?id=<?php echo $id_os; ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Cliente *</label>
                        <select class="form-select" name="id_cliente" required>
                            <option value="">-- Selecione o Cliente --</option>
                            <?php while($c = mysqli_fetch_assoc($clientes)) { ?>
                                <option value="<?php echo $c['id_cliente']; ?>" <?php echo $c['id_cliente'] == $os['id_cliente'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['nome']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Equipamento *</label>
                        <select class="form-select" name="id_equipamento" required>
                            <option value="">-- Selecione o Equipamento --</option>
                            <?php while($e = mysqli_fetch_assoc($equipamentos)) { ?>
                                <option value="<?php echo $e['id_equipamento']; ?>" <?php echo $e['id_equipamento'] == $os['id_equipamento'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($e['marca'] . ' ' . $e['modelo']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Técnico Responsável</label>
                        <select class="form-select" name="id_usuario_responsavel">
                            <option value="">-- Sem técnico alocado --</option>
                            <?php while($t = mysqli_fetch_assoc($tecnicos)) { ?>
                                <option value="<?php echo $t['id_usuario']; ?>" <?php echo $t['id_usuario'] == $os['id_usuario_responsavel'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t['nome']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Etapa / Status Atual</label>
                        <select class="form-select" name="status">
                            <option value="EM_ANALISE" <?php echo $os['status'] == 'EM_ANALISE' ? 'selected' : ''; ?>>Em Análise</option>
                            <option value="EM_REPARO" <?php echo $os['status'] == 'EM_REPARO' ? 'selected' : ''; ?>>Em Reparo</option>
                            <option value="AGUARDANDO_PECA" <?php echo $os['status'] == 'AGUARDANDO_PECA' ? 'selected' : ''; ?>>Aguardando Peça</option>
                            <option value="FINALIZADO" <?php echo $os['status'] == 'FINALIZADO' ? 'selected' : ''; ?>>Finalizado</option>
                            <option value="CANCELADO" <?php echo $os['status'] == 'CANCELADO' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Problema Relatado / Observações Técnicas</label>
                    <textarea class="form-control" name="observacoes" rows="5"><?php echo htmlspecialchars($os['observacoes']); ?></textarea>
                </div>

                <hr class="mt-4">
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-primary fw-bold" type="submit">Salvar Alterações</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>