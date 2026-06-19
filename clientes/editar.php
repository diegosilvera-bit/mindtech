<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
// Liga a exibição de erros para segurança no desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA: Apenas Gerente (G) e Atendimento (A) costumam editar clientes
verificarAcesso(['G', 'A']);

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

$mensagem = ''; 
$sucesso = false;

// Pega o ID do cliente vindo da URL
$id_cliente = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_cliente <= 0) {
    header("Location: listar.php");
    exit;
}

// Se o formulário foi enviado, processa a atualização
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $rg = $_POST['rg'];
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];
    $data_nascimento = !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : null;

    if (empty($nome) || empty($cpf)) {
        $mensagem = "Os campos Nome e CPF são obrigatórios.";
    } else {
        // Proteção contra SQL Injection
        $nome = mysqli_real_escape_string($conn, $nome);
        $cpf = mysqli_real_escape_string($conn, $cpf);
        $rg = mysqli_real_escape_string($conn, $rg);
        $telefone = mysqli_real_escape_string($conn, $telefone);
        $endereco = mysqli_real_escape_string($conn, $endereco);

        if ($data_nascimento) {
            $data_nascimento = "'" . mysqli_real_escape_string($conn, $data_nascimento) . "'";
        } else {
            $data_nascimento = "NULL";
        }

        // Monta o SQL de Update
        $sql_update = "UPDATE clientes SET 
                        nome = '$nome', 
                        cpf = '$cpf', 
                        rg = '$rg', 
                        telefone = '$telefone', 
                        endereco = '$endereco', 
                        data_nascimento = $data_nascimento 
                      WHERE id_cliente = $id_cliente";

        if (mysqli_query($conn, $sql_update)) {
            $mensagem = "Dados do cliente atualizados com sucesso!";
            $sucesso = true;
        } else {
            $mensagem = "Erro ao atualizar cliente: " . mysqli_error($conn);
        }
    }
}

// Busca os dados atuais do cliente para exibir no formulário
$sql_busca = "SELECT * FROM clientes WHERE id_cliente = $id_cliente";
$res_busca = mysqli_query($conn, $sql_busca);
$cliente = mysqli_fetch_assoc($res_busca);

// Se o cliente não existir no banco, volta para a lista
if (!$cliente) {
    header("Location: listar.php");
    exit;
}

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold"> <i class="bi bi-pencil-square"></i> Editar Cliente</h1>
        <a href="listar.php" class="btn btn-secondary">Voltar para Lista</a>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert <?php echo $sucesso ? 'alert-success' : 'alert-danger'; ?> shadow-sm fw-bold">
            <?php echo $mensagem; ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0 border-start border-4 border-success">
        <div class="card-body p-4">
            <form method="post" action="editar.php?id=<?php echo $id_cliente; ?>">
                
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-bold">Nome Completo *</label>
                        <input type="text" class="form-control" name="nome" value="<?php echo htmlspecialchars($cliente['nome']); ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Data de Nascimento</label>
                        <input type="date" class="form-control" name="data_nascimento" value="<?php echo $cliente['data_nascimento']; ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">CPF *</label>
                        <input type="text" class="form-control" name="cpf" value="<?php echo htmlspecialchars($cliente['cpf']); ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">RG</label>
                        <input type="text" class="form-control" name="rg" value="<?php echo htmlspecialchars($cliente['rg']); ?>">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Telefone / WhatsApp</label>
                        <input type="text" class="form-control" name="telefone" value="<?php echo htmlspecialchars($cliente['telefone']); ?>">
                    </div>
                </div>

                <div class="mb-3 mt-2">
                    <label class="form-label fw-bold">Endereço Completo</label>
                    <input type="text" class="form-control" name="endereco" value="<?php echo htmlspecialchars($cliente['endereco']); ?>" placeholder="Ex: Rua das Flores, 123 - Centro">
                </div>

                <hr class="mt-4">
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-success fw-bold" type="submit">Salvar Alterações</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>