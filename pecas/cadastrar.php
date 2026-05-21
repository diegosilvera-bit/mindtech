<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

$mensagem = ''; 

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Pega os dados do formulário de forma simples
    $codigo = $_POST['codigo'];
    $descricao = $_POST['descricao'];
    $quantidade = $_POST['quantidade_disponivel'];
    $valor_unitario = $_POST['valor_unitario'];
    $nivel_minimo = $_POST['nivel_minimo'];
    $nivel_maximo = $_POST['nivel_maximo'];

    // Validação básica (Código e Descrição são obrigatórios)
    if ($codigo == '' || $descricao == '') {
        $mensagem = "Os campos Código e Descrição são obrigatórios.";
    } else {
        
        // Se os campos de números ficarem vazios, define como zero para não dar erro no banco
        if ($quantidade == '') { $quantidade = 0; }
        if ($valor_unitario == '') { $valor_unitario = '0.00'; }
        if ($nivel_minimo == '') { $nivel_minimo = 0; }
        if ($nivel_maximo == '') { $nivel_maximo = 0; }

        // Proteção contra aspas no texto (evita erros no SQL)
        $codigo = mysqli_real_escape_string($conn, $codigo);
        $descricao = mysqli_real_escape_string($conn, $descricao);

        // Monta o comando SQL para a tabela pecas
        $sql = "INSERT INTO pecas (codigo, descricao, quantidade_disponivel, valor_unitario, nivel_minimo, nivel_maximo) 
                VALUES ('$codigo', '$descricao', '$quantidade', '$valor_unitario', '$nivel_minimo', '$nivel_maximo')";
        
        // Executa o comando e verifica se deu certo
        if (mysqli_query($conn, $sql)) {
            $mensagem = "Peça cadastrada com sucesso no sistema!";
        } else {
            $mensagem = "Erro ao cadastrar a peça: " . mysqli_error($conn);
        }
    }
}

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Cadastrar Nova Peça</h1>
        <a href="listar.php" class="btn btn-outline-secondary">Voltar para a Lista</a>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert alert-info shadow-sm">
            <?php echo $mensagem; ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0 border-start border-4 border-danger">
        <div class="card-body p-4">
            <p class="text-muted mb-4">Preencha as informações para adicionar um novo componente ao catálogo.</p>
            
            <form method="post" action="">
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Código da Peça *</label>
                        <input type="text" class="form-control" name="codigo" placeholder="Ex: CABO-HDMI" required>
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-bold">Descrição da Peça *</label>
                        <input type="text" class="form-control" name="descricao" placeholder="Ex: Cabo HDMI 2.0 - 2 Metros" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Quantidade Inicial</label>
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