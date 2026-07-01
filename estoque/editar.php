<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>

<style>
    .ts-dropdown .highlight {
        background: transparent !important;
        color: inherit !important;
        text-decoration: none !important;
        font-weight: bold !important;
    }
</style>

<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA
verificarAcesso(['G', 'A', 'E', 'T']);

include '../config/conexao.php'; 

$mensagem = ''; 
$sucesso = false;

$id_peca = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $codigo = mysqli_real_escape_string($conn, $_POST['codigo']);
    $descricao = mysqli_real_escape_string($conn, $_POST['descricao']);
    $quantidade = $_POST['quantidade_disponivel'] == '' ? 0 : (int)$_POST['quantidade_disponivel'];
    $valor_unitario = $_POST['valor_unitario'] == '' ? '0.00' : $_POST['valor_unitario'];
    $nivel_minimo = $_POST['nivel_minimo'] == '' ? 0 : (int)$_POST['nivel_minimo'];
    $nivel_maximo = $_POST['nivel_maximo'] == '' ? 0 : (int)$_POST['nivel_maximo'];
    
    // Pega o ID do fornecedor
    $id_fornecedor = !empty($_POST['id_fornecedor']) ? (int)$_POST['id_fornecedor'] : 'NULL';

    $sql_update = "UPDATE pecas SET 
                    codigo = '$codigo', 
                    descricao = '$descricao', 
                    quantidade_disponivel = $quantidade, 
                    valor_unitario = '$valor_unitario', 
                    nivel_minimo = $nivel_minimo, 
                    nivel_maximo = $nivel_maximo,
                    id_fornecedor = $id_fornecedor
                  WHERE id_peca = $id_peca";

    if (mysqli_query($conn, $sql_update)) {
        $mensagem = "Dados da peça atualizados com sucesso!";
        $sucesso = true;
    } else {
        $mensagem = "Erro ao atualizar a peça: " . mysqli_error($conn);
    }
}

// Busca a peça atual
$sql_peca = "SELECT * FROM pecas WHERE id_peca = $id_peca";
$res_peca = mysqli_query($conn, $sql_peca);
$peca = mysqli_fetch_assoc($res_peca);

if (!$peca) {
    header("Location: listar.php");
    exit;
}

// Busca fornecedores
$res_fornecedores = mysqli_query($conn, "SELECT id_fornecedor, nome FROM fornecedores ORDER BY nome ASC");

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold"><i class="bi bi-pencil-square text-light me-2"></i>Editar Peça</h1>
        <a href="listar.php" class="btn btn-secondary">Voltar para Lista</a>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert <?php echo $sucesso ? 'alert-success' : 'alert-danger'; ?> shadow-sm fw-bold">
            <?php echo $mensagem; ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0 border-start border-4 border-danger">
        <div class="card-body p-4">
            <form method="post" action="editar.php?id=<?php echo $id_peca; ?>">
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Código da Peça *</label>
                        <input type="text" class="form-control" name="codigo" value="<?php echo htmlspecialchars($peca['codigo']); ?>" required>
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-bold">Descrição Completa *</label>
                        <input type="text" class="form-control" name="descricao" value="<?php echo htmlspecialchars($peca['descricao']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Fornecedor Principal</label>
                        <select class="form-select" name="id_fornecedor" id="id_fornecedor" style="border-radius: 8px;">
                            <option value="" disabled <?php echo empty($peca['id_fornecedor']) ? 'selected' : ''; ?>>-- Selecione o Fornecedor ou deixe em branco --</option>
                            <?php 
                            if ($res_fornecedores && mysqli_num_rows($res_fornecedores) > 0) {
                                while($f = mysqli_fetch_assoc($res_fornecedores)) {
                                    $selected = (isset($peca['id_fornecedor']) && $peca['id_fornecedor'] == $f['id_fornecedor']) ? 'selected' : '';
                                    echo "<option value='".$f['id_fornecedor']."' $selected>".htmlspecialchars($f['nome'])."</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Quantidade Atual</label>
                        <input type="number" class="form-control" name="quantidade_disponivel" value="<?php echo $peca['quantidade_disponivel']; ?>">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Valor Unitário (R$)</label>
                        <input type="number" step="0.01" class="form-control" name="valor_unitario" value="<?php echo $peca['valor_unitario']; ?>">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold text-danger">Nível Mínimo</label>
                        <input type="number" class="form-control" name="nivel_minimo" value="<?php echo $peca['nivel_minimo']; ?>">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold text-success">Nível Máximo</label>
                        <input type="number" class="form-control" name="nivel_maximo" value="<?php echo $peca['nivel_maximo']; ?>">
                    </div>
                </div>

                <hr class="mt-4">
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-danger fw-bold" type="submit">Salvar Alterações</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    // Regra de busca estrita: o item deve obrigatoriamente INICIAR com o termo pesquisado
    function funcaoBuscaEstrita(search) {
        const query = search.trim().toLowerCase();
        return function(item) {
            if (!query) return 1;
            return item.text.toLowerCase().startsWith(query) ? 1 : 0;
        };
    }

    // Inicializa o Buscador de Fornecedores
    const buscadorFornecedor = new TomSelect("#id_fornecedor", {
        create: false,
        placeholder: "-- Selecione o Fornecedor ou deixe em branco --",
        allowEmptyOption: false, // Impede de selecionar a opção vazia caso deseje manter o formato "disabled selected"
        score: funcaoBuscaEstrita
    });
</script>

<?php include '../includes/footer.php'; ?>