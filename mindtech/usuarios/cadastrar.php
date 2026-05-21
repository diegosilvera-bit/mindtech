<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

$mensagem = ''; 

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Pega os dados do formulário de forma simples
    $nome = $_POST['nome'];
    $login = $_POST['login'];
    $senha = $_POST['senha'];
    $perfil = $_POST['perfil'];

    // Validação: Todos os campos são obrigatórios para criar um usuário
    if ($nome == '' || $login == '' || $senha == '' || $perfil == '') {
        $mensagem = "Por favor, preencha todos os campos.";
    } else {
        
        // Proteção contra aspas para evitar erros de SQL
        $nome = mysqli_real_escape_string($conn, $nome);
        $login = mysqli_real_escape_string($conn, $login);
        
        // Mantemos a senha simples conforme o seu usuário "admin" já existente no banco
        $senha = mysqli_real_escape_string($conn, $senha); 

        // Monta o comando SQL para inserir o usuário
        $sql = "INSERT INTO usuarios (nome, login, senha, perfil) 
                VALUES ('$nome', '$login', '$senha', '$perfil')";
        
        // Executa o comando e verifica se deu certo
        if (mysqli_query($conn, $sql)) {
            $mensagem = "Usuário cadastrado com sucesso!";
        } else {
            // Se tentar cadastrar um login que já existe, o banco vai dar erro (UNIQUE)
            $mensagem = "Erro ao cadastrar: " . mysqli_error($conn);
        }
    }
}

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">   <!-- Bootstrap: container centralizado + margin top e bottom -->
    <div class="d-flex justify-content-between align-items-center mb-4">   <!-- Bootstrap: flexbox - alinha título e botão nas extremidades -->
        <h1>Cadastrar Novo Usuário</h1>
        <a href="listar.php" class="btn btn-outline-secondary">Voltar para a Lista</a>   <!-- Bootstrap: botão com estilo outline (borda) -->
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert alert-info shadow-sm">   <!-- Bootstrap: alerta azul com sombra suave -->
            <?php echo $mensagem; ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0 border-start border-4 border-dark">   <!-- Bootstrap: card com sombra, sem borda completa e borda esquerda grossa escura -->
        <div class="card-body p-4">   <!-- Bootstrap: corpo do card com padding interno -->
            <p class="text-muted mb-4">Crie uma conta de acesso para um funcionário e defina o seu nível de permissão.</p>
            
            <form method="post" action="">
                
                <div class="row">   <!-- Bootstrap: row (linha) do grid system -->
                    <div class="col-md-8 mb-3">   <!-- Bootstrap: coluna responsiva (8/12 em md) + margin bottom -->
                        <label class="form-label fw-bold">Nome Completo do Funcionário *</label>
                        <input type="text" class="form-control" name="nome" placeholder="Ex: Maria Oliveira" required>   <!-- Bootstrap: input estilizado -->
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Perfil de Acesso *</label>
                        <select class="form-select" name="perfil" required>   <!-- Bootstrap: select estilizado -->
                            <option value="">Selecione...</option>
                            <option value="A">Atendimento (Recepção)</option>
                            <option value="T">Técnico</option>
                            <option value="E">Estoquista</option>
                            <option value="G">Gerente (Acesso Total)</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nome de Login *</label>
                        <input type="text" class="form-control" name="login" placeholder="Ex: maria.oliveira" required>
                        <small class="text-muted">Nome usado para entrar no sistema.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Senha de Acesso *</label>
                        <input type="text" class="form-control" name="senha" placeholder="Digite uma senha" required>
                    </div>
                </div>

                <hr class="mt-4">   <!-- Bootstrap: linha horizontal com margin top -->
                <div class="d-flex justify-content-end gap-2">   <!-- Bootstrap: flexbox alinhado à direita + espaçamento entre botões -->
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>   <!-- Bootstrap: botão claro com borda -->
                    <button class="btn btn-dark" type="submit">Salvar Usuário</button>   <!-- Bootstrap: botão escuro -->
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>