<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

$mensagem = ''; 
$tipo_alerta = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitização e captura dos dados
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $cnpj = filter_input(INPUT_POST, 'cnpj', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($nome) || empty($cnpj)) {
        $mensagem = "Os campos Nome e CNPJ são obrigatórios.";
        $tipo_alerta = "warning";
    } else {
        // Inclui a conexão (Ajustado para a pasta config)
        include '../config/conexao.php'; 
        
        // Proteção contra injeção de SQL antes de montar a string
        $nome = mysqli_real_escape_string($conn, $nome);
        $cnpj = mysqli_real_escape_string($conn, $cnpj);
        $email = mysqli_real_escape_string($conn, $email);
        $telefone = mysqli_real_escape_string($conn, $telefone);

        // Monta a query MySQLi
        $sql = "INSERT INTO fornecedores (nome, cnpj, telefone, email) VALUES ('$nome', '$cnpj', '$telefone', '$email')";
        
        // Executa
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $mensagem = "Fornecedor <strong>$nome</strong> cadastrado com sucesso!";
            $tipo_alerta = "success";
            unset($nome, $cnpj, $email, $telefone); // Limpa o formulário
        } else {
            $mensagem = "Erro ao salvar no banco de dados: " . mysqli_error($conn);
            $tipo_alerta = "danger";
        }
        
        // Fecha a conexão
        mysqli_close($conn);
    }
}
include '../includes/header.php'; 
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 "><i class="bi bi-truck text-white fs-2 me-1"></i> <h1>Cadastrar Fornecedor</h1>
        <a href="listar.php" class="btn btn-secondary me-2">Voltar para Lista</a>
    </div>

    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?= $tipo_alerta ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= $mensagem ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="post" action="">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Nome da Empresa *</label>
                        <input type="text" class="form-control" name="nome" placeholder="Ex: Sua Empresa S/A" value="<?= htmlspecialchars($nome ?? '') ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">CNPJ *</label>
                        <input type="text" class="form-control" name="cnpj" value="<?= htmlspecialchars($cnpj ?? '') ?>" required maxlength="18" placeholder="00.000.000/0000-00" oninput="mascaraCNPJ(this)">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">E-mail Comercial</label>
                        <input type="email" class="form-control" name="email" placeholder="contato@suaempresa.com.br" value="<?= htmlspecialchars($email ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Telefone / WhatsApp</label>
                        <input type="text" class="form-control" name="telefone" value="<?= htmlspecialchars($telefone ?? '') ?>" maxlength="15" placeholder="(00) 00000-0000" oninput="mascaraTelefone(this)">
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-success" type="submit">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
<script>
    // Máscara para CNPJ: 00.000.000/0000-00
    function mascaraCNPJ(input) {
        let v = input.value.replace(/\D/g, ""); // Remove tudo o que não é dígito
        v = v.replace(/^(\d{2})(\d)/, "$1.$2");
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
        v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
        v = v.replace(/(\d{4})(\d)/, "$1-$2");
        input.value = v.substring(0, 18); // Limita o tamanho
    }

    // Máscara para Telefone: (00) 0000-0000 ou (00) 00000-0000
    function mascaraTelefone(input) {
        let v = input.value.replace(/\D/g, ""); // Remove tudo o que não é dígito
        v = v.replace(/^(\d{2})(\d)/g, "($1) $2"); // Coloca parênteses
        v = v.replace(/(\d)(\d{4})$/, "$1-$2"); // Coloca o hífen
        input.value = v.substring(0, 15); // Limita o tamanho
    }
</script>