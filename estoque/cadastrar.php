<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Traz a conexão com o banco de dados
include '../config/conexao.php'; 

$mensagem = ''; 

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Pega os dados do formulário de forma simples
    $nome_item = $_POST['nome_item'];
    $quantidade = $_POST['quantidade'];
    $valor_unitario = $_POST['valor_unitario'];

    // Verifica se os campos principais estão vazios
    if ($nome_item == '' || $quantidade == '') {
        $mensagem = "Por favor, preencha o Nome e a Quantidade.";
    } else {
        
        // Se o valor unitário ficar vazio, define como zero
        if ($valor_unitario == '') {
            $valor_unitario = '0.00';
        }

        // Proteção básica para evitar erros no banco se o usuário digitar aspas
        $nome_item = mysqli_real_escape_string($conn, $nome_item);

        // Monta o comando SQL
        $sql = "INSERT INTO estoque (nome_item, quantidade, valor_unitario) 
                VALUES ('$nome_item', '$quantidade', '$valor_unitario')";
        
        // Executa o comando e verifica se deu certo
        if (mysqli_query($conn, $sql)) {
            $mensagem = "Item cadastrado com sucesso no estoque!";
        } else {
            $mensagem = "Erro ao salvar no banco: " . mysqli_error($conn);
        }
    }
}

include '../includes/header.php'; 
?>

<div class="container mt-4">
    <h1>Cadastrar Estoque</h1>
    <hr>

    <?php if ($mensagem != '') { ?>
        <div class="alert alert-info">
            <?php echo $mensagem; ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" action="">
                
                <div class="mb-3">
                    <label class="form-label">Nome do Item / Peça</label>
                    <input type="text" class="form-control" name="nome_item" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Quantidade</label>
                    <input type="number" class="form-control" name="quantidade" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Valor Unitário (R$)</label>
                    <input type="number" step="0.01" class="form-control" name="valor_unitario">
                </div>

                <br>
                <button class="btn btn-success" type="submit">Salvar Item</button>
                <a href="listar.php" class="btn btn-secondary me-2">Voltar para a Lista</a>
                
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>