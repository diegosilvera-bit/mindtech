<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
    
    // Captura os dados do formulário
    $nome = mysqli_real_escape_string($conn, trim($_POST['nome']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $login = mysqli_real_escape_string($conn, trim($_POST['login']));
    $senha = mysqli_real_escape_string($conn, trim($_POST['senha']));
    $perfil = mysqli_real_escape_string($conn, trim($_POST['perfil']));

    if (empty($nome) || empty($email) || empty($login) || empty($senha) || empty($perfil)) {
        $mensagem = "Por favor, preencha todos os campos obrigatórios.";
        $tipo_alerta = "warning";
    } else {
        $caminho_foto = null;

        // Processamento do upload da foto (se enviada)
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png'];

            if (in_array($extensao, $extensoes_permitidas)) {
                $diretorio_destinatario = '../uploads/';
                if (!is_dir($diretorio_destinatario)) {
                    mkdir($diretorio_destinatario, 0755, true);
                }

                $novo_nome_foto = 'usuario_' . time() . '_' . uniqid() . '.' . $extensao;
                $destino_final = $diretorio_destinatario . $novo_nome_foto;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino_final)) {
                    $caminho_foto = $novo_nome_foto;
                }
            } else {
                $mensagem = "Formato de imagem inválido! Apenas JPG, JPEG ou PNG são permitidos.";
                $tipo_alerta = "danger";
            }
        }

        if (empty($mensagem)) {
            if ($caminho_foto) {
                $caminho_foto_esc = mysqli_real_escape_string($conn, $caminho_foto);
                $sql_update = "UPDATE usuarios SET nome='$nome', email='$email', login='$login', senha='$senha', perfil='$perfil', foto='$caminho_foto_esc' WHERE id_usuario=$id_usuario";
            } else {
                $sql_update = "UPDATE usuarios SET nome='$nome', email='$email', login='$login', senha='$senha', perfil='$perfil' WHERE id_usuario=$id_usuario";
            }
            
            if (mysqli_query($conn, $sql_update)) {
                $mensagem = "Informações do usuário atualizadas com sucesso!";
                $tipo_alerta = "success";

                // Se o usuário editado é o mesmo que está logado, atualiza a sessão
                // para refletir a mudança (foto, nome, etc.) sem precisar deslogar.
                $id_logado = $_SESSION['usuario']['id'] ?? $_SESSION['usuario']['id_usuario'] ?? 0;
                if ($id_usuario == $id_logado) {
                    $_SESSION['usuario']['nome']   = $nome;
                    $_SESSION['usuario']['email']  = $email;
                    $_SESSION['usuario']['login']  = $login;
                    $_SESSION['usuario']['perfil'] = $perfil;
                    if ($caminho_foto) {
                        $_SESSION['usuario']['foto'] = $caminho_foto;
                    }
                }
            } else {
                $mensagem = "Erro ao atualizar dados: " . mysqli_error($conn);
                $tipo_alerta = "danger";
            }
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
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="bi bi-pencil-square text-white me-2"></i>Editar Funcionário</h1>
            <p class="text-white small mb-0">Atualize os dados cadastrais ou mude as permissões da conta.</p>
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
            <form method="POST" action="editar.php?id=<?php echo $id_usuario; ?>" enctype="multipart/form-data">
                
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-bold">Nome Completo *</label>
                        <input type="text" class="form-control" name="nome" value="<?php echo htmlspecialchars($usuario['nome'] ?? ''); ?>" required style="border-radius: 8px;">
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
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">E-mail *</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" required style="border-radius: 8px;">
                        <small class="text-muted">Utilizado para recuperação de senha.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nome de Login *</label>
                        <input type="text" class="form-control" name="login" value="<?php echo htmlspecialchars($usuario['login'] ?? ''); ?>" required style="border-radius: 8px;">
                        <small class="text-muted">Utilizado para efetuar o login no painel.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Senha de Acesso *</label>
                        <input type="text" class="form-control" name="senha" value="<?php echo htmlspecialchars($usuario['senha'] ?? ''); ?>" required style="border-radius: 8px;">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Foto de Perfil</label>
                        <input type="file" class="form-control" name="foto" accept="image/png, image/jpeg, image/jpg" style="border-radius: 8px;">
                        <small class="text-muted">Formatos aceitos: JPG, JPEG ou PNG.</small>
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-20">
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-danger border fw-bold px-4" style="border-radius: 8px;">Cancelar</a>
                    <button class="btn btn-success fw-bold px-5 shadow-sm" type="submit" style="border-radius: 8px;">
                        <i class="bi bi-save me-2"></i> Salvar Alterações
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>