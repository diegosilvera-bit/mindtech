<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Se o utilizador clicar em "Gerar Relatório"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo_relatorio = $_POST['tipo_relatorio'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];

    // Redireciona para a página listar.php passando as datas escolhidas pela URL
    header("Location: listar.php?tipo=$tipo_relatorio&inicio=$data_inicio&fim=$data_fim");
    exit;
}

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gerar Relatório</h1>
        <a href="../dashboard/index.php" class="btn btn-secondary me-2">Voltar ao Dashboard</a>
    </div>

    <div class="card shadow-sm border-0 border-start border-4 border-success">
        <div class="card-body p-4">
            <p class="text-muted mb-4">Escolha os filtros abaixo para gerar um relatório atualizado do sistema.</p>
            
            <form method="post" action="">
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Tipo de Relatório *</label>
                        <select class="form-select" name="tipo_relatorio" required>
                            <option value="">Selecione...</option>
                            <option value="faturamento">Faturamento (Receitas)</option>
                            <option value="ordens_servico">Ordens de Serviço por Período</option>
                            <option value="pecas_baixo_estoque">Peças com Baixo Estoque</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Data Inicial</label>
                        <input type="date" class="form-control" name="data_inicio">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Data Final</label>
                        <input type="date" class="form-control" name="data_fim">
                    </div>
                </div>

                <hr class="mt-4">
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="../dashboard/index.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-success" type="submit">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i> Gerar Relatório
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>