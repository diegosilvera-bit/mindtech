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
$tipo_alerta = '';

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

    if (empty($nome) || empty($cnpj)) {
        $mensagem = "Os campos Nome e CNPJ são obrigatórios.";
        $tipo_alerta = "warning";
    } else {
        // Query de Update mapeada com os campos da tabela 'fornecedores'
        $sql_update = "UPDATE fornecedores SET 
                        nome = '$nome', 
                        cnpj = '$cnpj', 
                        telefone = '$telefone', 
                        email = '$email', 
                        endereco = '$endereco' 
                      WHERE id_fornecedor = $id_fornecedor";

        if (mysqli_query($conn, $sql_update)) {
            $mensagem = "Fornecedor <strong>$nome</strong> atualizado com sucesso!";
            $tipo_alerta = "success";
        } else {
            $mensagem = "Erro ao atualizar fornecedor: " . mysqli_error($conn);
            $tipo_alerta = "danger";
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
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Editar Fornecedor</h1>
        <a href="listar.php" class="btn btn-secondary me-2">Voltar para Lista</a>
    </div>

    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?= $tipo_alerta ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= $mensagem ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Nome da Empresa *</label>
                        <input type="text" class="form-control" name="nome" value="<?= htmlspecialchars($fornecedor['nome']) ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">CNPJ *</label>
                        <input type="text" class="form-control" name="cnpj" value="<?= htmlspecialchars($fornecedor['cnpj'] ?? '') ?>" required maxlength="18" placeholder="00.000.000/0000-00" oninput="mascaraCNPJ(this)">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">E-mail Comercial</label>
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($fornecedor['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Telefone / WhatsApp</label>
                        <input type="text" class="form-control" name="telefone" value="<?= htmlspecialchars($fornecedor['telefone'] ?? '') ?>" maxlength="15" placeholder="(00) 00000-0000" oninput="mascaraTelefone(this)">
                    </div>
                </div>
                <!-- O campo de Endereço foi movido para uma nova linha mantendo o mesmo estilo visual -->
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label">Endereço Completo</label>
                        <input type="text" class="form-control" name="endereco" value="<?= htmlspecialchars($fornecedor['endereco'] ?? '') ?>" placeholder="Rua, Número, Bairro, Cidade - UF">
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-success" type="submit">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
<script>
    // Máscara para CNPJ: 00.000.000/0000-00
    function mascaraCNPJ(input) {
        let v = input.value.replace(/\D/g, ""); // Remove tudo o que não é dígito
        v = v.replace(/^(\d{2})(\d)/, "$1.$2");
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
        v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
        v = v.replace(/(\d{4})(\d)/, "$1-$2");
        input.value = v.substring(0, 18); // Limita o tamanho
    }

    // Máscara para Telefone: (00) 0000-0000 ou (00) 00000-0000
    function mascaraTelefone(input) {
        let v = input.value.replace(/\D/g, ""); // Remove tudo o que não é dígito
        v = v.replace(/^(\d{2})(\d)/g, "($1) $2"); // Coloca parênteses
        v = v.replace(/(\d)(\d{4})$/, "$1-$2"); // Coloca o hífen
        input.value = v.substring(0, 15); // Limita o tamanho
    }
</script>

<?php include '../includes/footer.php'; ?>