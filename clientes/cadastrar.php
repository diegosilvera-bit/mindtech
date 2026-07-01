<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php
require_once __DIR__ . '/../includes/functions.php';
require_once '../includes/auth.php';

include '../config/conexao.php';

$mensagem = '';
$tipo_alerta = 'info';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nome            = $_POST['nome'];
    $cpf             = $_POST['cpf'];
    $rg              = $_POST['rg'];
    $telefone        = $_POST['telefone'];
    $endereco        = $_POST['endereco'];
    $data_nascimento = $_POST['data_nascimento'];

    if ($nome == '' || $cpf == '') {
        $mensagem    = "Os campos Nome e CPF são obrigatórios.";
        $tipo_alerta = 'danger';
    } else {

        $nome     = mysqli_real_escape_string($conn, $nome);
        $cpf      = mysqli_real_escape_string($conn, $cpf);
        $rg       = mysqli_real_escape_string($conn, $rg);
        $telefone = mysqli_real_escape_string($conn, $telefone);
        $endereco = mysqli_real_escape_string($conn, $endereco);

        // VERIFICAÇÃO DE CPF DUPLICADO
        $sql_check = "SELECT id_cliente FROM clientes WHERE cpf = '$cpf' LIMIT 1";
        $res_check  = mysqli_query($conn, $sql_check);

        if (mysqli_num_rows($res_check) > 0) {
            $mensagem    = "Já existe um cliente cadastrado com este CPF (<strong>$cpf</strong>). Verifique a lista de clientes.";
            $tipo_alerta = 'warning';
        } else {

            $sql_data = $data_nascimento == '' ? "NULL" : "'$data_nascimento'";

            $sql = "INSERT INTO clientes (nome, cpf, rg, telefone, endereco, data_nascimento) 
                    VALUES ('$nome', '$cpf', '$rg', '$telefone', '$endereco', $sql_data)";

            if (mysqli_query($conn, $sql)) {
                $mensagem    = "Cliente cadastrado com sucesso!";
                $tipo_alerta = 'success';
            } else {
                $mensagem    = "Erro ao cadastrar o cliente: " . mysqli_error($conn);
                $tipo_alerta = 'danger';
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1>Cadastrar Novo Cliente</h1>

        <a href="listar.php" class="btn btn-secondary me-2">Voltar para a Lista</a>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> shadow-sm fw-bold alert-dismissible fade show">
            <?php echo $mensagem; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0 border-start border-4 border-success">
        <div class="card-body p-4">
            <p class="text-muted mb-4">Preencha os dados pessoais e de contato do cliente.</p>

            <form method="post" action="">

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-bold">Nome Completo *</label>
                        <input type="text" class="form-control" name="nome" placeholder="Ex: João da Silva" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Data de Nascimento</label>
                        <input type="date" class="form-control" name="data_nascimento">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">CPF *</label>

                        <input type="text" class="form-control" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00"
                            required>

                        <script>
                            document.getElementById('cpf').addEventListener('input', function(e) {
                                let v = e.target.value.replace(/\D/g, '');
                                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                                v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                                e.target.value = v;
                            });
                        </script>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">RG</label>
                        <input type="text" class="form-control" id="rg" name="rg" placeholder="00.000.000-0" maxlength="12">
                        <script>
                            document.getElementById('rg').addEventListener('input', function(e) {
                                let v = e.target.value.replace(/\D/g, '');
                                v = v.replace(/(\d{2})(\d)/, '$1.$2');
                                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                                v = v.replace(/(\d{3})(\d{1})$/, '$1-$2');
                                e.target.value = v;
                            });
                        </script>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Telefone / WhatsApp</label>

                        <input type="text" class="form-control" id="telefone" name="telefone"
                            placeholder="(00) 00000-0000" maxlength="15">

                        <script>
                            document.getElementById('telefone').addEventListener('input', function(e) {
                                let v = e.target.value.replace(/\D/g, '');
                                v = v.replace(/^(\d{2})(\d)/g, '($1) $2');
                                v = v.replace(/(\d{5})(\d)/, '$1-$2');
                                e.target.value = v;
                            });
                        </script>
                    </div>
                </div>

                <div class="mb-3 mt-2">
                    <label class="form-label fw-bold">Endereço Completo</label>
                    <input type="text" class="form-control" name="endereco" placeholder="Ex: Rua das Flores, 123 - Centro">
                </div>

                <hr class="mt-4">
                <div class="d-flex flex-wrap justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-success" type="submit">Salvar Cliente</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../includes/footer.php'; ?>