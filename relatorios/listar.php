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
    $titulo_relatorio = ' <i class="bi bi-cash-stack"></i> Faturamento Mensal & Receitas';
    $cor_borda = "border-primary";
} elseif ($tipo == 'ordens_servico') {
    $titulo_relatorio = '<i class="bi bi-file-earmark-word"></i> Ordens de Serviço por Período';
    $cor_borda = "border-warning";
} elseif ($tipo == 'pecas_baixo_estoque') {
    $titulo_relatorio = '<i class="bi bi-exclamation-circle"></i> Alerta de Peças com Baixo Estoque';
    $cor_borda = "border-danger";
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    @media print {
        .d-print-none { display: none !important; }
        body { background-color: #fff !important; color: #000 !important; padding: 0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        .table th { background-color: #f8f9fa !important; color: #000 !important; }
    }

    /* =========================================================
       MOBILE (até 768px): Ajuste dos botões e tabela vira "cards"
    ========================================================= */
    @media (max-width: 768px) {
        .botoes-mobile {
            flex-direction: column;
            align-items: stretch !important;
            width: 100%;
        }
        .botoes-mobile .btn, .botoes-mobile .input-group {
            width: 100%;
            max-width: 100% !important;
        }

        #tabelaRelatorio thead { display: none; }
        #tabelaRelatorio, #tabelaRelatorio tbody, #tabelaRelatorio tr, #tabelaRelatorio td { display: block; width: 100%; }
        #tabelaRelatorio tr { margin-bottom: 0.85rem; border: 1px solid #dee2e6; border-radius: 0.5rem; padding: 0.75rem 1rem; }
        #tabelaRelatorio td { border: none; padding: 0.3rem 0; text-align: left !important; }
        #tabelaRelatorio td::before {
            content: attr(data-label); display: block; font-size: 0.72rem; font-weight: 700;
            text-transform: uppercase; color: #6c757d; margin-bottom: 0.15rem;
        }
        #tabelaRelatorio tr.table-success td::before, #tabelaRelatorio tr.table-success {
            text-align: right !important;
        }
    }
</style>

<div class="container mt-4 mb-5">

    <div class="row align-items-center mb-4 d-print-none">
        
        <div class="col-12 col-xl-5 mb-3 mb-xl-0">
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
        
        <div class="col-12 col-xl-7">
            <div class="d-flex flex-wrap flex-md-nowrap gap-3 justify-content-xl-end align-items-center">
                
                <?php if (!empty($tipo)): ?>
                    <div class="input-group" style="max-width: 300px; min-width: 200px;">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" id="campoBusca" class="form-control" placeholder="Pesquisar no relatório..." autocomplete="off">
                    </div>
                <?php endif; ?>
                
                <div class="d-flex gap-2 text-nowrap botoes-mobile">
                    <a href="cadastrar.php" class="btn btn-secondary shadow-sm">
                        <i class="bi bi-arrow-left me-1"></i> Voltar
                    </a>
                </div>

            </div>
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
                <table id="tabelaRelatorio" class="table table-hover align-middle mb-0">

                    <?php if ($tipo == 'faturamento'): ?>
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Nº O.S.</th>
                                <th>Cliente</th>
                                <th>Equipamento</th>
                                <th>Funcionário</th>
                                <th>Valor Peças</th>
                                <th>Mão de Obra</th>
                                <th class="pe-4">Total Faturado</th>
                            </tr>
                        </thead>
                        <tbody id="corpoTabela">
                            <?php
                            // CONSULTA CORRIGIDA COM OS DADOS EXATOS DA SUA BASE DE DADOS MINTDECH (id_usuario_responsavel)
                            $sql = "SELECT o.id_orcamento, o.valor_pecas, o.valor_mao_obra, o.valor_total,
                                           os.id_os, os.data_entrada, 
                                           c.nome AS nome_cliente, 
                                           CONCAT(e.marca, ' ', e.modelo) AS equipamento,
                                           COALESCE(u_os.nome, u_orc.nome) AS nome_funcionario
                                    FROM orcamentos o
                                    JOIN ordens_servico os ON o.id_os = os.id_os
                                    LEFT JOIN clientes c ON os.id_cliente = c.id_cliente
                                    LEFT JOIN equipamentos e ON os.id_equipamento = e.id_equipamento
                                    LEFT JOIN usuarios u_os ON os.id_usuario_responsavel = u_os.id_usuario 
                                    LEFT JOIN usuarios u_orc ON o.usuario_responsavel = u_orc.id_usuario 
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
                                    $busca_row = mb_strtolower($row['id_os'] . ' ' . $row['nome_cliente'] . ' ' . $row['equipamento'] . ' ' . $row['nome_funcionario']);
                                    
                                    echo "<tr data-busca='" . htmlspecialchars($busca_row) . "'>";
                                    echo "<td class='ps-4 fw-bold text-dark' data-label='Nº O.S.'>OS #{$row['id_os']}</td>";
                                    echo "<td data-label='Cliente'>" . htmlspecialchars($row['nome_cliente'] ?? 'N/A') . "</td>";
                                    echo "<td data-label='Equipamento'>" . htmlspecialchars($row['equipamento'] ?? 'N/A') . "</td>";
                                    echo "<td data-label='Funcionário'>" . htmlspecialchars($row['nome_funcionario'] ?? 'Não Atribuído') . "</td>";
                                    echo "<td data-label='Valor Peças'>R$ " . number_format($row['valor_pecas'], 2, ',', '.') . "</td>";
                                    echo "<td data-label='Mão de Obra'>R$ " . number_format($row['valor_mao_obra'], 2, ',', '.') . "</td>";
                                    echo "<td class='pe-4 fw-bold text-success' data-label='Total Faturado'>R$ " . number_format($row['valor_total'], 2, ',', '.') . "</td>";
                                    echo "</tr>";
                                }
                                echo "<tr class='table-success fw-bold' data-busca=''>";
                                echo "<td colspan='6' class='text-end ps-4 py-3 fs-5 text-dark' data-label='Faturamento Total Bruto'>Faturamento Total Bruto:</td>";
                                echo "<td class='pe-4 py-3 fs-5 text-success' data-label=''>R$ " . number_format($total_geral, 2, ',', '.') . "</td>";
                                echo "</tr>";
                            } else {
                                echo "<tr><td colspan='7' class='text-center py-4 text-muted'>Nenhum faturamento de orçamento aprovado registado para este filtro.</td></tr>";
                            }
                            ?>
                            <tr id="semResultadoBusca" style="display:none;">
                                <td colspan="7" class="text-center py-4 text-muted"><i class="bi bi-search me-1"></i> Nenhum registo encontrado para a pesquisa.</td>
                            </tr>
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
                        <tbody id="corpoTabela">
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

                                    $busca_row = mb_strtolower($row['id_os'] . ' ' . $row['nome_cliente'] . ' ' . $row['equipamento'] . ' ' . $status_atual);

                                    echo "<tr data-busca='" . htmlspecialchars($busca_row) . "'>";
                                    echo "<td class='ps-4 fw-bold text-dark' data-label='Nº O.S.'>#{$row['id_os']}</td>";
                                    echo "<td data-label='Data Entrada'>{$data_pt}</td>";
                                    echo "<td class='fw-bold' data-label='Cliente'>{$row['nome_cliente']}</td>";
                                    echo "<td class='text-muted' data-label='Equipamento'>{$row['equipamento']}</td>";
                                    echo "<td class='pe-4 text-center' data-label='Status'><span class='badge {$badge_class} text-uppercase px-2 py-1' style='font-size:0.75rem;'>{$status_atual}</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-4 text-muted'>Nenhuma Ordem de Serviço encontrada.</td></tr>";
                            }
                            ?>
                            <tr id="semResultadoBusca" style="display:none;">
                                <td colspan="5" class="text-center py-4 text-muted"><i class="bi bi-search me-1"></i> Nenhuma O.S. encontrada para a pesquisa.</td>
                            </tr>
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
                        <tbody id="corpoTabela">
                            <?php
                            $sql = "SELECT * FROM pecas WHERE quantidade_disponivel <= nivel_minimo ORDER BY quantidade_disponivel ASC";
                            $result = mysqli_query($conn, $sql);

                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $busca_row = mb_strtolower($row['codigo'] . ' ' . $row['descricao']);
                                    echo "<tr data-busca='" . htmlspecialchars($busca_row) . "'>";
                                    echo "<td class='ps-4' data-label='Código da Peça'><span class='badge bg-dark fw-bold'>{$row['codigo']}</span></td>";
                                    echo "<td class='fw-bold text-dark' data-label='Descrição / Nome'>{$row['descricao']}</td>";
                                    echo "<td class='text-center text-danger fw-bold' data-label='Estoque Atual'><i class='bi bi-exclamation-triangle-fill me-1'></i>{$row['quantidade_disponivel']} un</td>";
                                    echo "<td class='pe-4 text-center text-muted' data-label='Nível de Alerta (Mínimo)'>{$row['nivel_minimo']} un</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center py-4 text-success fw-bold'><i class='bi bi-shield-check me-2 fs-5'></i>Excelente! Todas as peças estão com stock acima do mínimo.</td></tr>";
                            }
                            ?>
                            <tr id="semResultadoBusca" style="display:none;">
                                <td colspan="4" class="text-center py-4 text-muted"><i class="bi bi-search me-1"></i> Nenhuma peça encontrada.</td>
                            </tr>
                        </tbody>

                    <?php else: ?>
                        <tbody>
                            <tr>
                                <td class="text-center py-5 text-muted">
                                    <i class="bi bi-funnel fs-2 d-block mb-3 text-warning"></i>
                                    <span class="fw-bold d-block text-dark">Nenhum tipo de relatório foi selecionado.</span>
                                </td>
                            </tr>
                        </tbody>
                    <?php endif; ?>

                </table>
            </div>
        </div>
    </div> 

    <?php if (!empty($tipo)): ?>
        <div class="d-flex flex-wrap justify-content-end gap-2 mt-3 d-print-none botoes-mobile">
            <button onclick="exportarTabelaParaExcel('tabelaRelatorio', 'Relatorio_<?php echo ucfirst($tipo); ?>')" class="btn btn-success shadow-sm fw-bold">
                <i class="bi bi-file-earmark-excel me-1"></i> Exportar para Excel
            </button>
            <button onclick="window.print()" class="btn btn-primary shadow-sm fw-bold">
                <i class="bi bi-printer-fill me-1"></i> Imprimir / PDF
            </button>
        </div>
    <?php endif; ?>

    <div class="d-none d-print-block mt-4 text-center border-top pt-2">
        <p class="text-muted" style="font-size: 0.75rem;">Mindtech Gestão de Assistência &copy; <?php echo date('Y'); ?> — Relatório Gerencial de Uso Exclusivo</p>
    </div>

</div>

<script>
    // ================================================================
    // PESQUISA AO VIVO (Filtra a tabela a cada letra digitada)
    // ================================================================
    (function () {
        const campoBusca = document.getElementById('campoBusca');
        const linhas = document.querySelectorAll('#corpoTabela tr[data-busca]');
        const semResultado = document.getElementById('semResultadoBusca');
        if (!campoBusca) return;

        campoBusca.addEventListener('input', function () {
            const termo = this.value.toLowerCase().trim();
            let encontrados = 0;
            linhas.forEach(function (linha) {
                const bate = linha.dataset.busca.includes(termo);
                linha.style.display = bate ? '' : 'none';
                if (bate) encontrados++;
            });
            if (semResultado) {
                semResultado.style.display = (encontrados === 0 && termo !== '') ? '' : 'none';
            }
        });
    })();

    // ================================================================
    // EXPORTADOR PARA EXCEL (.XLS) - COM ESTILOS INLINE (MÁXIMA COMPATIBILIDADE)
    // ================================================================
    function exportarTabelaParaExcel(tableID, filename = '') {
        // Pega a tabela
        let table = document.getElementById(tableID);
        // Cria um clone para podermos limpar e alterar sem afetar o ecrã
        let clone = table.cloneNode(true);
        
        // 1. Remove a linha de "Nenhum registo encontrado"
        let semResultado = clone.querySelector('#semResultadoBusca');
        if(semResultado) semResultado.remove();
        
        // 2. Remove os ícones do Bootstrap
        let icons = clone.querySelectorAll('i');
        icons.forEach(icon => icon.remove());

        // 3. Pinta a linha de "Totais" (fazemos isso antes de apagar as classes)
        let celulasSucesso = clone.querySelectorAll('tr.table-success td');
        celulasSucesso.forEach(td => {
            td.style.backgroundColor = '#D9E1F2'; // Fundo azul claro
            td.style.fontWeight = 'bold';
        });

        // 4. Limpa todas as classes do Bootstrap e força o Estilo Inline
        let todasCelulas = clone.querySelectorAll('td, th');
        todasCelulas.forEach(celula => {
            // Remove a classe para o Excel não se confundir
            celula.removeAttribute('class'); 
            // Força a borda preta
            celula.style.border = '1px solid #000000';
            
            // Força o texto a ser preto em todas as células de dados
            if(celula.tagName.toLowerCase() === 'td') {
                celula.style.color = '#000000';
            }
        });

        // 5. Pinta o Cabeçalho
        let cabecalhos = clone.querySelectorAll('th');
        cabecalhos.forEach(th => {
            th.style.backgroundColor = '#4F81BD'; // Fundo Azul Escuro
            th.style.color = '#FFFFFF';           // Letra Branca
            th.style.fontWeight = 'bold';
            th.style.textAlign = 'center';
        });

        // Gera o HTML da tabela já tratada
        let tableHTML = clone.outerHTML;
        
        // Monta o ficheiro XML do Excel
        let template = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
        <meta charset="UTF-8">
        <style>
            table { border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; }
            /* O mso-number-format:"\\@" FORÇA O EXCEL A LER COMO TEXTO, IMPEDINDO QUE OS NÚMEROS SUMAM */
            td { mso-number-format:"\\@"; white-space: nowrap; padding: 6px; vertical-align: middle; }
            th { padding: 6px; vertical-align: middle; }
        </style>
        </head>
        <body>${tableHTML}</body>
        </html>`;
        
        // Codifica em Base64
        let dataType = 'data:application/vnd.ms-excel;base64,';
        let base64data = btoa(unescape(encodeURIComponent(template)));
        
        // Cria o nome do ficheiro
        let dataAtual = new Date().toISOString().split('T')[0];
        filename = filename ? filename + '_' + dataAtual + '.xls' : 'Relatorio_' + dataAtual + '.xls';
        
        // Dispara o download automático
        let downloadLink = document.createElement("a");
        document.body.appendChild(downloadLink);
        downloadLink.href = dataType + base64data;
        downloadLink.download = filename;
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
</script>

<?php include '../includes/footer.php'; ?>