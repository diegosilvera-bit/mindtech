<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

$mensagem = ''; 

// Pega o ID da peça que veio pela URL
$id_peca = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Se o formulário foi enviado para ATUALIZAR a peça
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Pega os novos dados digitados
    $codigo = mysqli_real_escape_string($conn, $_POST['codigo']);
    $descricao = mysqli_real_escape_string($conn, $_POST['descricao']);
    $quantidade = $_POST['quantidade_disponivel'] == '' ? 0 : $_POST['quantidade_disponivel'];
    $valor_unitario = $_POST['valor_unitario'] == '' ? '0.00' : $_POST['valor_unitario'];
    $nivel_minimo = $_POST['nivel_minimo'] == '' ? 0 : $_POST['nivel_minimo'];
    $nivel_maximo = $_POST['nivel_maximo'] == '' ? 0 : $_POST['nivel_maximo'];

    // Monta o comando de atualização
    $sql_update = "UPDATE pecas SET 
                    codigo = '$codigo', 
                    descricao = '$descricao', 
                    quantidade_disponivel = '$quantidade', 
                    valor_unitario = '$valor_unitario', 
                    nivel_minimo = '$nivel_minimo', 
                    nivel_maximo = '$nivel_maximo' 
                   WHERE id_peca = $id_peca";
    
    if (mysqli_query($conn, $sql_update)) {
        $mensagem = "Peça atualizada com sucesso!";
    } else {
        $mensagem = "Erro ao atualizar a peça: " . mysqli_error($conn);
    }
}

// Busca os dados da peça para PREENCHER O FORMULÁRIO na tela
$sql_busca = "SELECT * FROM pecas WHERE id_peca = $id_peca";
$result = mysqli_query($conn, $sql_busca);
$peca = mysqli_fetch_assoc($result);

// Se não achar a peça no banco (alguém digitou um ID errado na URL), devolve para a lista
if (!$peca) {
    header("Location: listar.php");
    exit;
}

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Editar Peça: <?php echo $peca['codigo']; ?></h1>
        <a href="listar.php" class="btn btn-outline-secondary">Voltar para a Lista</a>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert alert-info shadow-sm">
            <?php echo $mensagem; ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0 border-start border-4 border-danger">
        <div class="card-body p-4">
            
            <form method="post" action="">
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Código da Peça *</label>
                        <input type="text" class="form-control" name="codigo" value="<?php echo $peca['codigo']; ?>" required>
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-bold">Descrição da Peça *</label>
                        <input type="text" class="form-control" name="descricao" value="<?php echo $peca['descricao']; ?>" required>
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
                    <button class="btn btn-danger" type="submit">Salvar Alterações</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>