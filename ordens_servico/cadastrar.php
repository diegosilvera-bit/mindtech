<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// TRAVA DE SEGURANÇA: Gerente, Atendimento e Técnico podem abrir O.S.
verificarAcesso(['G', 'A', 'T']);

// Inclui a conexão com o banco
include '../config/conexao.php'; 

$mensagem = ''; 
$tipo_alerta = 'danger';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $id_cliente = (int)$_POST['id_cliente'];
    $id_equipamento = (int)$_POST['id_equipamento'];
    $id_tecnico = !empty($_POST['id_tecnico']) ? (int)$_POST['id_tecnico'] : 'NULL';
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $observacoes = mysqli_real_escape_string($conn, trim($_POST['observacoes']));

    if ($id_cliente <= 0) {
        $mensagem = "Por favor, selecione um cliente.";
    } elseif ($id_equipamento <= 0) {
        $mensagem = "Por favor, selecione um equipamento válido.";
    } elseif (empty($observacoes)) {
        $mensagem = "Por favor, descreva o problema relatado no campo de observações.";
    } else {
        // Verifica se o equipamento pertence mesmo ao cliente por segurança
        $query_valida = "SELECT id_equipamento FROM equipamentos WHERE id_equipamento = $id_equipamento AND id_cliente = $id_cliente AND ativo = 1";
        $res_valida = mysqli_query($conn, $query_valida);
        
        if ($res_valida && mysqli_num_rows($res_valida) > 0) {
            
            // Insere a O.S. vinculando Cliente, Equipamento e o Técnico Responsável
            $sql_insert = "INSERT INTO ordens_servico (id_equipamento, id_cliente, id_usuario_responsavel, status, observacoes, data_entrada) 
                           VALUES ($id_equipamento, $id_cliente, $id_tecnico, '$status', '$observacoes', NOW())";

            if (mysqli_query($conn, $sql_insert)) {
                $id_gerado = mysqli_insert_id($conn);
                
                // Aplica o padrão PRG: Redireciona imediatamente para evitar duplicidade no F5
                header("Location: visualizar.php?id=" . $id_gerado . "&cadastro=sucesso");
                exit;
            } else {
                $mensagem = "Erro ao registrar a Ordem de Serviço: " . mysqli_error($conn);
            }
        } else {
            $mensagem = "Equipamento não encontrado, inativo ou não pertence ao cliente selecionado.";
        }
    }
}

// 1. Busca todos os clientes ativos para o primeiro Select
$sql_clientes = "SELECT id_cliente, nome FROM clientes WHERE ativo = 1 ORDER BY nome ASC";
$res_clientes = mysqli_query($conn, $sql_clientes);

// 2. Busca os equipamentos ativos e os coloca num array para o JavaScript filtrar dinamicamente
$sql_equipamentos = "SELECT id_equipamento, id_cliente, tipo, marca, modelo FROM equipamentos WHERE ativo = 1 ORDER BY modelo ASC";
$res_equipamentos = mysqli_query($conn, $sql_equipamentos);
$array_equipamentos = [];
if ($res_equipamentos && mysqli_num_rows($res_equipamentos) > 0) {
    while ($row = mysqli_fetch_assoc($res_equipamentos)) {
        $array_equipamentos[] = $row;
    }
}

// 3. Busca os Técnicos (perfil = 'T') para o Select de responsáveis
$sql_tecnicos = "SELECT id_usuario, nome FROM usuarios WHERE perfil = 'T' ORDER BY nome ASC";
$res_tecnicos = mysqli_query($conn, $sql_tecnicos);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="bi bi-tools text-success me-2"></i>Abrir Ordem de Serviço</h1>
            <p class="text-muted small mb-0">Registre a entrada de um aparelho na assistência técnica.</p>
        </div>
        <a href="listar.php" class="btn btn-sm btn-outline-secondary fw-bold px-3">
            <i class="bi bi-arrow-left me-1"></i> Voltar à Lista
        </a>
    </div>

    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo $mensagem; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 border-start border-4 border-success">
        <div class="card-body p-4">
            <form method="POST" action="cadastrar.php">
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Cliente *</label>
                        <select class="form-select" name="id_cliente" id="id_cliente" required style="border-radius: 8px;" onchange="filtrarEquipamentos()">
                            <option value="" disabled selected>Selecione o cliente...</option>
                            <?php 
                            if ($res_clientes && mysqli_num_rows($res_clientes) > 0) {
                                while ($cli = mysqli_fetch_assoc($res_clientes)) {
                                    echo "<option value='{$cli['id_cliente']}'>" . htmlspecialchars($cli['nome']) . "</option>";
                                }
                            } else {
                                echo "<option value='' disabled>Nenhum cliente ativo.</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Aparelho do Cliente *</label>
                        <select class="form-select" name="id_equipamento" id="id_equipamento" required style="border-radius: 8px;">
                            <option value="" disabled selected>Selecione um cliente primeiro...</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status Inicial</label>
                        <select class="form-select" name="status" style="border-radius: 8px;">
                            <option value="EM_ANALISE" selected>1. Em Análise (Aguardando Orçamento)</option>
                            <option value="EM_REPARO">2. Em Reparo (Laboratório)</option>
                            <option value="AGUARDANDO_PECA">3. Aguardando Peça</option>
                            <option value="FINALIZADO">4. Finalizado</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Técnico Responsável</label>
                        <select class="form-select" name="id_tecnico" style="border-radius: 8px;">
                            <option value="" selected>Sem técnico definido no momento</option>
                            <?php 
                            if ($res_tecnicos && mysqli_num_rows($res_tecnicos) > 0) {
                                while ($tec = mysqli_fetch_assoc($res_tecnicos)) {
                                    echo "<option value='{$tec['id_usuario']}'>" . htmlspecialchars($tec['nome']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Problema Relatado / Observações Iniciais *</label>
                    <textarea class="form-control" name="observacoes" rows="4" placeholder="Descreva os sintomas do aparelho, arranhões, se veio com carregador..." required style="border-radius: 8px;"></textarea>
                </div>

                <hr class="my-4 text-muted opacity-20">
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border fw-bold px-4" style="border-radius: 8px;">Cancelar</a>
                    <button class="btn btn-success fw-bold px-5 shadow-sm" type="submit" style="border-radius: 8px;">
                        <i class="bi bi-save me-2"></i> Salvar Ordem de Serviço
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    // Converte o array PHP de equipamentos para um objeto Javascript
    const todosEquipamentos = <?php echo json_encode($array_equipamentos); ?>;

    function filtrarEquipamentos() {
        const idCliente = document.getElementById('id_cliente').value;
        const selectEquipamento = document.getElementById('id_equipamento');
        
        // Limpa as opções atuais
        selectEquipamento.innerHTML = '<option value="" disabled selected>Selecione o aparelho...</option>';
        
        // Filtra equipamentos do cliente selecionado
        const equipamentosFiltrados = todosEquipamentos.filter(eq => eq.id_cliente == idCliente);
        
        if (equipamentosFiltrados.length > 0) {
            equipamentosFiltrados.forEach(eq => {
                const option = document.createElement('option');
                option.value = eq.id_equipamento;
                option.textContent = `${eq.tipo} ${eq.marca} ${eq.modelo}`;
                selectEquipamento.appendChild(option);
            });
        } else {
            selectEquipamento.innerHTML = '<option value="" disabled selected>Nenhum aparelho cadastrado para este cliente.</option>';
        }
    }
</script>

<?php include '../includes/footer.php'; ?>