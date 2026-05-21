<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

// Pega o ID da OS vindo da URL (?id=X)
$id_os = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_os <= 0) {
    header("Location: listar.php");
    exit;
}

// Consulta robusta para trazer os dados da OS, do Cliente, do Equipamento e do Técnico Responsável
$sql = "SELECT os.*, 
               c.nome AS nome_cliente, c.cpf, c.telefone, c.endereco,
               e.tipo AS eq_tipo, e.marca AS eq_marca, e.modelo AS eq_modelo, e.numero_serie,
               u.nome AS nome_tecnico
        FROM ordens_servico os
        JOIN clientes c ON os.id_cliente = c.id_cliente
        JOIN equipamentos e ON os.id_equipamento = e.id_equipamento
        LEFT JOIN usuarios u ON os.id_usuario_responsavel = u.id_usuario
        WHERE os.id_os = $id_os";

$result = mysqli_query($conn, $sql);
$os = mysqli_fetch_assoc($result);

// Se não encontrar a OS no banco, volta para a lista
if (!$os) {
    header("Location: listar.php");
    exit;
}

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-earmark-text"></i> Detalhes da Ordem de Serviço #<?php echo $os['id_os']; ?></h2>
        <div>
            <a href="listar.php" class="btn btn-secondary me-2">Voltar para Lista</a>
            <a href="../orcamentos/cadastrar.php?id=<?php echo $os['id_os']; ?>" class="btn btn-primary">Gerar/Ver Orçamento</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white fw-bold">Dados do Cliente</div>
                <div class="card-body">
                    <p class="mb-1"><strong>Nome:</strong> <?php echo htmlspecialchars($os['nome_cliente']); ?></p>
                    <p class="mb-1"><strong>CPF:</strong> <?php echo htmlspecialchars($os['cpf']); ?></p>
                    <p class="mb-1"><strong>Telefone:</strong> <?php echo htmlspecialchars($os['telefone']); ?></p>
                    <p class="mb-0"><strong>Endereço:</strong> <?php echo htmlspecialchars($os['endereco']); ?></p>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold">Dados do Equipamento</div>
                <div class="card-body">
                    <p class="mb-1"><strong>Tipo:</strong> <?php echo htmlspecialchars($os['eq_tipo']); ?></p>
                    <p class="mb-1"><strong>Marca/Modelo:</strong> <?php echo htmlspecialchars($os['eq_marca'] . ' ' . $os['eq_modelo']); ?></p>
                    <p class="mb-0"><strong>N° de Série:</strong> <?php echo htmlspecialchars($os['numero_serie'] ? $os['numero_serie'] : 'Não informado'); ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-dark text-white fw-bold">Acompanhamento Técnico</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fw-bold text-muted d-block mb-1">Status Atual</label>
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                            <?php echo $os['status'] == 'EM_ANDAMENTO' ? 'Em Andamento' : $os['status']; ?>
                        </span>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold text-muted d-block mb-1">Técnico Responsável</label>
                        <p class="fs-5 fw-semibold text-dark">
                            <?php echo $os['nome_tecnico'] ? htmlspecialchars($os['nome_tecnico']) : '<em>Nenhum técnico alocado</em>'; ?>
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold text-muted d-block mb-1">Data de Entrada</label>
                        <p class="text-secondary"><?php echo date('d/m/Y H:i', strtotime($os['data_entrada'])); ?></p>
                    </div>

                    <div class="mb-0">
                        <label class="fw-bold text-muted d-block mb-1">Observações / Relato do Defeito</label>
                        <div class="p-3 bg-light rounded border text-secondary" style="white-space: pre-wrap; min-height: 120px;"><?php echo htmlspecialchars($os['observacoes']); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>