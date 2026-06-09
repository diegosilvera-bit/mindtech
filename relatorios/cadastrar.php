<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Se o utilizador clicar em "Gerar Relatório"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo_relatorio = $_POST['tipo_relatorio'] ?? '';
    $data_inicio = $_POST['data_inicio'] ?? '';
    $data_fim = $_POST['data_fim'] ?? '';

    if (!empty($tipo_relatorio)) {
        // Redireciona para a página listar.php passando as datas escolhidas pela URL
        header("Location: listar.php?tipo=$tipo_relatorio&inicio=$data_inicio&fim=$data_fim");
        exit;
    }
}

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">
                <i class="bi bi-file-earmark-bar-graph-fill text-success me-2"></i>Relatórios Gerenciais
            </h1>
        </div>
        <a href="../dashboard/index.php" class="btn btn-sm btn-outline-secondary fw-bold px-3">
            <i class="bi bi-arrow-left me-1"></i> Painel Principal
        </a>
    </div>

    <div class="card shadow-sm border-0 border-start border-4 border-success">
        <div class="card-body p-4">
            
            <form method="post" action="cadastrar.php">
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-dark">
                            <i class="bi bi-layers-half me-1 text-muted"></i> 1. O que deseja analisar? 
                        </label>
                        <select class="form-select form-select-lg fw-bold text-secondary" name="tipo_relatorio" required style="border-radius: 8px;">
                            <option value="" disabled selected>Escolha uma opção de relatório...</option>
                            <option value="faturamento">💰 Faturamento (Receitas e Lucros)</option>
                            <option value="ordens_servico">📋 Ordens de Serviço por Período</option>
                            <option value="pecas_baixo_estoque">⚠️ Peças com Baixo Estoque (Alerta)</option>
                        </select>
                    </div>
                </div>

                <div class="row p-3 bg-light rounded-3 mb-4 mx-0 border">
                    <div class="col-12 mb-2 px-0">
                        <label class="form-label fw-bold text-dark mb-0">
                            <i class="bi bi-calendar3 me-1 text-muted"></i> 2. Defina o intervalo de tempo (Opcional)
                        </label>
                        <small class="text-muted d-block mb-2">Se deixar em branco, o sistema mostrará o histórico completo.</small>
                    </div>
                    
                    <div class="col-md-6 mb-3 mb-md-0 px-md-2 px-0">
                        <label class="form-label small fw-bold text-secondary">Data Inicial</label>
                        <input type="date" class="form-control form-control-lg" name="data_inicio" style="border-radius: 8px;">
                    </div>

                    <div class="col-md-6 px-md-2 px-0">
                        <label class="form-label small fw-bold text-secondary">Data Final</label>
                        <input type="date" class="form-control form-control-lg" name="data_fim" style="border-radius: 8px;">
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-20">
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="../dashboard/index.php" class="btn btn-lg btn-light border fw-bold px-4" style="font-size: 0.95rem;">Cancelar</a>
                    <button class="btn btn-lg btn-success fw-bold px-5 shadow-sm" type="submit" style="font-size: 0.95rem;">
                        <i class="bi bi-printer me-2"></i> Gerar Relatório
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>