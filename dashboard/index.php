<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Define o fuso horário correto para o Brasil (Brasília)
date_default_timezone_set('America/Sao_Paulo');

include '../includes/header.php'; 
?>

<style>
    .card-hover {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card-hover:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
</style>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Painel de Controle</h1>
        <span class="text-muted"><i class="bi bi-calendar-event me-1"></i> <?= date('d/m/Y') ?></span>
    </div>

    <div class="alert alert-success border-0 shadow-sm border-start border-4 border-success mb-4" role="alert">
        <i class="bi bi-person-check-fill me-2 fs-5"></i>
        Bem-vindo(a) de volta, <strong><?= htmlspecialchars($_SESSION['usuario']['nome'] ?? 'Usuário') ?></strong>. O que vamos fazer hoje?
    </div>

    <div class="row g-4">
        
        <div class="col-md-4">
            <a href="/mindtech/usuarios/listar.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm border-start border-4 border-primary card-hover">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">Usuários</h5>
                            <small class="text-muted">Acessos do sistema</small>
                        </div>
                        <i class="bi bi-people-fill fs-1 text-primary opacity-50"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="/mindtech/clientes/listar.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm border-start border-4 border-success card-hover">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">Clientes</h5>
                            <small class="text-muted">Carteira de clientes</small>
                        </div>
                        <i class="bi bi-person-vcard-fill fs-1 text-success opacity-50"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="/mindtech/equipamentos/listar.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm border-start border-4 border-info card-hover">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">Equipamentos</h5>
                            <small class="text-muted">Aparelhos cadastrados</small>
                        </div>
                        <i class="bi bi-pc-display fs-1 text-info opacity-50"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="/mindtech/fornecedores/listar.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm border-start border-4 border-warning card-hover">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">Fornecedores</h5>
                            <small class="text-muted">Parceiros de negócios</small>
                        </div>
                        <i class="bi bi-truck fs-1 text-warning opacity-50"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="/mindtech/pecas/listar.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm border-start border-4 border-danger card-hover">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">Peças</h5>
                            <small class="text-muted">Catálogo de componentes</small>
                        </div>
                        <i class="bi bi-cpu-fill fs-1 text-danger opacity-50"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="/mindtech/estoque/listar.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm border-start border-4 border-secondary card-hover">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">Estoque</h5>
                            <small class="text-muted">Controle de inventário</small>
                        </div>
                        <i class="bi bi-boxes fs-1 text-secondary opacity-50"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="/mindtech/ordens_servico/listar.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm border-start border-4 border-dark card-hover">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">Ordens de Serviço</h5>
                            <small class="text-muted">Gerenciar manutenções</small>
                        </div>
                        <i class="bi bi-tools fs-1 text-dark opacity-50"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="/mindtech/orcamentos/listar.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm border-start border-4 border-primary card-hover">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">Orçamentos</h5>
                            <small class="text-muted">Valores e aprovações</small>
                        </div>
                        <i class="bi bi-cash-coin fs-1 text-primary opacity-50"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="/mindtech/relatorios/listar.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm border-start border-4 border-success card-hover">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">Relatórios</h5>
                            <small class="text-muted">Métricas e finanças</small>
                        </div>
                        <i class="bi bi-bar-chart-fill fs-1 text-success opacity-50"></i>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>