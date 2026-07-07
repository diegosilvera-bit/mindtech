<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// SEGURANÇA MÁXIMA: Bloqueia acesso direto à página caso não seja gerente
verificarAcesso(['G']);

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

$mensagem = ''; 
$tipo_alerta = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Captura e higieniza inputs
    $nome = mysqli_real_escape_string($conn, trim($_POST['nome']));
    $login = mysqli_real_escape_string($conn, trim($_POST['login']));
    $senha = mysqli_real_escape_string($conn, trim($_POST['senha']));
    $perfil = mysqli_real_escape_string($conn, trim($_POST['perfil']));

    if (empty($nome) || empty($login) || empty($senha) || empty($perfil)) {
        $mensagem = "Por favor, preencha todos os campos obrigatórios (*).";
        $tipo_alerta = "warning";
    } else {
        // Insere o novo registro
        $sql = "INSERT INTO usuarios (nome, login, senha, perfil) VALUES ('$nome', '$login', '$senha', '$perfil')";
        
        if (mysqli_query($conn, $sql)) {
            $mensagem = "Usuário cadastrado com sucesso!";
            $tipo_alerta = "success";
        } else {
            $mensagem = "Erro ao cadastrar usuário: " . mysqli_error($conn);
            $tipo_alerta = "danger";
        }
    }
}

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="bi bi-person-plus-fill text-dark me-2"></i>Novo Usuário</h1>
            </div>
            <a href="listar.php" class="btn btn-secondary px-3">
             Voltar à Lista
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
            <form method="POST" action="cadastrar.php">
                
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-bold">Nome Completo *</label>
                        <input type="text" class="form-control" name="nome" placeholder="Ex: Maria das Dores Silva" required style="border-radius: 8px;">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Perfil de Acesso *</label>
                        <select class="form-select" name="perfil" required style="border-radius: 8px;">
                            <option value="" selected disabled>Escolha o cargo...</option>
                            <option value="A">Atendimento (Recepção)</option>
                            <option value="T">Técnico (Laboratório)</option>
                            <option value="E">Estoquista (Peças)</option>
                            <option value="G">Gerente (Acesso Total)</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nome de Login *</label>
                        <input type="text" class="form-control" name="login" placeholder="Ex: maria.silva" required style="border-radius: 8px;">
                        <small class="text-muted">Utilizado para efetuar o login no painel.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Senha de Acesso *</label>
                        <input type="text" class="form-control" name="senha" placeholder="Crie uma senha de acesso estável" required style="border-radius: 8px;">
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-20">
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border fw-bold px-4" style="border-radius: 8px;">Cancelar</a>
                    <button class="btn btn-success fw-bold px-5 shadow-sm" type="submit" style="border-radius: 8px;">
                        <i class="bi bi-save me-2"></i> Salvar Usuário
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>