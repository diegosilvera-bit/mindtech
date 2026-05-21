<?php 
require_once __DIR__ . '/../includes/functions.php'; 
require_once '../includes/auth.php'; 

// Inclui a conexão com o banco de dados
include '../config/conexao.php'; 

// Busca todos os equipamentos, do mais novo para o mais antigo (DESC)
$sql = "SELECT * FROM equipamentos ORDER BY id_equipamento DESC";
$result = mysqli_query($conn, $sql);

include '../includes/header.php'; 
?>

<div class="container mt-4 mb-5">   <!-- Centraliza o conteúdo com espaçamento em cima e embaixo -->

    <div class="d-flex justify-content-between align-items-center mb-4">   <!-- Título e botões lado a lado -->
        <h1>Equipamentos Cadastrados</h1>
        <div>
            <a href="../dashboard/index.php" class="btn btn-secondary me-2">Voltar</a>
            <a href="cadastrar.php" class="btn btn-info text-white">+ Novo Equipamento</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 border-start border-4 border-info">   <!-- Card com sombra e borda azul grossa do lado esquerdo -->
        <div class="card-body p-0">
            
            <table class="table table-hover table-striped align-middle mb-0">   <!-- Tabela com listras e hover (escurece ao passar o mouse) -->
                <thead class="table-dark">   <!-- Cabeçalho escuro da tabela -->
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Dono (ID Cliente)</th>
                        <th>Tipo</th>
                        <th>Marca / Modelo</th>
                        <th>Nº de Série</th>
                        <th class="text-center pe-3">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($equip = mysqli_fetch_assoc($result)) { 
                    ?>
                            <tr>
                                <td class="ps-3 fw-bold">#<?php echo $equip['id_equipamento']; ?></td>
                                
                                <td>Cliente <?php echo $equip['id_cliente']; ?></td>
                                
                                <td>
                                    <!-- Badge azul para destacar o tipo do equipamento -->
                                    <span class="badge bg-info text-dark"><?php echo $equip['tipo']; ?></span>
                                </td>
                                
                                <td>
                                    <?php 
                                    $marca_modelo = trim($equip['marca'] . ' ' . $equip['modelo']);
                                    echo $marca_modelo != '' ? $marca_modelo : '-'; 
                                    ?>
                                </td>
                                
                                <td>
                                    <?php echo $equip['numero_serie'] != '' ? $equip['numero_serie'] : '-'; ?>
                                </td>
                                
                                <td class="text-center pe-3">
                                    <!-- Botão pequeno com borda azul -->
                                    <a href="editar.php?id=<?php echo $equip['id_equipamento']; ?>" class="btn btn-sm btn-outline-info">Editar</a>
                                </td>
                            </tr>
                    <?php 
                        } 
                    } else { 
                    ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Nenhum equipamento cadastrado na assistência ainda.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>