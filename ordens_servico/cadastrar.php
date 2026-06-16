<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA: Gerente, Atendimento e Técnico podem abrir O.S.
verificarAcesso(['G', 'A', 'T']);

// Inclui a conexão com o banco
include '../config/conexao.php'; 

$mensagem = ''; 

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Pega apenas o ID do equipamento (o cliente nós descobrimos pelo banco!)
    $id_equipamento = (int)$_POST['id_equipamento'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $observacoes = mysqli_real_escape_string($conn, $_POST['observacoes']);

    if ($id_equipamento <= 0) {
        $mensagem = "Por favor, selecione um equipamento válido.";
    } else {
        // Busca qual é o cliente dono deste equipamento automaticamente
        $query_dono = "SELECT id_cliente FROM equipamentos WHERE id_equipamento = $id_equipamento";
        $res_dono = mysqli_query($conn, $query_dono);
        $dono = mysqli_fetch_assoc($res_dono);
        $id_cliente = $dono['id_cliente'];

        // Monta o comando SQL com os dados corretos
        $sql = "INSERT INTO ordens_servico (id_cliente, id_equipamento, status, observacoes) 
                VALUES ($id_cliente, $id_equipamento, '$status', '$observacoes')";
        
        // Executa o comando e verifica o resultado
        if (mysqli_query($conn, $sql)) {
            $mensagem = "Ordem de Serviço criada e vinculada com sucesso!";
        } else {
            $mensagem = "Erro ao criar a OS: " . mysqli_error($conn);
        }
    }
}

// Busca a lista de equipamentos e cruza com o nome dos clientes para o Select
$sql_equipamentos = "SELECT e.id_equipamento, e.marca, e.modelo, c.nome AS cliente_nome 
                     FROM equipamentos e 
                     INNER JOIN clientes c ON e.id_cliente = c.id_cliente 
                     ORDER BY c.nome ASC";
$res_equipamentos = mysqli_query($conn, $sql_equipamentos);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Nova Ordem de Serviço</h1>
        <a href="listar.php" class="btn btn-secondary me-2">Voltar para a Lista</a>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert alert-info shadow-sm fw-bold">
            <?php echo $mensagem; ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0 border-start border-4 border-primary">
        <div class="card-body p-4">
            <form method="post" action="cadastrar.php">
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Selecione o Cliente e o Equipamento *</label>
                    <select class="form-select form-select-lg" name="id_equipamento" required>
                        <option value="">Clique para selecionar...</option>
                        <?php while($eq = mysqli_fetch_assoc($res_equipamentos)): ?>
                            <option value="<?php echo $eq['id_equipamento']; ?>">
                                Cliente: <?php echo $eq['cliente_nome']; ?> —— Aparelho: <?php echo $eq['marca'] . ' ' . $eq['modelo']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <small class="text-muted">A O.S. será vinculada automaticamente ao cliente dono deste aparelho.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Status Inicial</label>
                    <select class="form-select" name="status">
                        <option value="EM_ANALISE">1. Em Análise (Aguardando Orçamento)</option>
                        <option value="EM_REPARO">2. Em Reparo (Laboratório)</option>
                        <option value="AGUARDANDO_PECA">3. Aguardando Peça</option>
                        <option value="FINALIZADO">4. Finalizado</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Problema Relatado / Observações Iniciais *</label>
                    <textarea class="form-control" name="observacoes" rows="4" placeholder="Descreva os sintomas do aparelho, arranhões, se veio com carregador..." required></textarea>
                </div>

                <hr class="mt-4">
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-success" type="submit">Salvar Ordem de Serviço</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>