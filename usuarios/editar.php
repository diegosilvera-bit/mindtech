<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

$mensagem = ''; 

// Pega o ID do usuário que veio pela URL (ex: editar.php?id=2)
$id_usuario = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verifica se o formulário foi enviado para ATUALIZAR
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Pega os novos dados digitados e protege contra aspas
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $login = mysqli_real_escape_string($conn, $_POST['login']);
    $senha = mysqli_real_escape_string($conn, $_POST['senha']);
    $perfil = mysqli_real_escape_string($conn, $_POST['perfil']);

    // Validação básica
    if ($nome == '' || $login == '' || $senha == '' || $perfil == '') {
        $mensagem = "Por favor, preencha todos os campos.";
    } else {
        // Monta o comando de atualização na tabela de usuários
        $sql_update = "UPDATE usuarios SET 
                        nome = '$nome', 
                        login = '$login', 
                        senha = '$senha', 
                        perfil = '$perfil' 
                       WHERE id_usuario = $id_usuario";
        
        if (mysqli_query($conn, $sql_update)) {
            $mensagem = "Usuário atualizado com sucesso!";
        } else {
            $mensagem = "Erro ao atualizar o usuário: " . mysqli_error($conn);
        }
    }
}

// Busca os dados ATUAIS do usuário para preencher a tela
$sql_busca = "SELECT * FROM usuarios WHERE id_usuario = $id_usuario";
$result = mysqli_query($conn, $sql_busca);
$usuario = mysqli_fetch_assoc($result);

// Se não encontrar o usuário no banco (ID digitado errado na URL), volta para a lista
if (!$usuario) {
    header("Location: listar.php");
    exit;
}

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Editar Usuário: <?php echo $usuario['login']; ?></h1>
        <a href="listar.php" class="btn btn-secondary me-2">Voltar para a Lista</a>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert alert-info shadow-sm">
            <?php echo $mensagem; ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0 border-start border-4 border-dark">
        <div class="card-body p-4">
            
            <form method="post" action="">
                
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-bold">Nome Completo *</label>
                        <input type="text" class="form-control" name="nome" value="<?php echo $usuario['nome']; ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Perfil de Acesso *</label>
                        <select class="form-select" name="perfil" required>
                            <option value="A" <?php echo $usuario['perfil'] == 'A' ? 'selected' : ''; ?>>Atendimento (Recepção)</option>
                            <option value="T" <?php echo $usuario['perfil'] == 'T' ? 'selected' : ''; ?>>Técnico</option>
                            <option value="E" <?php echo $usuario['perfil'] == 'E' ? 'selected' : ''; ?>>Estoquista</option>
                            <option value="G" <?php echo $usuario['perfil'] == 'G' ? 'selected' : ''; ?>>Gerente (Acesso Total)</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nome de Login *</label>
                        <input type="text" class="form-control" name="login" value="<?php echo $usuario['login']; ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Senha de Acesso *</label>
                        <input type="text" class="form-control" name="senha" value="<?php echo $usuario['senha']; ?>" required>
                    </div>
                </div>

                <hr class="mt-4">
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-dark" type="submit">Salvar Alterações</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>