<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA: mesmo grupo que pode ver a lista de O.S.
verificarAcesso(['G', 'A', 'T']);

include '../config/conexao.php'; 

$mensagem = '';
$tipo_alerta = 'success';

// Gera um código aleatório único no formato XXXX-XXXX
function gerarCodigoUnico($conn) {
    do {
        $codigo = strtoupper(bin2hex(random_bytes(4))); // 8 caracteres
        $codigo = substr($codigo, 0, 4) . '-' . substr($codigo, 4, 4);

        $codigo_escapado = mysqli_real_escape_string($conn, $codigo);
        $check = mysqli_query($conn, "SELECT id_os FROM ordens_servico WHERE codigo_acompanhamento = '$codigo_escapado'");
    } while ($check && mysqli_num_rows($check) > 0);

    return $codigo;
}

// Ação: gerar (ou regenerar) o código de uma O.S. específica
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'gerar') {
    $id_os = (int)$_POST['id_os'];

    if ($id_os > 0) {
        $novo_codigo = gerarCodigoUnico($conn);
        $novo_codigo_escapado = mysqli_real_escape_string($conn, $novo_codigo);

        $sql_update = "UPDATE ordens_servico SET codigo_acompanhamento = '$novo_codigo_escapado' WHERE id_os = $id_os";

        if (mysqli_query($conn, $sql_update)) {
            $mensagem = "Código gerado com sucesso para a O.S. #$id_os: <strong>$novo_codigo</strong>";
            $tipo_alerta = 'success';
        } else {
            $mensagem = "Erro ao gerar o código: " . mysqli_error($conn);
            $tipo_alerta = 'danger';
        }
    }
}

// Busca todas as O.S. (exceto canceladas, que normalmente não precisam de acompanhamento)
$sql = "SELECT os.id_os, os.status, os.codigo_acompanhamento,
               c.nome AS nome_cliente,
               CONCAT(e.marca, ' ', e.modelo) AS equipamento
        FROM ordens_servico os
        JOIN clientes c ON os.id_cliente = c.id_cliente
        JOIN equipamentos e ON os.id_equipamento = e.id_equipamento
        ORDER BY os.id_os DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Erro fatal no banco de dados: " . mysqli_error($conn));
}

// Monta a URL pública de consulta (ajuste o domínio/caminho se necessário)
$url_consulta = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/consultar.php';

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="bi bi-qr-code text-info me-2"></i>Código de Acompanhamento</h1>
            <p class="text-muted small mb-0">Gere um código único para o cliente acompanhar o andamento do reparo sem precisar de login.</p>
        </div>
        <a href="listar.php" class="btn btn-sm btn-outline-secondary fw-bold px-3">
            <i class="bi bi-arrow-left me-1"></i> Voltar à Lista
        </a>
    </div>

    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php echo $mensagem; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="alert alert-light border shadow-sm small">
        <i class="bi bi-info-circle me-1"></i>
        O cliente poderá consultar o status do aparelho em:
        <a href="consultar.php" target="_blank"><?php echo htmlspecialchars($url_consulta); ?></a>
        informando o código gerado abaixo.
    </div>

    <div class="card shadow-sm border-0 border-start border-4 border-info">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">N° OS</th>
                            <th>Cliente</th>
                            <th>Equipamento</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Código de Acompanhamento</th>
                            <th class="text-center pe-3">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-muted">#<?php echo $row['id_os']; ?></td>
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['nome_cliente']); ?></td>
                                    <td><?php echo htmlspecialchars($row['equipamento']); ?></td>
                                    <td class="text-center"><span class="badge bg-secondary"><?php echo $row['status']; ?></span></td>
                                    <td class="text-center">
                                        <?php if (!empty($row['codigo_acompanhamento'])): ?>
                                            <code class="fs-6" id="codigo-<?php echo $row['id_os']; ?>"><?php echo htmlspecialchars($row['codigo_acompanhamento']); ?></code>
                                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2"
                                                    onclick="copiarCodigo('<?php echo htmlspecialchars($row['codigo_acompanhamento']); ?>')"
                                                    title="Copiar código">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small">Nenhum código gerado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center pe-3">
                                        <form method="POST" action="gerar_codigo.php" class="d-inline"
                                              onsubmit="return <?php echo !empty($row['codigo_acompanhamento']) ? "confirm('Isso vai gerar um NOVO código e o anterior deixará de funcionar. Continuar?')" : 'true'; ?>;">
                                            <input type="hidden" name="acao" value="gerar">
                                            <input type="hidden" name="id_os" value="<?php echo $row['id_os']; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo !empty($row['codigo_acompanhamento']) ? 'btn-outline-info' : 'btn-info'; ?> fw-bold">
                                                <i class="bi bi-arrow-repeat me-1"></i>
                                                <?php echo !empty($row['codigo_acompanhamento']) ? 'Gerar Novo' : 'Gerar Código'; ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Nenhuma Ordem de Serviço encontrada.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function copiarCodigo(codigo) {
    navigator.clipboard.writeText(codigo).then(() => {
        alert('Código copiado: ' + codigo);
    });
}
</script>

<?php include '../includes/footer.php'; ?>