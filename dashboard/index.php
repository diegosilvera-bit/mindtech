<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Define o fuso horário correto para o Brasil (Brasília)
date_default_timezone_set('America/Sao_Paulo');

// Captura o perfil do utilizador logado para exibir apenas os links permitidos na barra lateral
$perfil = $_SESSION['usuario']['perfil'] ?? '';

include '../includes/header.php'; 
?>

<style>
    /* Estilos para o Menu Lateral */
    .sidebar {
        min-height: calc(100vh - 56px); /* Ocupa a altura da tela descontando o header se houver */
        background-color: #1e1e24;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }
    .sidebar .nav-link {
        color: #b3b3b3;
        padding: 12px 20px;
        font-weight: 500;
        transition: all 0.2s ease;
        border-radius: 5px;
        margin-bottom: 5px;
    }
    .sidebar .nav-link:hover {
        color: #ffffff;
        background-color: #2d2d35;
    }
    .sidebar .nav-link.active {
        color: #ffffff;
        background-color: #ecc245; /* Dourado do sistema */
        color: #121212 !important;
        font-weight: bold;
    }

    /* Área reservada para renderizar o Gráfico de Processo */
    .area-grafico-processo {
        min-height: 450px;
        background-color: #fdfdfd;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="container-fluid">
    <div class="row">
        
        <div class="col-md-3 col-lg-2 px-0 sidebar d-none d-md-block p-3">
            <div class="position-sticky pt-3">
                <span class="text-muted px-3 text-uppercase px-3 small fw-bold d-block mb-3">Navegação</span>
                <ul class="nav flex-column px-2">
                    <li class="nav-item">
                        <a class="nav-link active" href="/mindtech/dashboard/index.php">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    
                    <?php if (in_array($perfil, ['G'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/mindtech/usuarios/listar.php">
                            <i class="bi bi-people-fill me-2"></i> Usuários
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (in_array($perfil, ['G', 'A'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/mindtech/clientes/listar.php">
                            <i class="bi bi-person-vcard-fill me-2"></i> Clientes
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (in_array($perfil, ['G', 'A', 'T'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/mindtech/equipamentos/listar.php">
                            <i class="bi bi-pc-display me-2"></i> Equipamentos
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (in_array($perfil, ['G', 'E', 'T'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/mindtech/fornecedores/listar.php">
                            <i class="bi bi-truck me-2"></i> Fornecedores
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (in_array($perfil, ['G', 'A', 'E', 'T'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/mindtech/pecas/listar.php">
                            <i class="bi bi-cpu-fill me-2"></i> Peças
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/mindtech/estoque/listar.php">
                            <i class="bi bi-boxes me-2"></i> Estoque
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (in_array($perfil, ['G', 'A', 'T'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/mindtech/ordens_servico/listar.php">
                            <i class="bi bi-tools me-2"></i> Ordens de Serviço
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (in_array($perfil, ['G', 'A'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/mindtech/orcamentos/listar.php">
                            <i class="bi bi-cash-coin me-2"></i> Orçamentos
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (in_array($perfil, ['G'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/mindtech/relatorios/listar.php">
                            <i class="bi bi-bar-chart-fill me-2"></i> Relatórios
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <hr class="text-secondary mx-3 my-4">
                
                <ul class="nav flex-column px-2">
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="/mindtech/logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i> Sair do Sistema
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800 fw-bold">Painel de Controle</h1>
                <span class="text-muted"><i class="bi bi-calendar-event me-1"></i> <?= date('d/m/Y') ?></span>
            </div>

            <div class="alert alert-success border-0 shadow-sm border-start border-4 border-success mb-4" role="alert">
                <i class="bi bi-person-check-fill me-2 fs-5"></i>
                Bem-vindo(a) de volta, <strong><?= htmlspecialchars($_SESSION['usuario']['nome'] ?? 'Usuário') ?></strong>.
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold py-3">
                    <i class="bi bi-diagram-3-fill me-2"></i> Gráfico de Processos e Fluxo de Trabalho
                </div>
                <div class="card-body p-4">
                    
                    <div class="area-grafico-processo">
                        <div class="text-center text-muted">
                            <i class="bi bi-bar-chart-line fs-1 mb-2 d-block text-secondary"></i>
                            <h5>Área Disponível para o Gráfico</h5>
                            <p class="small text-secondary px-3">
                                Insira aqui a sua tag &lt;canvas&gt; para Chart.js ou os elementos HTML/SVG do seu fluxo de processo.
                            </p>
                        </div>
                    </div>
                    
                </div>
            </div>
            </div> </div> </div> <?php include '../includes/footer.php'; ?>