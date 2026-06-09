<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA
verificarAcesso(['G', 'A', 'E', 'T']);

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

$mensagem = ''; 

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Pega os dados do formulário
    $codigo = mysqli_real_escape_string($conn, $_POST['codigo']);
    $descricao = mysqli_real_escape_string($conn, $_POST['descricao']);
    $quantidade = $_POST['quantidade_disponivel'] == '' ? 0 : (int)$_POST['quantidade_disponivel'];
    $valor_unitario = $_POST['valor_unitario'] == '' ? '0.00' : $_POST['valor_unitario'];
    $nivel_minimo = $_POST['nivel_minimo'] == '' ? 0 : (int)$_POST['nivel_minimo'];
    $nivel_maximo = $_POST['nivel_maximo'] == '' ? 0 : (int)$_POST['nivel_maximo'];
    
    // NOVO: Pega o Fornecedor (se não selecionar, grava como NULL)
    $id_fornecedor = !empty($_POST['id_fornecedor']) ? (int)$_POST['id_fornecedor'] : 'NULL';

    if ($codigo == '' || $descricao == '') {
        $mensagem = "Os campos Código e Descrição são obrigatórios.";
    } else {
        // Monta o comando SQL para inserir a peça com o fornecedor
        $sql = "INSERT INTO pecas (codigo, descricao, quantidade_disponivel, valor_unitario, nivel_minimo, nivel_maximo, id_fornecedor) 
                VALUES ('$codigo', '$descricao', $quantidade, '$valor_unitario', $nivel_minimo, $nivel_maximo, $id_fornecedor)";
        
        if (mysqli_query($conn, $sql)) {
            $mensagem = "Peça cadastrada no catálogo com sucesso!";
        } else {
            $mensagem = "Erro ao cadastrar a peça: " . mysqli_error($conn);
        }
    }
}

// NOVO: Busca os fornecedores para a Dropdown
$res_fornecedores = mysqli_query($conn, "SELECT id_fornecedor, nome FROM fornecedores ORDER BY nome ASC");

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Cadastrar Nova Peça</h1>
        <a href="listar.php" class="btn btn-secondary">Voltar para Lista</a>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert alert-info shadow-sm fw-bold">
            <?php echo $mensagem; ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0 border-start border-4 border-danger">
        <div class="card-body p-4">
            <form method="post" action="cadastrar.php">
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Código da Peça *</label>
                        <input type="text" class="form-control" name="codigo" placeholder="Ex: TELA-IPHONE-13" required>
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-bold">Descrição Completa *</label>
                        <input type="text" class="form-control" name="descricao" placeholder="Ex: Tela Display LCD iPhone 13 Original" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Fornecedor Principal (Opcional)</label>
                        <select class="form-select" name="id_fornecedor">
                            <option value="">-- Selecione o Fornecedor ou deixe em branco --</option>
                            <?php 
                            if ($res_fornecedores && mysqli_num_rows($res_fornecedores) > 0) {
                                while($f = mysqli_fetch_assoc($res_fornecedores)) {
                                    echo "<option value='".$f['id_fornecedor']."'>".htmlspecialchars($f['nome'])."</option>";
                                }
                            }
                            ?>
                        </select>
                        <small class="text-muted">Ajuda a saber de quem comprar quando o stock estiver baixo.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Quantidade Atual</label>
                        <input type="number" class="form-control" name="quantidade_disponivel" placeholder="0">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Valor Unitário (R$)</label>
                        <input type="number" step="0.01" class="form-control" name="valor_unitario" placeholder="0.00">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold text-danger">Nível Mínimo</label>
                        <input type="number" class="form-control" name="nivel_minimo" placeholder="Ex: 5">
                        <small class="text-muted">Avisa se o estoque baixar.</small>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold text-success">Nível Máximo</label>
                        <input type="number" class="form-control" name="nivel_maximo" placeholder="Ex: 50">
                    </div>
                </div>

                <hr class="mt-4">
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-danger" type="submit">Salvar Peça</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>