<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<!-- Biblioteca do Chart.js para o Gráfico de Rendas -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 
require_once '../config/conexao.php'; 

// Define o fuso horário correto
date_default_timezone_set('America/Sao_Paulo');
$perfil = $_SESSION['usuario']['perfil'] ?? '';

// =========================================================================
// LÓGICA DO GRÁFICO: Contar O.S. por Status
// =========================================================================
$contagem = [
    'analise' => 0,
    'reparo' => 0,
    'finalizado' => 0,
    'aguardando' => 0
];

$sql_status = "SELECT status, COUNT(*) as total FROM ordens_servico GROUP BY status";
$resultado_status = mysqli_query($conn, $sql_status);

if ($resultado_status && mysqli_num_rows($resultado_status) > 0) {
    while ($row = mysqli_fetch_assoc($resultado_status)) {
        $status = strtolower(trim($row['status']));
        
        if (strpos($status, 'análise') !== false || strpos($status, 'analise') !== false || strpos($status, 'aberto') !== false) {
            $contagem['analise'] += $row['total'];
        } elseif (strpos($status, 'reparo') !== false || strpos($status, 'andamento') !== false) {
            $contagem['reparo'] += $row['total'];
        } elseif (strpos($status, 'finalizado') !== false || strpos($status, 'concluído') !== false || strpos($status, 'concluido') !== false) {
            $contagem['finalizado'] += $row['total'];
        } elseif (strpos($status, 'aguardando') !== false || strpos($status, 'peca') !== false || strpos($status, 'peça') !== false) {
            $contagem['aguardando'] += $row['total'];
        }
    }
}

// =========================================================================
// BUSCA DA TABELA DE ALERTA: O.S. com status 'AGUARDANDO_PECA'
// =========================================================================
$sql_aguardando_peca = "SELECT os.id_os, os.data_entrada, 
                               c.nome AS nome_cliente, 
                               CONCAT(e.marca, ' ', e.modelo) AS equipamento
                        FROM ordens_servico os
                        INNER JOIN clientes c ON os.id_cliente = c.id_cliente
                        INNER JOIN equipamentos e ON os.id_equipamento = e.id_equipamento
                        WHERE os.status = 'AGUARDANDO_PECA' 
                        ORDER BY os.id_os ASC"; 

$res_aguardando = mysqli_query($conn, $sql_aguardando_peca);

// =========================================================================
// LÓGICA DO GRÁFICO DE RENDAS (SEMANAL, MENSAL, ANUAL) - O.S FINALIZADAS
// =========================================================================
$dados_grafico = [
    'semanal' => ['labels' => [], 'valores' => []],
    'mensal'  => ['labels' => [], 'valores' => []],
    'anual'   => ['labels' => [], 'valores' => []]
];

// 1. Busca Semanal (Últimas 12 Semanas)
$sql_semanal = "SELECT YEARWEEK(COALESCE(o.data_saida, o.atualizado_em), 1) AS periodo, 
                       SUM(COALESCE(orcs.valor_total, 0)) AS total 
                FROM ordens_servico o 
                LEFT JOIN orcamentos orcs ON orcs.id_os = o.id_os 
                WHERE o.status = 'FINALIZADO' 
                GROUP BY periodo ORDER BY periodo ASC LIMIT 12";
$res_semanal = mysqli_query($conn, $sql_semanal);
if ($res_semanal) {
    while ($row = mysqli_fetch_assoc($res_semanal)) {
        $ano = substr($row['periodo'], 0, 4);
        $sem = substr($row['periodo'], 4, 2);
        $dados_grafico['semanal']['labels'][] = "Sem $sem/$ano";
        $dados_grafico['semanal']['valores'][] = (float) $row['total'];
    }
}

// 2. Busca Mensal (Últimos 12 Meses)
$sql_mensal = "SELECT DATE_FORMAT(COALESCE(o.data_saida, o.atualizado_em), '%Y-%m') AS periodo, 
                      SUM(COALESCE(orcs.valor_total, 0)) AS total 
               FROM ordens_servico o 
               LEFT JOIN orcamentos orcs ON orcs.id_os = o.id_os 
               WHERE o.status = 'FINALIZADO' 
               GROUP BY periodo ORDER BY periodo ASC LIMIT 12";
$res_mensal = mysqli_query($conn, $sql_mensal);
if ($res_mensal) {
    while ($row = mysqli_fetch_assoc($res_mensal)) {
        $data_obj = DateTime::createFromFormat('Y-m', $row['periodo']);
        $dados_grafico['mensal']['labels'][] = $data_obj ? $data_obj->format('m/Y') : $row['periodo'];
        $dados_grafico['mensal']['valores'][] = (float) $row['total'];
    }
}

// 3. Busca Anual (Últimos 5 Anos)
$sql_anual = "SELECT YEAR(COALESCE(o.data_saida, o.atualizado_em)) AS periodo, 
                     SUM(COALESCE(orcs.valor_total, 0)) AS total 
              FROM ordens_servico o 
              LEFT JOIN orcamentos orcs ON orcs.id_os = o.id_os 
              WHERE o.status = 'FINALIZADO' 
              GROUP BY periodo ORDER BY periodo ASC LIMIT 5";
$res_anual = mysqli_query($conn, $sql_anual);
if ($res_anual) {
    while ($row = mysqli_fetch_assoc($res_anual)) {
        $dados_grafico['anual']['labels'][] = $row['periodo'];
        $dados_grafico['anual']['valores'][] = (float) $row['total'];
    }
}

$tem_dados_grafico = count($dados_grafico['mensal']['valores']) > 0 || count($dados_grafico['semanal']['valores']) > 0 || count($dados_grafico['anual']['valores']) > 0;
$json_dados_grafico = json_encode($dados_grafico);

// =========================================================================
// LÓGICA DO NOVO GRÁFICO (COLUNAS) - VALORES POR STATUS DA O.S
// =========================================================================
$sql_status_valor = "SELECT 
                        o.status, 
                        SUM(COALESCE(orcs.valor_total, 0)) AS total_valor 
                     FROM ordens_servico o 
                     LEFT JOIN orcamentos orcs ON orcs.id_os = o.id_os 
                     GROUP BY o.status 
                     ORDER BY total_valor DESC"; // Ordena do maior valor para o menor
$res_status_valor = mysqli_query($conn, $sql_status_valor);

$labels_status = [];
$valores_status = [];
$cores_status = [];

if ($res_status_valor && mysqli_num_rows($res_status_valor) > 0) {
    while ($row = mysqli_fetch_assoc($res_status_valor)) {
        $status_raw = strtoupper(trim($row['status']));
        
        // Remove os underlines e deixa bonitinho
        $status_nome = str_replace('_', ' ', $status_raw);
        $labels_status[] = $status_nome;
        $valores_status[] = (float) $row['total_valor'];
        
        // Define uma cor específica para cada coluna baseado no status
        if (strpos($status_raw, 'FINALIZADO') !== false || strpos($status_raw, 'CONCLUIDO') !== false) {
            $cores_status[] = 'rgba(25, 135, 84, 0.7)'; // Verde (Sucesso/Realizado)
        } elseif (strpos($status_raw, 'CANCELADO') !== false || strpos($status_raw, 'RECUSADO') !== false) {
            $cores_status[] = 'rgba(220, 53, 69, 0.7)'; // Vermelho (Perdido)
        } elseif (strpos($status_raw, 'ANALISE') !== false) {
            $cores_status[] = 'rgba(13, 110, 253, 0.7)'; // Azul (Em orçamento)
        } elseif (strpos($status_raw, 'REPARO') !== false || strpos($status_raw, 'ANDAMENTO') !== false) {
            $cores_status[] = 'rgba(255, 193, 7, 0.7)'; // Amarelo (Garantido, em execução)
        } elseif (strpos($status_raw, 'AGUARDANDO') !== false) {
            $cores_status[] = 'rgba(253, 126, 20, 0.7)'; // Laranja (Pausado/Aguardando)
        } else {
            $cores_status[] = 'rgba(108, 117, 125, 0.7)'; // Cinza (Outros)
        }
    }
}

$json_labels_status = json_encode($labels_status);
$json_valores_status = json_encode($valores_status);
$json_cores_status = json_encode($cores_status);
// =========================================================================

include '../includes/header.php'; 
?>

<style>
    /* =======================================================
       NOVO TRAVAMENTO ABSOLUTO (HEADER E BARRA LATERAL)
       ======================================================= */
    
    /* 1. Trava a Barra Superior (Nav) no topo da tela */
    .navbar {
        position: fixed !important;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1050;
    }

    /* 2. Empurra o corpo do site para não ficar por baixo da Navbar fixa */
    body {
        padding-top: 56px !important; /* Altura padrão da barra do topo */
    }

    @media (min-width: 768px) {
        /* 3. Trava a Barra Lateral de forma fixa e permanente */
        .sidebar {
            position: fixed !important;
            top: 56px; /* Cola exatamente embaixo da navbar */
            left: 0;
            width: 25%; /* Mesma largura do col-md-3 */
            height: calc(100vh - 56px) !important; /* Altura da tela menos o topo */
            z-index: 1040;
            overflow-y: auto; /* Rola internamente apenas se tiver muitos botões */
        }
        
        /* 4. Empurra o painel principal para a direita, para não sumir atrás da barra */
        .painel-direito {
            margin-left: 25% !important;
        }

        /* Customização da barrinha de rolagem invisível para a barra lateral */
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background-color: #333333; border-radius: 10px; }
        .sidebar::-webkit-scrollbar-thumb:hover { background-color: #ecc245; }
    }

    @media (min-width: 992px) {
        .sidebar {
            width: 16.666667% !important; /* Mesma largura do col-lg-2 */
        }
        .painel-direito {
            margin-left: 16.666667% !important;
        }
    }

    /* ======================================================= */

    /* Manter a cor de fundo original do projeto */
    .bg-original { background-color: #1e1e24 !important; }

    /* Estilos Menu Lateral Originais */
    .sidebar { background-color: #1e1e24 !important; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
    
    /* ======== ESTÉTICA PREMIUM PARA OS BOTÕES ======== */
    .sidebar .nav-link { 
        position: relative; 
        color: #a0a0a0; 
        font-size: 0.9rem; 
        padding: 10px 15px 10px 20px; 
        font-weight: 500; 
        border-radius: 0 50px 50px 0; 
        margin-bottom: 4px; 
        display: flex; 
        align-items: center; 
        justify-content: flex-start;
        text-align: left;
        overflow: hidden; 
        z-index: 1;
        transition: color 0.3s ease, transform 0.2s ease;
    }
    
    .sidebar .nav-link i {
        font-size: 1.1rem;
        min-width: 26px; 
        transition: transform 0.3s ease;
    }

    .sidebar .nav-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 0%; 
        background: linear-gradient(90deg, rgba(236,194,69,0.1) 0%, rgba(236,194,69,0) 100%); 
        z-index: -1;
        transition: width 0.3s ease-out; 
    }

    .sidebar .nav-link:not(.active):hover::before {
        width: 100%; 
    }
    .sidebar .nav-link:not(.active):hover { 
        color: #ecc245; 
        transform: translateX(4px); 
    }

    .sidebar .nav-link.active { 
        background-color: #ecc245; 
        color: #121212 !important; 
        font-weight: 600; 
        box-shadow: 0 4px 10px rgba(236, 194, 69, 0.25); 
    }

    /* Estilos do Fluxo de Processo */
    .fluxo-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        padding: 20px 0;
    }
    .etapa-fluxo {
        flex: 1;
        min-width: 180px;
        text-align: center;
        padding: 25px 15px;
        border-radius: 10px;
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        position: relative;
        transition: transform 0.2s;
    }
    .etapa-fluxo:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    .seta-fluxo {
        color: #adb5bd;
        font-size: 2rem;
    }
    .numero-destaque {
        font-size: 2.5rem;
        font-weight: 900;
        line-height: 1;
        margin: 10px 0;
    }

    @keyframes fadeScaleIn {
        0% { opacity: 0; transform: scale(0.97); }
        100% { opacity: 1; transform: scale(1); }
    }
    .animate-page {
        animation: fadeScaleIn 0.6s ease forwards;
        transform-origin: top center;
    }
</style>

<div class="container-fluid px-0">
    <div class="row g-0">
        
        <div class="col-md-3 col-lg-2 px-0 sidebar bg-original offcanvas-md offcanvas-start" tabindex="-1" id="sidebarMobile" aria-labelledby="sidebarMobileLabel">
            
            <div class="offcanvas-header border-bottom border-secondary d-md-none bg-original">
                <h5 class="offcanvas-title text-white fw-bold" id="sidebarMobileLabel">Mindtech</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMobile" aria-label="Close"></button>
            </div>

            <div class="offcanvas-body d-md-flex flex-column py-3 pe-3 ps-0 bg-original h-100">
                <ul class="nav flex-column pe-2 ps-0 w-100">
                    <li class="nav-item"><a class="nav-link" href="/mindtech/dashboard/index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    
                    <?php if (in_array($perfil, ['G', 'A'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/mindtech/clientes/listar.php"><i class="bi bi-person-vcard-fill me-2"></i> Atendimento</a></li>
                    <?php endif; ?>
                    
                    <?php if (in_array($perfil, ['G', 'A', 'T'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/mindtech/ordens_servico/listar.php"><i class="bi bi-tools me-2"></i> Ordens de Serviço</a></li>
                    <?php endif; ?>
                    
                    <?php if (in_array($perfil, ['G', 'A'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/mindtech/orcamentos/listar.php"><i class="bi bi-cash-coin me-2"></i> Orçamentos</a></li>
                    <?php endif; ?>

                    <?php if (in_array($perfil, ['G', 'A', 'E', 'T'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/mindtech/estoque/listar.php"><i class="bi bi-boxes me-2"></i> Estoque</a></li>
                    <?php endif; ?>
                    
                    <?php if (in_array($perfil, ['G', 'E', 'T'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/mindtech/fornecedores/listar.php"><i class="bi bi-truck me-2"></i> Fornecedores</a></li>
                    <?php endif; ?>
                    
                    <?php if (in_array($perfil, ['G'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/mindtech/relatorios/cadastrar.php"><i class="bi bi-bar-chart-fill me-2"></i> Relatórios</a></li>
                        <li class="nav-item"><a class="nav-link" href="/mindtech/usuarios/listar.php"><i class="bi bi-person me-2"></i> Usuários</a></li>
                    <?php endif; ?>
                </ul>
                <hr class="text-secondary mx-3 my-3 d-none d-md-block">
                
                <ul class="nav flex-column pe-2 ps-0 w-100 mt-auto">
                    <li class="nav-item"><a class="nav-link text-danger" href="/mindtech/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sair do Sistema</a></li>
                </ul>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4 animate-page painel-direito">
            
            <div class="d-md-none d-flex justify-content-between align-items-center mb-4 bg-original p-3 rounded shadow-sm text-white">
                <h5 class="mb-0 fw-bold" style="color: #ecc245;">Painel Mindtech</h5>
                <button class="btn btn-outline-light border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile" aria-controls="sidebarMobile">
                    <i class="bi bi-list fs-2"></i>
                </button>
            </div>

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
                <h1 class="h3 mb-0 text-gray-800 fw-bold">Painel de Controle</h1>
                <span class="text-white"><i class="bi bi-calendar-event me-1"></i> <?= date('d/m/Y') ?></span>            
            </div>

            <div class="card shadow-sm border-0 border-top border-4 border-warning mb-4">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="bi bi-diagram-3-fill text-warning me-2"></i> Fluxo de Ordens de Serviço
                </div>
                <div class="card-body px-3 px-md-4">
                    
                    <p class="text-muted mb-4">Acompanhamento em tempo real do status das manutenções na assistência técnica.</p>

                    <div class="fluxo-container">
                        <div class="etapa-fluxo border-primary bg-primary bg-opacity-10 w-100 w-sm-auto">
                            <i class="bi bi-search fs-3 text-primary"></i>
                            <div class="numero-destaque text-primary"><?= $contagem['analise'] ?></div>
                            <h6 class="fw-bold mb-0 text-primary">Em Análise</h6>
                            <small class="text-muted">Aguardando orçamento</small>
                        </div>

                        <i class="bi bi-arrow-down seta-fluxo d-block d-md-none text-center w-100"></i>
                        <i class="bi bi-arrow-right seta-fluxo d-none d-md-block"></i>

                        <div class="etapa-fluxo border-warning bg-warning bg-opacity-10 w-100 w-sm-auto">
                            <i class="bi bi-tools fs-3 text-warning"></i>
                            <div class="numero-destaque text-warning"><?= $contagem['reparo'] ?></div>
                            <h6 class="fw-bold mb-0 text-dark">Em Reparo</h6>
                            <small class="text-muted">Laboratório atuando</small>
                        </div>

                        <i class="bi bi-arrow-down seta-fluxo d-block d-md-none text-center w-100"></i>
                        <i class="bi bi-arrow-right seta-fluxo d-none d-md-block"></i>

                        <div class="etapa-fluxo border-success bg-success bg-opacity-10 w-100 w-sm-auto">
                            <i class="bi bi-check-circle-fill fs-3 text-success"></i>
                            <div class="numero-destaque text-success"><?= $contagem['finalizado'] ?></div>
                            <h6 class="fw-bold mb-0 text-success">Finalizado</h6>
                            <small class="text-muted">Pronto para entrega</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 border-start border-4 border-danger mb-4">
                <div class="card-header bg-white py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center border-0 gap-2">
                    <h5 class="fw-bold text-danger mb-0 fs-6 fs-md-5">
                        <i class="bi bi-hourglass-split me-2"></i>Ordens de Serviço — Aguardando Peças
                    </h5>
                    <span class="badge bg-danger fs-6 rounded-pill align-self-start align-self-sm-auto">
                        <?= mysqli_num_rows($res_aguardando) ?> Pendente(s)
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 ps-md-4">Nº O.S.</th>
                                    <th>Cliente</th>
                                    <th>Equipamento</th>
                                    <th>Aguardando Desde</th>
                                    <th class="text-center pe-3 pe-md-4">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($res_aguardando && mysqli_num_rows($res_aguardando) > 0) {
                                    while ($os = mysqli_fetch_assoc($res_aguardando)) { 
                                        $data_formatada = date('d/m/Y', strtotime($os['data_entrada']));
                                ?>
                                    <tr>
                                        <td class="ps-3 ps-md-4 fw-bold text-danger fs-6 fs-md-5">#<?= $os['id_os'] ?></td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($os['nome_cliente']) ?></td>
                                        <td class="text-muted"><?= htmlspecialchars($os['equipamento']) ?></td>
                                        <td><?= $data_formatada ?></td>
                                        <td class="text-center pe-3 pe-md-4">
                                            <a href="../ordens_servico/visualizar.php?id=<?= $os['id_os'] ?>" class="btn btn-sm btn-danger fw-bold">
                                                Ver O.S.
                                            </a>
                                        </td>
                                    </tr>
                                <?php 
                                    }
                                } else { 
                                ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted text-wrap">
                                            <span class="text-success fw-bold">
                                                <i class="bi bi-check-circle-fill me-2"></i>Excelente! Nenhuma Ordem de Serviço está retida por falta de peças.
                                            </span>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- ========================================================= -->
                <!-- CARD DINÂMICO 1: GRÁFICO DE FATURAMENTO                   -->
                <!-- ========================================================= -->
                <div class="col-xl-6 mb-4">
                    <div class="card shadow-sm border-0 border-top border-4 border-success h-100">
                        <div class="card-header bg-white fw-bold py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center">
                            <div class="mb-2 mb-sm-0">
                                <i class="bi bi-graph-up-arrow text-success me-2"></i> Faturamento (Realizado)
                            </div>
                            
                            <select id="filtroFaturamento" class="form-select form-select-sm w-auto shadow-sm" style="min-width: 110px;">
                                <option value="semanal">Semanal</option>
                                <option value="mensal" selected>Mensal</option>
                                <option value="anual">Anual</option>
                            </select>
                        </div>
                        <div class="card-body px-3 px-md-4">
                            <?php if ($tem_dados_grafico): ?>
                                <div style="position: relative; height: 320px; width: 100%;">
                                    <canvas id="graficoFaturamento"></canvas>
                                </div>
                                <script>
                                    document.addEventListener("DOMContentLoaded", function() {
                                        const dadosCompletos = <?= $json_dados_grafico ?>;
                                        const ctx = document.getElementById('graficoFaturamento').getContext('2d');
                                        
                                        let graficoInstancia = new Chart(ctx, {
                                            type: 'line',
                                            data: {
                                                labels: dadosCompletos['mensal'].labels,
                                                datasets: [{
                                                    label: 'Faturamento (R$)',
                                                    data: dadosCompletos['mensal'].valores,
                                                    borderColor: '#198754',
                                                    backgroundColor: 'rgba(25, 135, 84, 0.2)',
                                                    borderWidth: 2,
                                                    fill: true,
                                                    tension: 0.3
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: {
                                                    legend: { display: true, position: 'top' },
                                                    tooltip: {
                                                        callbacks: {
                                                            label: function(context) {
                                                                let valor = context.parsed.y || 0;
                                                                return 'R$ ' + valor.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                                            }
                                                        }
                                                    }
                                                },
                                                scales: {
                                                    y: {
                                                        beginAtZero: true,
                                                        ticks: {
                                                            callback: function(value) {
                                                                return 'R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        });

                                        document.getElementById('filtroFaturamento').addEventListener('change', function(e) {
                                            const periodoEscolhido = e.target.value;
                                            graficoInstancia.data.labels = dadosCompletos[periodoEscolhido].labels;
                                            graficoInstancia.data.datasets[0].data = dadosCompletos[periodoEscolhido].valores;
                                            graficoInstancia.update();
                                        });
                                    });
                                </script>
                            <?php else: ?>
                                <div class="text-center py-5 text-muted">
                                    <span class="text-warning fw-bold fs-5">
                                        <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>Não há dados suficientes.
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- CARD DINÂMICO 2: GRÁFICO DE COLUNAS (VALOR POR STATUS)    -->
                <!-- ========================================================= -->
                <div class="col-xl-6 mb-4">
                    <div class="card shadow-sm border-0 border-top border-4 border-info h-100">
                        <div class="card-header bg-white fw-bold py-3">
                            <i class="bi bi-bar-chart-fill text-info me-2"></i> Valores por Status (Em R$)
                        </div>
                        <div class="card-body px-3 px-md-4">
                            <?php if (count($labels_status) > 0): ?>
                                <div style="position: relative; height: 320px; width: 100%;">
                                    <canvas id="graficoStatusValor"></canvas>
                                </div>
                                <script>
                                    document.addEventListener("DOMContentLoaded", function() {
                                        const ctxStatus = document.getElementById('graficoStatusValor').getContext('2d');
                                        
                                        new Chart(ctxStatus, {
                                            type: 'bar',
                                            data: {
                                                labels: <?= $json_labels_status ?>,
                                                datasets: [{
                                                    label: 'Valor Total (R$)',
                                                    data: <?= $json_valores_status ?>,
                                                    backgroundColor: <?= $json_cores_status ?>,
                                                    borderColor: <?= $json_cores_status ?>.map(color => color.replace('0.7', '1')), // Borda com cor sólida
                                                    borderWidth: 1,
                                                    borderRadius: 4
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: {
                                                    legend: { display: false }, // Oculta a legenda, pois as cores já representam o status
                                                    tooltip: {
                                                        callbacks: {
                                                            label: function(context) {
                                                                let valor = context.parsed.y || 0;
                                                                return 'R$ ' + valor.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                                            }
                                                        }
                                                    }
                                                },
                                                scales: {
                                                    y: {
                                                        beginAtZero: true,
                                                        ticks: {
                                                            callback: function(value) {
                                                                // Retira casas decimais no eixo Y para ficar mais limpo
                                                                return 'R$ ' + value.toLocaleString('pt-BR'); 
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        });
                                    });
                                </script>
                            <?php else: ?>
                                <div class="text-center py-5 text-muted">
                                    <span class="text-warning fw-bold fs-5">
                                        <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>Nenhuma O.S vinculada a orçamentos foi encontrada.
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- ========================================================= -->
            </div>

        </div> 
    </div> 
</div> 

<?php include '../includes/footer.php'; ?>