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

// Filtro de pesquisa por nome do usuário
$busca = trim($_GET['busca'] ?? '');
$whereBusca = '';
if ($busca !== '') {
    $buscaEsc = mysqli_real_escape_string($conn, $busca);
    $whereBusca = "WHERE nome LIKE '%$buscaEsc%'";
}

// Busca todos os usuários cadastrados em ordem alfabética
$sql = "SELECT * FROM usuarios $whereBusca ORDER BY nome ASC";
$result = mysqli_query($conn, $sql);

include '../includes/header.php';
?>

<style>
    .topo-pagina {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .topo-pagina__acoes {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .campo-busca-wrap .input-group {
        min-width: 220px;
    }

    @media (max-width: 768px) {
        .topo-pagina {
            flex-direction: column;
            align-items: stretch;
        }
        .campo-busca-wrap {
            order: -1;
            width: 100%;
        }
        .campo-busca-wrap .input-group,
        .campo-busca-wrap form {
            width: 100%;
            min-width: 0;
        }
        .topo-pagina__acoes {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            width: 100%;
        }
        .topo-pagina__acoes .btn {
            width: 100%;
        }
    }

    @media (max-width: 768px) {
        #tabelaUsuarios thead { display: none; }
        #tabelaUsuarios, #tabelaUsuarios tbody, #tabelaUsuarios tr, #tabelaUsuarios td { display: block; width: 100%; }
        #tabelaUsuarios tr { margin-bottom: 0.85rem; border: 1px solid #dee2e6; border-radius: 0.5rem; padding: 0.75rem 1rem; }
        #tabelaUsuarios td { border: none; padding: 0.3rem 0; text-align: left !important; }
        #tabelaUsuarios td::before {
            content: attr(data-label);
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 0.15rem;
        }
        #tabelaUsuarios td[data-label="Ações"] .d-flex { flex-wrap: wrap; justify-content: flex-start !important; }
    }
</style>

<div class="container mt-4 mb-5">

    <div class="topo-pagina">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="bi bi-people-fill text-white me-2"></i>Usuários do Sistema
            </h1>
        </div>
        <div class="topo-pagina__acoes">
            <div class="campo-busca-wrap">

                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="busca" id="campoBusca" class="form-control" placeholder="Pesquisar por usuário..." value="<?php echo htmlspecialchars($busca); ?>" autocomplete="off">
                        <?php if ($busca !== ''): ?>
                            <a href="listar.php" class="btn btn-outline-secondary" title="Limpar"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </div>

            </div>
            <a href="../dashboard/index.php" class="btn btn-dark shadow-sm">
                Dashboard
            </a>

            <?php if ($perfil_logado === 'G'): ?>
                <a href="cadastrar.php" class="btn btn-success shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> Novo Usuário
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
                <table id="tabelaUsuarios" class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Código</th>
                            <th>Nome Completo</th>
                            <th>Nome de Login</th>
                            <th>Perfil / Nível</th>
                            <th class="text-center pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="corpoTabela">
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
                                <tr data-nome="<?php echo htmlspecialchars(mb_strtolower($usuario['nome'])); ?>">
                                    <td class="ps-4 fw-bold text-muted">#<?php echo $usuario['id_usuario']; ?></td>
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                    <td><code><?php echo htmlspecialchars($usuario['login']); ?></code></td>
                                    <td>
                                        <span class="badge <?php echo $cor_badge; ?> px-2 py-1"><?php echo $nome_perfil; ?></span>
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
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted"><?php echo $busca !== '' ? 'Nenhum usuário encontrado para "' . htmlspecialchars($busca) . '".' : 'Nenhum usuário cadastrado no sistema.'; ?></td>
                            </tr>
                        <?php } ?>
                        <tr id="semResultadoBusca" style="display:none;">
                            <td colspan="5" class="text-center py-4 text-muted"><i class="bi bi-search me-1"></i> Nenhum usuário encontrado.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Pesquisa ao vivo: filtra a tabela a cada letra digitada
    (function () {
        const campoBusca = document.getElementById('campoBusca');
        const linhas = document.querySelectorAll('#corpoTabela tr[data-nome]');
        const semResultado = document.getElementById('semResultadoBusca');
        if (!campoBusca) return;

        campoBusca.addEventListener('input', function () {
            const termo = this.value.toLowerCase().trim();
            let encontrados = 0;
            linhas.forEach(function (linha) {
                const bate = linha.dataset.nome.includes(termo);
                linha.style.display = bate ? '' : 'none';
                if (bate) encontrados++;
            });
            if (semResultado) {
                semResultado.style.display = encontrados === 0 ? '' : 'none';
            }
        });
    })();
</script>

<?php include '../includes/footer.php'; ?>