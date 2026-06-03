<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 
include '../includes/header.php'; 


$mensagem = ''; 

// Pega o ID da Ordem de Serviço pela URL (ex: orcamento.php?id=1)
$id_os = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// PROCESSO DE SALVAR AS ALTERAÇÕES (POST)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario_responsavel = $_POST['id_usuario_responsavel'];
    $observacoes = mysqli_real_escape_string($conn, $_POST['observacoes']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $prazo_garantia = (int)$_POST['prazo_garantia'];
    
    // Tratamento para o caso de não selecionar nenhum usuário
    $usuario_sql = ($id_usuario_responsavel == '') ? "NULL" : (int)$id_usuario_responsavel;

    // Atualiza a OS com o técnico responsável e os dados do orçamento
    $sql_update = "UPDATE ordens_servico SET 
                    id_usuario_responsavel = $usuario_sql,
                    observacoes = '$observacoes',
                    status = '$status',
                    prazo_garantia = $prazo_garantia
                   WHERE id_os = $id_os";

    if (mysqli_query($conn, $sql_update)) {
        $mensagem = "Orçamento/OS atualizado com sucesso!";
    } else {
        $mensagem = "Erro ao atualizar Orçamento: " . mysqli_error($conn);
    }
}

// BUSCA OS DADOS DA OS E DO CLIENTE/EQUIPAMENTO

$sql_os = "SELECT os.*, c.nome AS nome_cliente, e.marca, e.modelo, e.tipo 
           FROM ordens_servico os
           JOIN clientes c ON os.id_cliente = c.id_cliente
           JOIN equipamentos e ON os.id_equipamento = e.id_equipamento
           WHERE os.id_os = $id_os";

$result_os = mysqli_query($conn, $sql_os);
$os = mysqli_fetch_assoc($result_os);

// Se a OS não existir, volta para a listagem
if (!$os) {
    header("Location: listar.php");
    exit;
}

// BUSCA OS TÉCNICOS E GERENTES PARA O SELECT
// Filtra apenas quem é Técnico (T) ou Gerente (G) para não listar atendentes no orçamento

$sql_usuarios = "SELECT id_usuario, nome FROM usuarios WHERE perfil IN ('T', 'G') ORDER BY nome ASC";
$result_usuarios = mysqli_query($conn, $sql_usuarios);

?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Orçamento / Gerenciar OS #<?php echo $os['id_os']; ?></h1>
        <a href="listar.php" class="btn btn-secondary me-2">Voltar para a Lista</a>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert alert-info shadow-sm">
            <?php echo $mensagem; ?>
        </div>
    <?php } ?>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-light border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Dados do Cliente</h5>
                    <p class="mb-0 fw-bold fs-5 text-dark"><?php echo $os['nome_cliente']; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-light border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Equipamento</h5>
                    <p class="mb-0 fw-bold fs-5 text-dark">
                        <span class="badge bg-secondary me-2"><?php echo $os['tipo']; ?></span>
                        <?php echo $os['marca'] . ' ' . $os['modelo']; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 border-start border-4 border-warning">
        <div class="card-body p-4">
            <form method="post" action="">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Técnico Responsável *</label>
                        <select class="form-select" name="id_usuario_responsavel" required>
                            <option value="">-- Selecione o Técnico --</option>
                            <?php 
                            if ($result_usuarios && mysqli_num_rows($result_usuarios) > 0) {
                                while ($user = mysqli_fetch_assoc($result_usuarios)) {
                                    // Verifica se este usuário já é o responsável atual da OS
                                    $selected = ($os['id_usuario_responsavel'] == $user['id_usuario']) ? 'selected' : '';
                                    echo "<option value='{$user['id_usuario']}' {$selected}>{$user['nome']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Status do Serviço *</label>
                        <select class="form-select" name="status" required>
                            <option value="EM_ANDAMENTO" <?php echo $os['status'] == 'EM_ANDAMENTO' ? 'selected' : ''; ?>>Em Andamento</option>
                            <option value="ORCAMENTO" <?php echo $os['status'] == 'ORCAMENTO' ? 'selected' : ''; ?>>Aguardando Orçamento</option>
                            <option value="FINALIZADO" <?php echo $os['status'] == 'FINALIZADO' ? 'selected' : ''; ?>>Finalizado / Concluído</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Garantia (Dias)</label>
                        <input type="number" class="form-control" name="prazo_garantia" value="<?php echo (int)$os['prazo_garantia']; ?>" min="0" placeholder="Ex: 90">
                    </div>
                </div>

                <div class="mb-3 mt-2">
                    <label class="form-label fw-bold">Observações / Laudo Técnico</label>
                    <textarea class="form-control" name="observacoes" rows="5" placeholder="Descreva os defeitos relatados, testes efetuados e a solução do problema..."><?php echo $os['observacoes']; ?></textarea>
                </div>

                <hr class="mt-4">
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-warning text-dark fw-bold" type="submit">Salvar Orçamento</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>