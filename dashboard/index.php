<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<?php 

require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 
require_once '../config/conexao.php'; 

// Define o fuso horário correto
date_default_timezone_set('America/Sao_Paulo');
$perfil = $_SESSION['usuario']['perfil'] ?? '';

// LÓGICA DO GRÁFICO: Contar O.S. por Status

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


// BUSCA DA TABELA DE ALERTA: O.S. com status 'AGUARDANDO_PECA'

$sql_aguardando_peca = "SELECT os.id_os, os.data_entrada, 
                               c.nome AS nome_cliente, 
                               CONCAT(e.marca, ' ', e.modelo) AS equipamento
                        FROM ordens_servico os
                        INNER JOIN clientes c ON os.id_cliente = c.id_cliente
                        INNER JOIN equipamentos e ON os.id_equipamento = e.id_equipamento
                        WHERE os.status = 'AGUARDANDO_PECA' 
                        ORDER BY os.id_os ASC"; 

$res_aguardando = mysqli_query($conn, $sql_aguardando_peca);

include '../includes/header.php'; 
?>

<style>
    /* Estilos Menu Lateral */
    .sidebar { min-height: calc(100vh - 56px); background-color: #1e1e24; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
    .sidebar .nav-link { color: #b3b3b3; padding: 12px 20px; font-weight: 500; transition: all 0.2s ease; border-radius: 5px; margin-bottom: 5px; }
    .sidebar .nav-link:hover { color: #ffffff; background-color: #2d2d35; }
    .sidebar .nav-link.active { background-color: #ecc245; color: #121212 !important; font-weight: bold; }

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
</style>

<div class="container-fluid">
    <div class="row">
        
        <div class="col-md-3 col-lg-2 px-0 sidebar d-none d-md-block p-3">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column px-2">
                    <li class="nav-item"><a class="nav-link active" href="/mindtech/dashboard/index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    
                    <?php if (in_array($perfil, ['G', 'A'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/mindtech/clientes/listar.php"><i class="bi bi-person-vcard-fill me-2"></i> Clientes</a></li>
                    <?php endif; ?>
                    
<<<<<<< HEAD
                    <?php if (in_array($perfil, ['G', 'A', 'T'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/mindtech/equipamentos/listar.php"><i class="bi bi-pc-display me-2" a> ></i> <br>Equipamentos </a></li>
                    <?php endif; ?>
                    
=======
>>>>>>> 9e7df6b47183ae8d4a95c599d7673436a5a81b21
                    <?php if (in_array($perfil, ['G', 'A'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/mindtech/orcamentos/listar.php"><i class="bi bi-cash-coin me-2"></i> Orçamentos</a></li>
                    <?php endif; ?>
                    
                    <?php if (in_array($perfil, ['G', 'A', 'T'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/mindtech/ordens_servico/listar.php"><i class="bi bi-tools me-2"></i> Ordens de Serviço</a></li>
                    <?php endif; ?>
                    
                    <?php if (in_array($perfil, ['G', 'A', 'E', 'T'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/mindtech/estoque/listar.php"><i class="bi bi-boxes me-2"></i> Estoque</a></li>
                    <?php endif; ?>
                    
                    <?php if (in_array($perfil, ['G', 'A', 'E', 'T'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/mindtech/pecas/listar.php"><i class="bi bi-cpu-fill me-2"></i> Peças</a></li>
                    <?php endif; ?>
                    
                    <?php if (in_array($perfil, ['G', 'E', 'T'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/mindtech/fornecedores/listar.php"><i class="bi bi-truck me-2"></i> Fornecedores</a></li>
                    <?php endif; ?>
                    
                    <?php if (in_array($perfil, ['G'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/mindtech/relatorios/cadastrar.php"><i class="bi bi-bar-chart-fill me-2"></i> Relatórios</a></li>
                    <?php endif; ?>
                    
                    <?php if (in_array($perfil, ['G'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/mindtech/usuarios/listar.php"><i class="bi bi-person me-2"></i> Usuários</a></li>
                    <?php endif; ?>
                </ul>
                <hr class="text-secondary mx-3 my-4">
                <ul class="nav flex-column px-2">
                    <li class="nav-item"><a class="nav-link text-danger" href="/mindtech/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sair do Sistema</a></li>
                </ul>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800 fw-bold">Painel de Controle</h1>
                <span class="text-white-50"><i class="bi bi-calendar-event me-1"></i> <?= date('d/m/Y') ?></span>            </div>

            <div class="card shadow-sm border-0 border-top border-4 border-warning mb-4">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="bi bi-diagram-3-fill text-warning me-2"></i> Fluxo de Ordens de Serviço
                </div>
                <div class="card-body px-4">
                    
                    <p class="text-muted mb-4">Acompanhamento em tempo real do status das manutenções na assistência técnica.</p>

                    <div class="fluxo-container">
                        <div class="etapa-fluxo border-primary bg-primary bg-opacity-10">
                            <i class="bi bi-search fs-3 text-primary"></i>
                            <div class="numero-destaque text-primary"><?= $contagem['analise'] ?></div>
                            <h6 class="fw-bold mb-0 text-primary">Em Análise</h6>
                            <small class="text-muted">Aguardando orçamento</small>
                        </div>

                        <i class="bi bi-arrow-right seta-fluxo d-none d-lg-block"></i>

                        <div class="etapa-fluxo border-warning bg-warning bg-opacity-10">
                            <i class="bi bi-tools fs-3 text-warning"></i>
                            <div class="numero-destaque text-warning"><?= $contagem['reparo'] ?></div>
                            <h6 class="fw-bold mb-0 text-dark">Em Reparo</h6>
                            <small class="text-muted">Laboratório atuando</small>
                        </div>

                        <i class="bi bi-arrow-right seta-fluxo d-none d-lg-block"></i>

                        <div class="etapa-fluxo border-success bg-success bg-opacity-10">
                            <i class="bi bi-check-circle-fill fs-3 text-success"></i>
                            <div class="numero-destaque text-success"><?= $contagem['finalizado'] ?></div>
                            <h6 class="fw-bold mb-0 text-success">Finalizado</h6>
                            <small class="text-muted">Pronto para entrega</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 border-start border-4 border-danger mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                    <h5 class="fw-bold text-danger mb-0">
                        <i class="bi bi-hourglass-split me-2"></i>Ordens de Serviço — Aguardando Peças
                    </h5>
                    <span class="badge bg-danger fs-6 rounded-pill">
                        <?= mysqli_num_rows($res_aguardando) ?> Pendente(s)
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width: 12%;">Nº O.S.</th>
                                    <th style="width: 28%;">Cliente</th>
                                    <th style="width: 35%;">Equipamento</th>
                                    <th style="width: 13%;">Aguardando Desde</th>
                                    <th class="text-center pe-4" style="width: 12%;">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($res_aguardando && mysqli_num_rows($res_aguardando) > 0) {
                                    while ($os = mysqli_fetch_assoc($res_aguardando)) { 
                                        $data_formatada = date('d/m/Y', strtotime($os['data_entrada']));
                                ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-danger fs-5">#<?= $os['id_os'] ?></td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($os['nome_cliente']) ?></td>
                                        <td class="text-muted"><?= htmlspecialchars($os['equipamento']) ?></td>
                                        <td><?= $data_formatada ?></td>
                                        <td class="text-center pe-4">
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
                                        <td colspan="5" class="text-center py-4 text-muted">
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

        </div> 
    </div> 
</div> 

<?php include '../includes/footer.php'; ?>