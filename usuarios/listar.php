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

// Busca todos os usuários cadastrados
$sql = "SELECT * FROM usuarios $whereBusca ORDER BY ativo DESC, nome ASC";
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

    .avatar-circle, .avatar-placeholder {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
    }
    .avatar-circle { border: 2px solid #fff; }
    .avatar-placeholder { background: #34495e; color: #fff; }
    .avatar-circle:hover, .avatar-placeholder:hover { transform: scale(1.1); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }

    .foto-modal {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.65); backdrop-filter: blur(4px);
        display: flex; align-items: center; justify-content: center;
        z-index: 9999; opacity: 0; pointer-events: none; transition: opacity 0.25s ease-out;
    }
    .foto-modal.show { opacity: 1; pointer-events: auto; }
    .foto-modal-content {
        background-color: #fff; padding: 10px; border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); position: relative;
        transform: scale(0.7); transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); max-width: 90%;
    }
    .foto-modal.show .foto-modal-content { transform: scale(1); }
    .foto-modal-img, .foto-modal-placeholder {
        width: 250px; height: 250px; border-radius: 12px; object-fit: cover; display: flex;
        align-items: center; justify-content: center; font-weight: bold; font-size: 70px;
    }
    .foto-modal-placeholder { background: #34495e; color: #fff; user-select: none; }
    .foto-modal-close {
        position: absolute; top: -15px; right: -15px; background: #212529; color: #fff;
        border: none; width: 32px; height: 32px; border-radius: 50%; display: flex;
        align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    .foto-modal-close:hover { background: #dc3545; transform: scale(1.1); }

    @media (max-width: 768px) {
        .topo-pagina { flex-direction: column; align-items: stretch; }
        .campo-busca-wrap, .campo-busca-wrap .input-group, .campo-busca-wrap form { width: 100%; min-width: 0; order: -1;}
        .topo-pagina__acoes { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; width: 100%; }
        .topo-pagina__acoes .btn { width: 100%; }
        #tabelaUsuarios thead { display: none; }
        #tabelaUsuarios, #tabelaUsuarios tbody, #tabelaUsuarios tr, #tabelaUsuarios td { display: block; width: 100%; }
        #tabelaUsuarios tr { margin-bottom: 0.85rem; border: 1px solid #dee2e6; border-radius: 0.5rem; padding: 0.75rem 1rem; }
        #tabelaUsuarios td { border: none; padding: 0.3rem 0; text-align: left !important; }
        #tabelaUsuarios td::before {
            content: attr(data-label); display: block; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #6c757d; margin-bottom: 0.15rem;
        }
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
            <a href="../dashboard/index.php" class="btn btn-dark shadow-sm">Dashboard</a>

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
            <strong>Operação Negada!</strong> Você não pode inativar a sua própria conta logada.
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
                            <th>Login / Status</th>
                            <th>Perfil / Nível</th>
                            <th class="text-center pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="corpoTabela">
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($usuario = mysqli_fetch_assoc($result)) {

                                // Fallback caso a coluna ativo ainda não exista na linha
                                $isAtivo = !isset($usuario['ativo']) || $usuario['ativo'] == 1;

                                $nome_perfil = 'Desconhecido';
                                $cor_badge = 'bg-secondary';
                                switch ($usuario['perfil']) {
                                    case 'G': $nome_perfil = 'Gerente'; $cor_badge = 'bg-dark'; break;
                                    case 'T': $nome_perfil = 'Técnico'; $cor_badge = 'bg-warning text-dark'; break;
                                    case 'E': $nome_perfil = 'Estoquista'; $cor_badge = 'bg-info text-dark'; break;
                                    case 'A': $nome_perfil = 'Atendimento'; $cor_badge = 'bg-primary'; break;
                                }

                                $nomes = explode(' ', trim($usuario['nome']));
                                $iniciais = mb_strtoupper(mb_substr($nomes[0], 0, 1));
                                if (count($nomes) > 1) {
                                    $iniciais .= mb_strtoupper(mb_substr(end($nomes), 0, 1));
                                }
                                ?>
                                <tr data-nome="<?php echo htmlspecialchars(mb_strtolower($usuario['nome'])); ?>" class="<?php echo !$isAtivo ? 'opacity-50 bg-light' : ''; ?>">
                                    <td data-label="Código" class="ps-4 fw-bold text-muted">#<?php echo $usuario['id_usuario']; ?></td>
                                    
                                    <td data-label="Nome Completo">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <?php 
                                                $caminho_foto = '../uploads/' . $usuario['foto'];
                                                if (!empty($usuario['foto']) && file_exists($caminho_foto)): 
                                                ?>
                                                    <img src="<?php echo $caminho_foto; ?>" alt="Foto" class="avatar-circle img-preview-trigger" data-type="image" data-src="<?php echo $caminho_foto; ?>">
                                                <?php else: ?>
                                                    <div class="avatar-placeholder img-preview-trigger" data-type="initials" data-initials="<?php echo htmlspecialchars($iniciais); ?>">
                                                        <?php echo htmlspecialchars($iniciais); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark d-block <?php echo !$isAtivo ? 'text-decoration-line-through' : ''; ?>"><?php echo htmlspecialchars($usuario['nome']); ?></span>
                                            </div>
                                        </div>
                                    </td>

                                    <td data-label="Login / Status">
                                        <code><?php echo htmlspecialchars($usuario['login']); ?></code><br>
                                        <?php if ($isAtivo): ?>
                                            <span class="badge bg-success mt-1" style="font-size: 0.7em;">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger mt-1" style="font-size: 0.7em;">Inativo</span>
                                        <?php endif; ?>
                                    </td>

                                    <td data-label="Perfil / Nível">
                                        <span class="badge <?php echo $cor_badge; ?> px-2 py-1"><?php echo $nome_perfil; ?></span>
                                    </td>

                                    <td data-label="Ações" class="text-center pe-4">
                                        <?php if ($perfil_logado === 'G'): ?>
                                            <div class="d-flex justify-content-center gap-2">

                                                <a href="editar.php?id=<?php echo $usuario['id_usuario']; ?>"
                                                   class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" title="Editar Usuário">
                                                    <i class="bi bi-pencil-square"></i> Editar
                                                </a>

                                                <?php if ($isAtivo): ?>
                                                    <a href="status.php?id=<?php echo $usuario['id_usuario']; ?>&acao=inativar"
                                                       class="btn btn-sm btn-warning d-inline-flex align-items-center gap-1" title="Inativar Usuário"
                                                       onclick="return confirm('Deseja inativar o funcionário <?php echo htmlspecialchars($usuario['nome']); ?>? Ele perderá acesso ao sistema.');">
                                                        <i class="bi bi-person-slash"></i> Inativar
                                                    </a>
                                                <?php else: ?>
                                                    <a href="status.php?id=<?php echo $usuario['id_usuario']; ?>&acao=ativar"
                                                       class="btn btn-sm btn-success d-inline-flex align-items-center gap-1" title="Ativar Usuário"
                                                       onclick="return confirm('Deseja reativar o acesso de <?php echo htmlspecialchars($usuario['nome']); ?> ao sistema?');">
                                                        <i class="bi bi-person-check"></i> Ativar
                                                    </a>
                                                <?php endif; ?>

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
                            <tr><td colspan="5" class="text-center py-4 text-muted">Nenhum usuário encontrado.</td></tr>
                        <?php } ?>
                        <tr id="semResultadoBusca" style="display:none;"><td colspan="5" class="text-center py-4 text-muted"><i class="bi bi-search me-1"></i> Nenhum usuário encontrado.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="foto-modal" id="previewFotoModal">
    <div class="foto-modal-content">
        <button class="foto-modal-close" id="fecharPreviewBtn"><i class="bi bi-x-lg"></i></button>
        <div id="modalContentArea"></div>
    </div>
</div>

<script>
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
            if (semResultado) semResultado.style.display = encontrados === 0 ? '' : 'none';
        });
    })();

    (function () {
        const modal = document.getElementById('previewFotoModal');
        const contentArea = document.getElementById('modalContentArea');
        const fecharBtn = document.getElementById('fecharPreviewBtn');
        const triggers = document.querySelectorAll('.img-preview-trigger');

        triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                const type = this.getAttribute('data-type');
                if (type === 'image') {
                    contentArea.innerHTML = `<img src="${this.getAttribute('data-src')}" class="foto-modal-img" alt="Foto">`;
                } else if (type === 'initials') {
                    contentArea.innerHTML = `<div class="foto-modal-placeholder">${this.getAttribute('data-initials')}</div>`;
                }
                modal.classList.add('show');
            });
        });

        fecharBtn.addEventListener('click', () => modal.classList.remove('show'));
        modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('show'); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') modal.classList.remove('show'); });
    })();
</script>

<?php include '../includes/footer.php'; ?>