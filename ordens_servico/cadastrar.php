<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a conexão com o banco
include '../config/conexao.php'; 

$mensagem = ''; 

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Pega os dados do formulário
    $id_cliente = $_POST['id_cliente'];
    $id_equipamento = $_POST['id_equipamento'];
    $status = $_POST['status'];
    $observacoes = $_POST['observacoes'];

    // Validação básica
    if ($id_cliente == '' || $id_equipamento == '') {
        $mensagem = "Os campos ID do Cliente e ID do Equipamento são obrigatórios.";
    } else {
        // Proteção contra aspas e erros no banco
        $observacoes = mysqli_real_escape_string($conn, $observacoes);

        // Monta o comando SQL
        $sql = "INSERT INTO ordens_servico (id_cliente, id_equipamento, status, observacoes) 
                VALUES ('$id_cliente', '$id_equipamento', '$status', '$observacoes')";
        
        // Executa o comando e verifica o resultado
        if (mysqli_query($conn, $sql)) {
            $mensagem = "Ordem de Serviço criada com sucesso!";
        } else {
            $mensagem = "Erro ao criar a OS: " . mysqli_error($conn);
        }
    }
}

include '../includes/header.php'; 
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Nova Ordem de Serviço</h1>
        <a href="listar.php" class="btn btn-outline-secondary">Voltar para a Lista</a>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert alert-info shadow-sm">
            <?php echo $mensagem; ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0 border-start border-4 border-primary">
        <div class="card-body p-4">
            <form method="post" action="">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">ID do Cliente *</label>
                        <input type="number" class="form-control" name="id_cliente" placeholder="Ex: 1" required>
                        <small class="text-muted">Número de cadastro do cliente.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">ID do Equipamento *</label>
                        <input type="number" class="form-control" name="id_equipamento" placeholder="Ex: 5" required>
                        <small class="text-muted">Número do equipamento registrado.</small>
                    </div>
                </div>

                <div class="mb-3 mt-2">
                    <label class="form-label fw-bold">Status Inicial</label>
                    <select class="form-select" name="status">
                        <option value="EM_ANALISE">Em Análise</option>
                        <option value="EM_REPARO">Em Reparo</option>
                        <option value="AGUARDANDO_PECA">Aguardando Peça</option>
                        <option value="FINALIZADO">Finalizado</option>
                        <option value="CANCELADO">Cancelado</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Problema Relatado / Observações</label>
                    <textarea class="form-control" name="observacoes" rows="4" placeholder="Descreva o problema que o cliente informou..."></textarea>
                </div>

                <hr class="mt-4">
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-primary" type="submit">Salvar Ordem de Serviço</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>