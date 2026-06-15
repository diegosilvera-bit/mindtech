<?php
require_once __DIR__ . '/../includes/functions.php';
require_once '../includes/auth.php';


// Inclui a conexão com o banco de dados
include '../config/conexao.php';

$mensagem = '';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Pega os dados do formulário de forma simples
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $rg = $_POST['rg'];
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];
    $data_nascimento = $_POST['data_nascimento'];

    // Validação básica (Nome e CPF são obrigatórios)
    if ($nome == '' || $cpf == '') {
        $mensagem = "Os campos Nome e CPF são obrigatórios.";
    } else {

        // Proteção contra aspas para não quebrar o banco de dados
        $nome = mysqli_real_escape_string($conn, $nome);
        $cpf = mysqli_real_escape_string($conn, $cpf);
        $rg = mysqli_real_escape_string($conn, $rg);
        $telefone = mysqli_real_escape_string($conn, $telefone);
        $endereco = mysqli_real_escape_string($conn, $endereco);

        // Tratamento simples para a data (se estiver vazia, grava como nulo no banco)
        if ($data_nascimento == '') {
            $sql_data = "NULL";
        } else {
            $sql_data = "'$data_nascimento'";
        }

        // Monta o comando SQL para inserir o cliente
        $sql = "INSERT INTO clientes (nome, cpf, rg, telefone, endereco, data_nascimento) 
                VALUES ('$nome', '$cpf', '$rg', '$telefone', '$endereco', $sql_data)";

        // Executa o comando e verifica se deu certo
        if (mysqli_query($conn, $sql)) {
            $mensagem = "Cliente cadastrado com sucesso!";
        } else {
            $mensagem = "Erro ao cadastrar o cliente: " . mysqli_error($conn);
        }
    }
}

include '../includes/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Cadastrar Novo Cliente</h1>
        <a href="listar.php" class="btn btn-secondary me-2">Voltar para a Lista</a>
    </div>

    <?php if ($mensagem != '') { ?>
        <div class="alert alert-info shadow-sm">
            <?php echo $mensagem; ?>
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
                        <input type="text" class="form-control" id="cpf" name="cpf" placeholder="000.000.000-00"
                            required>
                        <script>
                            document.getElementById('cpf').addEventListener('input', function (e) {
                                let valor = e.target.value.replace(/\D/g, '');

                                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                                valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

                                e.target.value = valor;
                            });
                        </script>

                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">RG</label>
                        <input type="text" class="form-control" id="rg" name="rg" placeholder="00.000.000-0">
                        <script>
                            document.getElementById('rg').addEventListener('input', function (e) {
                                let valor = e.target.value.replace(/\D/g, '');

                                valor = valor.replace(/(\d{2})(\d)/, '$1.$2');
                                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                                valor = valor.replace(/(\d{3})(\d{1})$/, '$1-$2');

                                e.target.value = valor;
                            });
                        </script>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Telefone / WhatsApp</label>
                        <input type="text" class="form-control" id="telefone" name="telefone"
                            placeholder="(00) 00000-0000">

                        <script>
                            document.getElementById('telefone').addEventListener('input', function (e) {
                                let valor = e.target.value.replace(/\D/g, '');

                                valor = valor.replace(/^(\d{2})(\d)/g, '($1) $2');
                                valor = valor.replace(/(\d{5})(\d)/, '$1-$2');

                                e.target.value = valor;
                            });
                        </script>

                    </div>
                </div>

                <div class="mb-3 mt-2">
                    <label class="form-label fw-bold">Endereço Completo</label>
                    <input type="text" class="form-control" name="endereco"
                        placeholder="Ex: Rua das Flores, 123 - Centro">
                </div>

                <hr class="mt-4">
                <div class="d-flex justify-content-end gap-2">
                    <a href="listar.php" class="btn btn-light border">Cancelar</a>
                    <button class="btn btn-success" type="submit">Salvar Cliente</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>