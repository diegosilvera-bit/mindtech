<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

// Busca todos os usuários cadastrados em ordem alfabética
$sql = "SELECT * FROM usuarios ORDER BY nome ASC";
$result = mysqli_query($conn, $sql);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">   <!-- Bootstrap: container centralizado com margem superior e inferior -->
    <div class="d-flex justify-content-between align-items-center mb-4">   <!-- Bootstrap: flexbox - alinha título e botões nas extremidades -->
        <h1>Usuários do Sistema</h1>
        <div>
            <a href="../dashboard/index.php" class="btn btn-secondary me-2">Voltar</a>   <!-- Bootstrap: botão cinza com margem à direita -->
            <a href="cadastrar.php" class="btn btn-dark">+ Novo Usuário</a>   <!-- Bootstrap: botão escuro -->
        </div>
    </div>

    <div class="card shadow-sm border-0 border-start border-4 border-dark">   <!-- Bootstrap: card com sombra, sem borda total e borda esquerda grossa escura -->
        <div class="card-body p-0">   <!-- Bootstrap: corpo do card sem padding lateral/superior -->
            <table class="table table-hover table-striped align-middle mb-0">   <!-- Bootstrap: tabela com hover, listras alternadas e alinhamento vertical -->
                <thead class="table-dark">   <!-- Bootstrap: cabeçalho da tabela com fundo escuro -->
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Nome do Funcionário</th>
                        <th>Login</th>
                        <th>Perfil de Acesso</th>
                        <th class="text-center pe-3">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($usuario = mysqli_fetch_assoc($result)) { 
                            
                            // Traduz a letra do banco de dados para o nome completo e escolhe uma cor
                            $nome_perfil = '';
                            $cor_badge = '';
                            
                            if ($usuario['perfil'] == 'G') {
                                $nome_perfil = 'Gerente';
                                $cor_badge = 'bg-dark'; // Preto
                            } elseif ($usuario['perfil'] == 'T') {
                                $nome_perfil = 'Técnico';
                                $cor_badge = 'bg-primary'; // Azul
                            } elseif ($usuario['perfil'] == 'A') {
                                $nome_perfil = 'Atendimento';
                                $cor_badge = 'bg-info text-dark'; // Ciano
                            } elseif ($usuario['perfil'] == 'E') {
                                $nome_perfil = 'Estoquista';
                                $cor_badge = 'bg-warning text-dark'; // Amarelo
                            }
                    ?>
                            <tr>
                                <td class="ps-3 text-muted">#<?php echo $usuario['id_usuario']; ?></td>
                                
                                <td class="fw-bold"><?php echo $usuario['nome']; ?></td>
                                
                                <td><?php echo $usuario['login']; ?></td>
                                
                                <td>
                                    <!-- Bootstrap: badge com cor dinâmica conforme o perfil -->
                                    <span class="badge <?php echo $cor_badge; ?>">
                                        <?php echo $nome_perfil; ?>
                                    </span>
                                </td>
                                
                                <td class="text-center pe-3">
                                    <a href="editar.php?id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-sm btn-outline-dark">Editar</a>   <!-- Bootstrap: botão pequeno com borda escura -->
                                </td>
                            </tr>
                    <?php 
                        } 
                    } else { 
                    ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">   <!-- Bootstrap: centralizado com padding vertical -->
                                Nenhum usuário cadastrado no sistema.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>