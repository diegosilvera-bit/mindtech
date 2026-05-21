<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

// Pega os parâmetros passados pelo formulário via URL (GET)
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$inicio = isset($_GET['inicio']) ? $_GET['inicio'] : '';
$fim = isset($_GET['fim']) ? $_GET['fim'] : '';

// Define o título da página baseado na escolha
$titulo_relatorio = "Relatório";
if ($tipo == 'faturamento') { $titulo_relatorio = "Faturamento Mensal"; }
if ($tipo == 'ordens_servico') { $titulo_relatorio = "Ordens de Serviço por Período"; }
if ($tipo == 'pecas_baixo_estoque') { $titulo_relatorio = "Peças com Baixo Estoque"; }

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <h1><?php echo $titulo_relatorio; ?></h1>
        <div>
            <a href="cadastrar.php" class="btn btn-secondary me-2">Voltar aos Filtros</a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer me-1"></i> Imprimir
            </button>
        </div>
    </div>

    <?php if ($tipo == '') { ?>
        <div class="alert alert-warning shadow-sm">
            Nenhum relatório selecionado. Por favor, volte e escolha um tipo de relatório.
        </div>
    <?php } else { ?>

        <div class="card shadow-sm border-0 border-start border-4 border-success">
            <div class="card-body p-0">
                <table class="table table-hover table-striped align-middle mb-0">
                    
                    <?php 
                    // =========================================================================
                    // 1. RELATÓRIO DE FATURAMENTO (Usa a view vw_faturamento_mensal do seu banco)
                    // =========================================================================
                    if ($tipo == 'faturamento') { 
                    ?>
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">Mês / Ano</th>
                                <th>Total Faturado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM vw_faturamento_mensal ORDER BY ano_mes DESC";
                            $result = mysqli_query($conn, $sql);
                            $total_geral = 0;

                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $total_geral += $row['fatura_total'];
                                    
                                    // Pega o "2026-05" e transforma em "05/2026"
                                    $partes_data = explode('-', $row['ano_mes']);
                                    $mes_ano = $partes_data[1] . '/' . $partes_data[0];

                                    echo "<tr>";
                                    echo "<td class='ps-3 fw-bold'>{$mes_ano}</td>";
                                    echo "<td class='text-success fw-bold'>R$ " . number_format($row['fatura_total'], 2, ',', '.') . "</td>";
                                    echo "</tr>";
                                }
                                // Mostra a linha final com a soma de tudo
                                echo "<tr class='table-success'>";
                                echo "<td class='ps-3 fw-bold text-end'>TOTAL GERAL:</td>";
                                echo "<td class='fw-bold text-success'>R$ " . number_format($total_geral, 2, ',', '.') . "</td>";
                                echo "</tr>";
                            } else {
                                echo "<tr><td colspan='2' class='text-center py-4'>Nenhum faturamento encontrado.</td></tr>";
                            }
                            ?>
                        </tbody>

                    <?php 
                    // =========================================================================
                    // 2. RELATÓRIO DE ORDENS DE SERVIÇO (Filtra por Data)
                    // =========================================================================
                    } elseif ($tipo == 'ordens_servico') { 
                    ?>
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">Nº OS</th>
                                <th>Data de Entrada</th>
                                <th>ID Cliente</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM ordens_servico WHERE 1=1";
                            
                            // Adiciona as datas à pesquisa se o utilizador as tiver preenchido
                            if ($inicio != '') { $sql .= " AND data_entrada >= '$inicio 00:00:00'"; }
                            if ($fim != '') { $sql .= " AND data_entrada <= '$fim 23:59:59'"; }
                            
                            $sql .= " ORDER BY data_entrada DESC";
                            $result = mysqli_query($conn, $sql);

                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $data_br = date('d/m/Y', strtotime($row['data_entrada']));
                                    $status_limpo = str_replace('_', ' ', $row['status']);

                                    echo "<tr>";
                                    echo "<td class='ps-3 fw-bold'>#{$row['id_os']}</td>";
                                    echo "<td>{$data_br}</td>";
                                    echo "<td>Cliente {$row['id_cliente']}</td>";
                                    echo "<td>{$status_limpo}</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center py-4'>Nenhuma OS encontrada neste período.</td></tr>";
                            }
                            ?>
                        </tbody>

                    <?php 
                    // =========================================================================
                    // 3. RELATÓRIO DE PEÇAS COM ESTOQUE BAIXO
                    // =========================================================================
                    } elseif ($tipo == 'pecas_baixo_estoque') { 
                    ?>
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">Código</th>
                                <th>Descrição da Peça</th>
                                <th>Quantidade Atual</th>
                                <th>Nível Mínimo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Busca apenas peças que estão com quantidade menor ou igual ao limite mínimo
                            $sql = "SELECT * FROM pecas WHERE quantidade_disponivel <= nivel_minimo ORDER BY quantidade_disponivel ASC";
                            $result = mysqli_query($conn, $sql);

                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td class='ps-3'><span class='badge bg-secondary'>{$row['codigo']}</span></td>";
                                    echo "<td>{$row['descricao']}</td>";
                                    echo "<td class='text-danger fw-bold'>{$row['quantidade_disponivel']} un</td>";
                                    echo "<td>{$row['nivel_minimo']} un</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center py-4 text-success fw-bold'>Nenhuma peça em baixo estoque no momento!</td></tr>";
                            }
                            ?>
                        </tbody>
                    <?php } ?>

                </table>
            </div>
        </div>

    <?php } ?>

</div>

<?php include '../includes/footer.php'; ?>