<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php';
require_once '../includes/auth.php';

// Inclui a conexão com o banco de dados
include '../config/conexao.php';

// Garante que a sessão está ativa e pega o perfil do utilizador logado
$perfil_logado = $_SESSION['usuario']['perfil'] ?? 'A';

// Busca todos os usuários cadastrados em ordem alfabética
$sql = "SELECT * FROM usuarios ORDER BY nome ASC";
$result = mysqli_query($conn, $sql);

include '../includes/header.php';
?>

<div class="container mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">
                <i class="bi bi-people-fill text-dark me-2"></i>Usuários do Sistema
            </h1>
        </div>
        <div>
            <a href="../dashboard/index.php">
                <button class="btn btn-dark">Dashboard</button>
            </a>

            <?php if ($perfil_logado === 'G'): ?>
                <a href="cadastrar.php">
                    <button class="btn btn-success"> + Novo Usuário</button>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['erro']) && $_GET['erro'] == 'auto_exclusao'): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-octagon-fill me-2"></i>
            <strong>Operação Negada!</strong> Você não pode excluir a sua própria conta de usuário logada.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 border-start border-4 border-dark">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Código</th>
                            <th>Nome Completo</th>
                            <th>Nome de Login</th>
                            <th>Perfil / Nível</th>
                            <th class="text-center pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($usuario = mysqli_fetch_assoc($result)) {

                                // Mapeamento legível de perfis com Badges personalizados
                                $nome_perfil = 'Desconhecido';
                                $cor_badge = 'bg-secondary';

                                switch ($usuario['perfil']) {
                                    case 'G':
                                        $nome_perfil = 'Gerente';
                                        $cor_badge = 'bg-dark';
                                        break;
                                    case 'T':
                                        $nome_perfil = 'Técnico';
                                        $cor_badge = 'bg-warning text-dark';
                                        break;
                                    case 'E':
                                        $nome_perfil = 'Estoquista';
                                        $cor_badge = 'bg-info text-dark';
                                        break;
                                    case 'A':
                                        $nome_perfil = 'Atendimento';
                                        $cor_badge = 'bg-primary';
                                        break;
                                }
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">#<?php echo $usuario['id_usuario']; ?></td>
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                    <td><code><?php echo htmlspecialchars($usuario['login']); ?></code></td>
                                    <td><span
                                            class="badge <?php echo $cor_badge; ?> px-2 py-1"><?php echo $nome_perfil; ?></span>
                                    </td>

                                    <td class="text-center pe-4">
                                        <?php if ($perfil_logado === 'G'): ?>
                                            <div class="d-flex justify-content-center gap-2">

                                                <a href="editar.php?id=<?php echo $usuario['id_usuario']; ?>"
                                                    class="btn btn-sm btn-primary" title="Editar Usuário">
                                                    <i class="bi bi-pencil-square"></i> Editar
                                                </a>

                                                <a href="deletar.php?id=<?php echo $usuario['id_usuario']; ?>"
                                                    class="btn btn-sm btn-danger" title="Excluir Usuário"
                                                    onclick="return confirm('Aviso: Tem certeza absoluta que deseja excluir o funcionário <?php echo htmlspecialchars($usuario['nome']); ?> do sistema?');">
                                                    <i class="bi bi-trash3-fill"></i> Excluir
                                                </a>

                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small"><i class="bi bi-lock-fill me-1"></i>Apenas Leitura</span>
                                        <?php endif; ?>
                                    </td>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Nenhum usuário cadastrado no sistema.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>