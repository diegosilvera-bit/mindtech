<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

$mensagem = ''; 

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Pega os dados do formulário de forma simples
    $id_cliente = $_POST['id_cliente'];
    $tipo = $_POST['tipo'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $numero_serie = $_POST['numero_serie'];
    $observacoes = $_POST['observacoes'];

    // Validação básica (Cliente e Tipo são obrigatórios)
    if ($id_cliente == '' || $tipo == '') {
        $mensagem = "Os campos ID do Cliente e Tipo são obrigatórios.";
    } else {
        
        // Proteção simples para evitar erros no banco de dados se o usuário digitar aspas
        $tipo = mysqli_real_escape_string($conn, $tipo);
        $marca = mysqli_real_escape_string($conn, $marca);
        $modelo = mysqli_real_escape_string($conn, $modelo);
        $numero_serie = mysqli_real_escape_string($conn, $numero_serie);
        $observacoes = mysqli_real_escape_string($conn, $observacoes);

        // Monta o comando SQL para inserir o equipamento
        $sql = "INSERT INTO equipamentos (id_cliente, tipo, marca, modelo, numero_serie, observacoes) 
                VALUES ('$id_cliente', '$tipo', '$marca', '$modelo', '$numero_serie', '$observacoes')";
        
        // Executa o comando e verifica o resultado
        if (mysqli_query($conn, $sql)) {
            $mensagem = "Equipamento cadastrado com sucesso!";
        } else {
            $mensagem = "Erro ao cadastrar o equipamento: " . mysqli_error($conn);
        }
    }
}

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Cadastrar Novo Equipamento</h1>
        <a href="listar.php" class="btn btn-secondary me-2">Voltar para a Lista</a>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert alert-info shadow-sm">
            <?php echo $mensagem; ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0 border-start border-4 border-info">
        <div class="card-body p-4">
            <p class="text-muted mb-4">Preencha as informações do aparelho que o cliente deixou na assistência.</p>
            
            <form method="post" action="">
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">ID do Cliente (Dono) *</label>
                        <input type="number" class="form-control" name="id_cliente" placeholder="Ex: 1" required>
                        <small class="text-muted">Número de cadastro do cliente.</small>
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-bold">Tipo de Equipamento *</label>
                        <input type="text" class="form-control" name="tipo" placeholder="Ex: Notebook, Desktop, Impressora, Celular..." required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Marca</label>
                        <input type="text" class="form-control" name="marca" placeholder="Ex: Dell, Samsung, HP...">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Modelo</label>
                        <input type="text" class="form-control" name="modelo" placeholder="Ex: Inspiron 15 3000">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Número de Série (S/N)</label>
                        <input type="text" class="form-control" name="numero_serie" placeholder="Ex: BR-123456789">
                    </div>
                </div>

                <div class="mb-3 mt-2">
                    <label class="form-label fw-bold">Observações / Estado Físico</label>
                    <textarea class="form-control" name="observacoes" rows="3" placeholder="Ex: Aparelho chegou com a tela trincada e sem carregador..."></textarea>
                </div>

                <hr class="mt-4">
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-info text-white" type="submit">Salvar Equipamento</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>