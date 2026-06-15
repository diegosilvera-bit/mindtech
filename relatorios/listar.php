<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

include '../config/conexao.php'; 

$tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
$inicio = isset($_GET['inicio']) ? trim($_GET['inicio']) : '';
$fim = isset($_GET['fim']) ? trim($_GET['fim']) : '';

$titulo_relatorio = "Relatório Geral";
$cor_borda = "border-secondary";

if ($tipo == 'faturamento') { 
    $titulo_relatorio = "Faturamento Mensal & Receitas"; 
    $cor_borda = "border-primary";
} elseif ($tipo == 'ordens_servico') { 
    $titulo_relatorio = "Ordens de Serviço por Período"; 
    $cor_borda = "border-warning";
} elseif ($tipo == 'pecas_baixo_estoque') { 
    $titulo_relatorio = "Alerta de Peças com Baixo Estoque"; 
    $cor_borda = "border-danger";
}

include '../includes/header.php'; 
?>

<style>
    @media print {
        .d-print-none { display: none !important; }
        body { background-color: #fff !important; color: #000 !important; padding: 0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        .table th { background-color: #f8f9fa !important; color: #000 !important; }
    }
</style>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><?php echo $titulo_relatorio; ?></h1>
            <?php if (!empty($inicio) && !empty($fim)): ?>
                <span class="badge bg-light text-dark border">
                    <i class="bi bi-calendar-range me-1 text-muted"></i> 
                    Período: <strong><?php echo date('d/m/Y', strtotime($inicio)); ?></strong> até <strong><?php echo date('d/m/Y', strtotime($fim)); ?></strong>
                </span>
            <?php else: ?>
                <span class="badge bg-light text-dark border">Histórico Completo</span>
            <?php endif; ?>
        </div>
        <div>
            <a href="cadastrar.php" class="btn btn-sm btn-light border fw-bold me-2 px-3">
                <i class="bi bi-arrow-left me-1"></i> Voltar aos Filtros
            </a>
            <?php if (!empty($tipo)): ?>
                <button onclick="window.print()" class="btn btn-sm btn-primary fw-bold px-4 shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> Imprimir / Salvar PDF
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-none d-print-block mb-4 border-bottom pb-3">
        <div class="row align-items-center">
            <div class="col-8">
                <h2 class="fw-bold mb-1 text-dark">Mindtech — Assistência Técnica</h2>
                <p class="text-muted small mb-0">Relatório Gerencial Interno emitido em: <?php echo date('d/m/Y H:i'); ?></p>
            </div>
            <div class="col-4 text-end">
                <h4 class="fw-bold text-uppercase text-secondary mb-0" style="font-size: 1.1rem;"><?php echo $titulo_relatorio; ?></h4>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 border-start border-4 <?php echo $cor_borda; ?>">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    
                    <?php if ($tipo == 'faturamento'): ?>
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Cód. Orçamento</th>
                                <th>Nº O.S.</th>
                                <th>Valor Peças</th>
                                <th>Mão de Obra</th>
                                <th class="pe-4">Valor Total Recebido</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT o.*, os.data_entrada 
                                    FROM orcamentos o 
                                    JOIN ordens_servico os ON o.id_os = os.id_os 
                                    WHERE o.aprovado = 1";
                            
                            if (!empty($inicio) && !empty($fim)) {
                                $sql .= " AND DATE(os.data_entrada) BETWEEN '$inicio' AND '$fim'";
                            }
                            $sql .= " ORDER BY o.id_orcamento DESC";
                            
                            $result = mysqli_query($conn, $sql);
                            $total_geral = 0;

                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $total_geral += $row['valor_total'];
                                    echo "<tr>";
                                    echo "<td class='ps-4 fw-bold text-muted'>#{$row['id_orcamento']}</td>";
                                    echo "<td><span class='badge bg-secondary-subtle text-secondary border fw-bold'>OS #{$row['id_os']}</span></td>";
                                    echo "<td>R$ " . number_format($row['valor_pecas'], 2, ',', '.') . "</td>";
                                    echo "<td>R$ " . number_format($row['valor_mao_obra'], 2, ',', '.') . "</td>";
                                    echo "<td class='pe-4 fw-bold text-success'>R$ " . number_format($row['valor_total'], 2, ',', '.') . "</td>";
                                    echo "</tr>";
                                }
                                echo "<tr class='table-success fw-bold'>";
                                echo "<td colspan='4' class='text-end ps-4 py-3 fs-5 text-dark'>Faturamento Total Bruto:</td>";
                                echo "<td class='pe-4 py-3 fs-5 text-success'>R$ " . number_format($total_geral, 2, ',', '.') . "</td>";
                                echo "</tr>";
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-4 text-muted'>Nenhum faturamento de orçamento aprovado registrado para este filtro.<br><small class='text-danger'>Nota: Certifique-se de que a O.S. possui um orçamento salvo e marcado como 'Aprovado'.</small></td></tr>";
                            }
                            ?>
                        </tbody>

                    <?php elseif ($tipo == 'ordens_servico'): ?>
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Nº O.S.</th>
                                <th>Data Entrada</th>
                                <th>Cliente</th>
                                <th>Equipamento</th>
                                <th class="pe-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT os.*, c.nome AS nome_cliente, CONCAT(e.marca, ' ', e.modelo) AS equipamento 
                                    FROM ordens_servico os
                                    JOIN clientes c ON os.id_cliente = c.id_cliente
                                    JOIN equipamentos e ON os.id_equipamento = e.id_equipamento 
                                    WHERE 1=1";
                            
                            if (!empty($inicio) && !empty($fim)) {
                                $sql .= " AND DATE(os.data_entrada) BETWEEN '$inicio' AND '$fim'";
                            }
                            $sql .= " ORDER BY os.id_os DESC";
                            
                            $result = mysqli_query($conn, $sql);

                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $data_pt = date('d/m/Y', strtotime($row['data_entrada']));
                                    $status_atual = trim($row['status']);
                                    $badge_class = "bg-secondary";
                                    
                                    if(strpos(strtolower($status_atual), 'analise') !== false || strpos(strtolower($status_atual), 'análise') !== false) $badge_class = "bg-primary";
                                    if(strpos(strtolower($status_atual), 'reparo') !== false) $badge_class = "bg-warning text-dark";
                                    if(strpos(strtolower($status_atual), 'finalizado') !== false) $badge_class = "bg-success";
                                    if(strpos(strtolower($status_atual), 'peca') !== false || strpos(strtolower($status_atual), 'peça') !== false) $badge_class = "bg-danger";

                                    echo "<tr>";
                                    echo "<td class='ps-4 fw-bold text-dark'>#{$row['id_os']}</td>";
                                    echo "<td>{$data_pt}</td>";
                                    echo "<td class='fw-bold'>{$row['nome_cliente']}</td>";
                                    echo "<td class='text-muted'>{$row['equipamento']}</td>";
                                    echo "<td class='pe-4 text-center'><span class='badge {$badge_class} text-uppercase px-2 py-1' style='font-size:0.75rem;'>{$status_atual}</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-4 text-muted'>Nenhuma Ordem de Serviço encontrada para o período solicitado.</td></tr>";
                            }
                            ?>
                        </tbody>

                    <?php elseif ($tipo == 'pecas_baixo_estoque'): ?>
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Código da Peça</th>
                                <th>Descrição / Nome</th>
                                <th class="text-center">Estoque Atual</th>
                                <th class="pe-4 text-center">Nível de Alerta (Mínimo)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM pecas WHERE quantidade_disponivel <= nivel_minimo ORDER BY quantidade_disponivel ASC";
                            $result = mysqli_query($conn, $sql);

                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($row)) {
                                    echo "<tr>";
                                    echo "<td class='ps-4'><span class='badge bg-dark fw-bold'>{$row['codigo']}</span></td>";
                                    echo "<td class='fw-bold text-dark'>{$row['descricao']}</td>";
                                    echo "<td class='text-center text-danger fw-bold'><i class='bi bi-exclamation-triangle-fill me-1'></i>{$row['quantidade_disponivel']} un</td>";
                                    echo "<td class='pe-4 text-center text-muted'>{$row['nivel_minimo']} un</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center py-4 text-success fw-bold'><i class='bi bi-shield-check me-2 fs-5'></i>Excelente! Todas as peças encontram-se acima do nível mínimo.</td></tr>";
                            }
                            ?>
                        </tbody>
                    
                    <?php else: ?>
                        <tbody>
                            <tr>
                                <td class="text-center py-5 text-muted">
                                    <i class="bi bi-funnel fs-2 d-block mb-3 text-warning"></i>
                                    <span class="fw-bold d-block text-dark">Nenhum tipo de relatório foi selecionado.</span>
                                    Por favor, volte para a tela de filtros e escolha uma opção válida.
                                    <div class="mt-3">
                                        <a href="cadastrar.php" class="btn btn-sm btn-success fw-bold px-4">Ir para Filtros</a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    <?php endif; ?>

                </table>
            </div>
        </div>
    </div>
    
    <div class="d-none d-print-block mt-4 text-center border-top pt-2">
        <p class="text-muted" style="font-size: 0.75rem;">Mindtech Gestão de Assistência &copy; <?php echo date('Y'); ?> — Relatório Gerencial de Uso Exclusivo</p>
    </div>

</div>

<?php include '../includes/footer.php'; ?>