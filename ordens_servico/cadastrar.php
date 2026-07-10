<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>

<style>
    .ts-dropdown .highlight {
        background: transparent !important;
        color: inherit !important;
        text-decoration: none !important;
        font-weight: bold !important;
    }
</style>

<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

verificarAcesso(['G', 'A', 'T']);
include '../config/conexao.php'; 

$mensagem = ''; 
$tipo_alerta = 'danger';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $id_cliente = (int)$_POST['id_cliente'];
    $id_equipamento = (int)$_POST['id_equipamento'];
    $id_tecnico = !empty($_POST['id_tecnico']) ? (int)$_POST['id_tecnico'] : 'NULL';
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $observacoes = mysqli_real_escape_string($conn, trim($_POST['observacoes']));
    
    // Tratamento da Data de Entrega
    $data_prevista = !empty($_POST['data_prevista_entrega']) ? "'" . mysqli_real_escape_string($conn, $_POST['data_prevista_entrega']) . " 23:59:59'" : "NULL";

    if ($id_cliente <= 0) {
        $mensagem = "Por favor, selecione um cliente.";
    } elseif ($id_equipamento <= 0) {
        $mensagem = "Por favor, selecione um equipamento válido.";
    } elseif (empty($observacoes)) {
        $mensagem = "Por favor, descreva o problema relatado no campo de observações.";
    } else {
        $sql_insert = "INSERT INTO ordens_servico (id_equipamento, id_cliente, id_usuario_responsavel, status, observacoes, data_prevista_entrega, data_entrada) 
                       VALUES ($id_equipamento, $id_cliente, $id_tecnico, '$status', '$observacoes', $data_prevista, NOW())";

        if (mysqli_query($conn, $sql_insert)) {
            $id_gerado = mysqli_insert_id($conn);
            header("Location: visualizar.php?id=" . $id_gerado . "&cadastro=sucesso");
            exit;
        } else {
            $mensagem = "Erro ao registar a Ordem de Serviço: " . mysqli_error($conn);
        }
    }
}

$sql_clientes = "SELECT id_cliente, nome, cpf FROM clientes ORDER BY nome ASC";
$res_clientes = mysqli_query($conn, $sql_clientes);

$sql_equipamentos = "SELECT id_equipamento, id_cliente, tipo, marca, modelo FROM equipamentos ORDER BY marca ASC";
$res_equipamentos = mysqli_query($conn, $sql_equipamentos);

$array_equipamentos = [];
if ($res_equipamentos && mysqli_num_rows($res_equipamentos) > 0) {
    while($row = mysqli_fetch_assoc($res_equipamentos)) {
        $array_equipamentos[] = $row;
    }
}

$sql_tecnicos = "SELECT id_usuario, nome FROM usuarios WHERE perfil IN ('T', 'G') ORDER BY nome ASC";
$res_tecnicos = mysqli_query($conn, $sql_tecnicos);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="bi bi-tools text-success-white me-2"></i>Abrir Ordem de Serviço</h1>
        </div>
        <a href="listar.php" class="btn btn-secondary px-3">
             Voltar para a Lista
        </a>
    </div>

    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $mensagem; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 border-start border-4 border-success">
        <div class="card-body p-4">
            <form method="POST" action="cadastrar.php">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-bold">1. Selecione o Cliente *</label>
                        <select class="form-select" name="id_cliente" id="id_cliente" required style="border-radius: 8px;">
                            <option value="" disabled selected>Pesquise ou selecione na lista...</option>
                            <?php 
                            if ($res_clientes && mysqli_num_rows($res_clientes) > 0) {
                                while ($cli = mysqli_fetch_assoc($res_clientes)) {
                                    echo "<option value='{$cli['id_cliente']}'>" . htmlspecialchars($cli['nome']) . " (CPF: {$cli['cpf']})</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-bold">2. Selecione o Aparelho *</label>
                        <select class="form-select" name="id_equipamento" id="id_equipamento" required style="border-radius: 8px;">
                            <option value="" disabled selected>Primeiro, selecione um cliente...</option>
                        </select>
                    </div>
                </div>

                <div class="row bg-light p-3 rounded mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Técnico Responsável</label>
                        <select class="form-select" name="id_tecnico" id="id_tecnico" style="border-radius: 8px;">
                            <option value="" disabled selected>Não alocado...</option>
                            <?php 
                            if ($res_tecnicos && mysqli_num_rows($res_tecnicos) > 0) {
                                while ($tec = mysqli_fetch_assoc($res_tecnicos)) {
                                    echo "<option value='{$tec['id_usuario']}'>" . htmlspecialchars($tec['nome']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Status Inicial</label>
                        <select class="form-select" name="status" style="border-radius: 8px;">
                            <option value="EM_ANALISE" selected>Em Análise (Orçamento)</option>
                            <option value="EM_REPARO">Em Reparo (Laboratório)</option>
                            <option value="AGUARDANDO_PECA">Aguardando Peça</option>
                            <option value="FINALIZADO">Finalizado</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-primary-black">Previsão de Entrega</label>
                        <input type="date" class="form-control border-primary" name="data_prevista_entrega" style="border-radius: 8px;">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Problema Relatado / Observações Iniciais *</label>
                    <textarea class="form-control" name="observacoes" rows="4" required style="border-radius: 8px;"></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border fw-bold px-4">Cancelar</a>
                    <button class="btn btn-success fw-bold px-5 shadow-sm" type="submit">
                        <i class="bi bi-save me-2"></i> Gravar Ordem de Serviço
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Converte o array PHP de equipamentos para um objeto Javascript
    const todosEquipamentos = <?php echo json_encode($array_equipamentos); ?>;

    // Regra de busca estrita: o item deve obrigatoriamente INICIAR com o termo pesquisado
    function funcaoBuscaEstrita(search) {
        const query = search.trim().toLowerCase();
        return function(item) {
            if (!query) return 1;
            return item.text.toLowerCase().startsWith(query) ? 1 : 0;
        };
    }

    // 1. Inicializa o Buscador de Clientes
    const buscadorCliente = new TomSelect("#id_cliente", {
        create: false,
        placeholder: "Pesquise ou selecione na lista...",
        allowEmptyOption: false,
        score: funcaoBuscaEstrita
    });

    // 2. Inicializa o Buscador de Equipamentos
    const buscadorEquipamento = new TomSelect("#id_equipamento", {
        create: false,
        placeholder: "Primeiro, selecione um cliente...",
        allowEmptyOption: false,
        score: funcaoBuscaEstrita
    });

    // 3. Inicializa o Buscador de Técnicos
    const buscadorTecnico = new TomSelect("#id_tecnico", {
        create: false,
        placeholder: "Não alocado...", // Texto configurado como placeholder
        allowEmptyOption: false, // Desativa a seleção da opção em branco
        score: funcaoBuscaEstrita
    });

    // Escuta a mudança do Cliente para disparar a filtragem de aparelhos
    buscadorCliente.on('change', function() {
        filtrarEquipamentos();
    });

    function filtrarEquipamentos() {
        // Pega o valor atual selecionado no Tom Select do cliente
        const idCliente = document.getElementById('id_cliente').value;
        
        // Limpa as seleções e opções anteriores do Tom Select do aparelho
        buscadorEquipamento.clear();
        buscadorEquipamento.clearOptions();
        
        // Filtra os equipamentos vinculados ao ID do cliente selecionado
        const equipamentosFiltrados = todosEquipamentos.filter(eq => eq.id_cliente == idCliente);
        
        if (equipamentosFiltrados.length > 0) {
            equipamentosFiltrados.forEach(eq => {
                buscadorEquipamento.addOption({
                    value: eq.id_equipamento,
                    text: `${eq.tipo} ${eq.marca} ${eq.modelo}`
                });
            });
        } else {
            // Se o cliente não possuir aparelhos ativos cadastrados
            buscadorEquipamento.addOption({
                value: "",
                text: "Nenhum aparelho registado."
            });
        }
        
        // Solicita ao componente atualizar a renderização na tela
        buscadorEquipamento.refreshOptions(false);
    }
</script>

<?php include '../includes/footer.php'; ?>