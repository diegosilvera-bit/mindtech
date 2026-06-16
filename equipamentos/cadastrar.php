<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA: Perfis autorizados a cadastrar equipamentos
verificarAcesso(['G', 'A', 'T']);

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

$mensagem = ''; 

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Pega o ID do cliente selecionado no dropdown e os demais dados
    $id_cliente = (int)$_POST['id_cliente'];
    $tipo = $_POST['tipo'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $numero_serie = $_POST['numero_serie'];
    $observacoes = $_POST['observacoes'];

    // Validação básica (Cliente e Tipo são obrigatórios)
    if ($id_cliente <= 0 || empty($tipo)) {
        $mensagem = "Por favor, selecione um Cliente e informe o Tipo do equipamento.";
    } else {
        
        // Proteção para evitar erros ou quebras se o usuário digitar aspas
        $tipo = mysqli_real_escape_string($conn, $tipo);
        $marca = mysqli_real_escape_string($conn, $marca);
        $modelo = mysqli_real_escape_string($conn, $modelo);
        $numero_serie = mysqli_real_escape_string($conn, $numero_serie);
        $observacoes = mysqli_real_escape_string($conn, $observacoes);

        // Monta o comando SQL para inserir o equipamento com o ID do cliente automatizado
        $sql = "INSERT INTO equipamentos (id_cliente, tipo, marca, modelo, numero_serie, observacoes) 
                VALUES ($id_cliente, '$tipo', '$marca', '$modelo', '$numero_serie', '$observacoes')";
        
        if (mysqli_query($conn, $sql)) {
            $mensagem = "Equipamento cadastrado e vinculado ao cliente com sucesso!";
        } else {
            $mensagem = "Erro ao cadastrar o equipamento: " . mysqli_error($conn);
        }
    }
}

// BUSCA AUTOMÁTICA DE CLIENTES: Puxa todos os clientes ordenados por nome para preencher o select
$sql_clientes = "SELECT id_cliente, nome, cpf FROM clientes ORDER BY nome ASC";
$res_clientes = mysqli_query($conn, $sql_clientes);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Cadastrar Equipamento</h1>
        <a href="listar.php" class="btn btn-secondary">Voltar para Lista</a>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert alert-info shadow-sm fw-bold">
            <?php echo $mensagem; ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0 border-start border-4 border-info">
        <div class="card-body p-4">
            <form method="post" action="cadastrar.php">
                
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark">Selecione o Cliente (Dono) *</label>
                    <select class="form-select form-select-lg" name="id_cliente" required>
                        <option value="">Clique aqui para buscar o cliente...</option>
                        <?php 
                        if ($res_clientes && mysqli_num_rows($res_clientes) > 0) {
                            while($cli = mysqli_fetch_assoc($res_clientes)) {
                                echo "<option value='".$cli['id_cliente']."'>".htmlspecialchars($cli['nome'])." (CPF: ".$cli['cpf'].")</option>";
                            }
                        }
                        ?>
                    </select>
                    <small class="text-muted">Não é necessário digitar IDs. Selecione o cliente pelo nome para vinculá-lo automaticamente ao aparelho.</small>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Tipo de Equipamento *</label>
                        <select class="form-select" name="tipo" required>
                            <option value="">Selecione...</option>
                            <option value="Notebook">Notebook</option>
                            <option value="Desktop (PC)">Desktop (PC)</option>
                            <option value="Smartphone">Smartphone</option>
                            <option value="Tablet">Tablet</option>
                            <option value="Monitor">Monitor</option>
                            <option value="Impressora">Impressora</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Marca</label>
                        <input type="text" class="form-control" name="marca" placeholder="Ex: Dell, Samsung, HP...">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Modelo</label>
                        <input type="text" class="form-control" name="modelo" placeholder="Ex: Inspiron 15 3000">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Número de Série (S/N)</label>
                        <input type="text" class="form-control" name="numero_serie" placeholder="Ex: BR-123456789">
                    </div>
                </div>

                <div class="mb-3 mt-2">
                    <label class="form-label fw-bold">Observações / Estado Físico</label>
                    <textarea class="form-control" name="observacoes" rows="3" placeholder="Ex: Aparelho chegou com a tela trincada e marcas de uso na carcaça..."></textarea>
                </div>

                <hr class="mt-4">
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-success text-white fw-bold" type="submit">Salvar Equipamento</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>