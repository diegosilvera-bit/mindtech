<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA: Apenas Gerente (G) e Atendimento (A) costumam editar fornecedores
verificarAcesso(['G', 'A']);

// Inclui a conexão com o banco
include '../config/conexao.php'; 

$mensagem = ''; 
$tipo_alerta = 'danger';
$sucesso = false;

// Pega o ID do fornecedor vindo da URL
$id_fornecedor = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_fornecedor <= 0) {
    header("Location: listar.php");
    exit;
}

// Se o formulário foi enviado, processa a atualização no banco de dados
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = mysqli_real_escape_string($conn, trim($_POST['nome']));
    $cnpj = mysqli_real_escape_string($conn, trim($_POST['cnpj']));
    $telefone = mysqli_real_escape_string($conn, trim($_POST['telefone']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $endereco = mysqli_real_escape_string($conn, trim($_POST['endereco']));

    if (empty($nome)) {
        $mensagem = "O nome do fornecedor é obrigatório.";
    } else {
        // Query de Update mapeada com os campos da sua tabela 'fornecedores'
        $sql_update = "UPDATE fornecedores SET 
                        nome = '$nome', 
                        cnpj = '$cnpj', 
                        telefone = '$telefone', 
                        email = '$email', 
                        endereco = '$endereco' 
                      WHERE id_fornecedor = $id_fornecedor";

        if (mysqli_query($conn, $sql_update)) {
            $mensagem = "Fornecedor atualizado com sucesso!";
            $tipo_alerta = "success";
            $sucesso = true;
        } else {
            $mensagem = "Erro ao atualizar fornecedor: " . mysqli_error($conn);
        }
    }
}

// Busca os dados atuais do fornecedor para preencher o formulário
$sql_select = "SELECT * FROM fornecedores WHERE id_fornecedor = $id_fornecedor LIMIT 1";
$result_select = mysqli_query($conn, $sql_select);
$fornecedor = mysqli_fetch_assoc($result_select);

// Se o fornecedor não existir no banco, redireciona de volta
if (!$fornecedor) {
    header("Location: listar.php?erro=nao_encontrado");
    exit;
}

include '../includes/header.php'; 
?>

<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <h1 class="h3 mb-0 text-white">
            <i class="bi bi-pencil-square text-warning me-2"></i>Editar Fornecedor
        </h1>
        <a href="listar.php" class="btn btn-secondary btn-sm align-self-start align-self-sm-center">
            <i class="bi bi-arrow-left me-1"></i> Voltar para Lista
        </a>
    </div>

    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?= $tipo_alerta ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi <?= $sucesso ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"></i>
            <?= $mensagem ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 bg-white text-dark" style="border-radius: 12px;">
        <div class="card-body p-4">
            <p class="text-muted border-bottom pb-2 mb-4">
                <i class="bi bi-info-circle me-1"></i> Altere as informações cadastrais do fornecedor <strong>#<?= $fornecedor['id_fornecedor'] ?></strong>.
            </p>

            <form method="POST" action="">
                <div class="row g-3">
                    
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">Nome do Fornecedor / Razão Social <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-light" name="nome" 
                               value="<?= htmlspecialchars($fornecedor['nome']) ?>" required placeholder="Ex: Importadora de Componentes Ltda">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">CNPJ</label>
                        <input type="text" class="form-control bg-light" name="cnpj" 
                               value="<?= htmlspecialchars($fornecedor['cnpj'] ?? '') ?>" placeholder="00.000.000/0000-00">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">Telefone de Contato</label>
                        <input type="text" class="form-control bg-light" name="telefone" 
                               value="<?= htmlspecialchars($fornecedor['telefone'] ?? '') ?>" placeholder="(00) 00000-0000">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">E-mail Corporativo</label>
                        <input type="email" class="form-control bg-light" name="email" 
                               value="<?= htmlspecialchars($fornecedor['email'] ?? '') ?>" placeholder="contato@fornecedor.com">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Endereço Completo</label>
                        <input type="text" class="form-control bg-light" name="endereco" 
                               value="<?= htmlspecialchars($fornecedor['endereco'] ?? '') ?>" placeholder="Rua, Número, Bairro, Cidade - UF">
                    </div>

                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2 flex-wrap">
                    <a href="listar.php" class="btn btn-light border px-4 flex-grow-1 flex-md-grow-0">Cancelar</a>
                    <button class="btn btn-primary fw-bold px-4 flex-grow-1 flex-md-grow-0" type="submit" style="border-radius: 8px;">
                        <i class="bi bi-save me-2"></i>Salvar Alterações
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>