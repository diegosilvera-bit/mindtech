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

// Captura o ID do usuário logado para auditoria (Tenta os padrões mais comuns de sessão)
$id_usuario_logado = $_SESSION['usuario']['id_usuario'] ?? $_SESSION['id_usuario'] ?? 'NULL';
if (is_numeric($id_usuario_logado)) {
    $id_usuario_logado = (int)$id_usuario_logado;
} else {
    $id_usuario_logado = 'NULL';
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $id_equipamento = (int)$_POST['id_equipamento'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $observacoes = mysqli_real_escape_string($conn, trim($_POST['observacoes']));

    if ($id_equipamento <= 0) {
        $mensagem = "Por favor, selecione um equipamento válido.";
    } elseif (empty($observacoes)) {
        $mensagem = "Por favor, descreva o problema relatado no campo de observações.";
    } else {
        // 1. Descobre automaticamente quem é o cliente dono do aparelho
        $query_dono = "SELECT id_cliente FROM equipamentos WHERE id_equipamento = $id_equipamento AND ativo = 1";
        $res_dono = mysqli_query($conn, $query_dono);
        
        if ($res_dono && mysqli_num_rows($res_dono) > 0) {
            $dono = mysqli_fetch_assoc($res_dono);
            $id_cliente = (int)$dono['id_cliente'];

            // 2. Insere a O.S. vinculando Cliente, Equipamento e o Usuário que abriu
            $sql_insert = "INSERT INTO ordens_servico (id_equipamento, id_cliente, id_usuario_responsavel, status, observacoes, data_entrada) 
                           VALUES ($id_equipamento, $id_cliente, $id_usuario_logado, '$status', '$observacoes', NOW())";

            if (mysqli_query($conn, $sql_insert)) {
                $id_gerado = mysqli_insert_id($conn);
                
                // 3. Aplica o padrão PRG: Redireciona imediatamente para evitar duplicidade no F5
                header("Location: visualizar.php?id=" . $id_gerado . "&cadastro=sucesso");
                exit;
            } else {
                $mensagem = "Erro ao registrar a Ordem de Serviço: " . mysqli_error($conn);
            }
        } else {
            $mensagem = "Equipamento não encontrado ou inativo no banco de dados.";
        }
    }
}

// Busca equipamentos ativos e os nomes dos donos para o Select
$sql_equipamentos = "SELECT e.id_equipamento, e.tipo, e.marca, e.modelo, c.nome AS nome_cliente 
                     FROM equipamentos e 
                     JOIN clientes c ON e.id_cliente = c.id_cliente 
                     WHERE e.ativo = 1 AND c.ativo = 1 
                     ORDER BY c.nome ASC, e.modelo ASC";
$res_equipamentos = mysqli_query($conn, $sql_equipamentos);

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
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Selecione o Aparelho / Cliente *</label>
                    <select class="form-select" name="id_equipamento" required style="border-radius: 8px;">
                        <option value="" disabled selected>Pesquise ou selecione na lista...</option>
                        <?php 
                        if ($res_equipamentos && mysqli_num_rows($res_equipamentos) > 0) {
                            while ($eq = mysqli_fetch_assoc($res_equipamentos)) {
                                $descricao_aparelho = sprintf(
                                    "%s - %s %s (%s)", 
                                    htmlspecialchars($eq['nome_cliente']),
                                    htmlspecialchars($eq['tipo']),
                                    htmlspecialchars($eq['marca']),
                                    htmlspecialchars($eq['modelo'])
                                );
                                echo "<option value='{$eq['id_equipamento']}'>{$descricao_aparelho}</option>";
                            }
                        } else {
                            echo "<option value='' disabled>Nenhum equipamento ativo cadastrado.</option>";
                        }
                        ?>
                    </select>
                    <small class="text-muted">A O.S. será vinculada automaticamente ao cliente dono deste aparelho.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Status Inicial</label>
                    <select class="form-select" name="status" style="border-radius: 8px;">
                        <option value="EM_ANALISE" selected>1. Em Análise (Aguardando Orçamento)</option>
                        <option value="EM_REPARO">2. Em Reparo (Laboratório)</option>
                        <option value="AGUARDANDO_PECA">3. Aguardando Peça</option>
                        <option value="FINALIZADO">4. Finalizado</option>
                    </select>
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

<?php include '../includes/footer.php'; ?>