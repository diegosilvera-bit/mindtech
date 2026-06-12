<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// SEGURANÇA MÁXIMA: Bloqueia acesso direto caso não seja gerente
verificarAcesso(['G']);

include '../config/conexao.php'; 

$mensagem = ''; 
$tipo_alerta = '';

$id_usuario = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_usuario <= 0) {
    header("Location: listar.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $nome = mysqli_real_escape_string($conn, trim($_POST['nome']));
    $login = mysqli_real_escape_string($conn, trim($_POST['login']));
    $senha = mysqli_real_escape_string($conn, trim($_POST['senha']));
    $perfil = mysqli_real_escape_string($conn, trim($_POST['perfil']));

    if (empty($nome) || empty($login) || empty($senha) || empty($perfil)) {
        $mensagem = "Por favor, preencha todos os campos obrigatórios.";
        $tipo_alerta = "warning";
    } else {
        $sql_update = "UPDATE usuarios SET nome='$nome', login='$login', senha='$senha', perfil='$perfil' WHERE id_usuario=$id_usuario";
        
        if (mysqli_query($conn, $sql_update)) {
            $mensagem = "Informações do usuário atualizadas com sucesso!";
            $tipo_alerta = "success";
        } else {
            $mensagem = "Erro ao atualizar dados: " . mysqli_error($conn);
            $tipo_alerta = "danger";
        }
    }
}

// Carrega os dados atuais para o formulário
$sql_busca = "SELECT * FROM usuarios WHERE id_usuario = $id_usuario";
$result_busca = mysqli_query($conn, $sql_busca);
$usuario = mysqli_fetch_assoc($result_busca);

if (!$usuario) {
    header("Location: listar.php");
    exit;
}

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="bi bi-pencil-square text-dark me-2"></i>Editar Funcionário</h1>
            <p class="text-muted small mb-0">Atualize os dados cadastrais ou mude as permissões da conta.</p>
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

    <div class="card shadow-sm border-0 border-start border-4 border-dark">
        <div class="card-body p-4">
            <form method="POST" action="editar.php?id=<?php echo $id_usuario; ?>">
                
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-bold">Nome Completo *</label>
                        <input type="text" class="form-control" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required style="border-radius: 8px;">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Perfil de Acesso *</label>
                        <select class="form-select" name="perfil" required style="border-radius: 8px;">
                            <option value="A" <?php echo $usuario['perfil'] == 'A' ? 'selected' : ''; ?>>Atendimento (Recepção)</option>
                            <option value="T" <?php echo $usuario['perfil'] == 'T' ? 'selected' : ''; ?>>Técnico (Laboratório)</option>
                            <option value="E" <?php echo $usuario['perfil'] == 'E' ? 'selected' : ''; ?>>Estoquista (Peças)</option>
                            <option value="G" <?php echo $usuario['perfil'] == 'G' ? 'selected' : ''; ?>>Gerente (Acesso Total)</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nome de Login *</label>
                        <input type="text" class="form-control" name="login" value="<?php echo htmlspecialchars($usuario['login']); ?>" required style="border-radius: 8px;">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Senha de Acesso *</label>
                        <input type="text" class="form-control" name="senha" value="<?php echo htmlspecialchars($usuario['senha']); ?>" required style="border-radius: 8px;">
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-20">
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border fw-bold px-4" style="border-radius: 8px;">Cancelar</a>
                    <button class="btn btn-dark fw-bold px-5 shadow-sm" type="submit" style="border-radius: 8px;">
                        <i class="bi bi-save me-2"></i> Salvar Alterações
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>