<?php
header('Content-Type: application/json');
require_once '../config/conexao.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_peca = isset($_POST['id_peca']) ? (int)$_POST['id_peca'] : 0;
    $quantidade = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 0;
    $observacoes = isset($_POST['observacoes']) ? mysqli_real_escape_string($conn, trim($_POST['observacoes'])) : '';

    if ($id_peca > 0 && $quantidade > 0) {
        // Busca o nome da peça na tabela pecas para preencher o painel corretamente
        $res_peca = mysqli_query($conn, "SELECT descricao FROM pecas WHERE id_peca = $id_peca");
        $dados_peca = mysqli_fetch_assoc($res_peca);
        $nome_peca = $dados_peca ? mysqli_real_escape_string($conn, $dados_peca['descricao']) : 'Peça Desconhecida';

        // A tabela pedidos_reposicao não possui coluna id_usuario, por isso ela não é enviada aqui
        // (esse era o motivo do pedido nunca ser gravado: erro "Unknown column 'id_usuario'").
        $sql = "INSERT INTO pedidos_reposicao (id_peca, nome_peca, quantidade, observacoes, status, data_pedido) 
                VALUES ($id_peca, '$nome_peca', $quantidade, '$observacoes', 'PENDENTE', NOW())";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['sucesso' => true]);
            exit;
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => mysqli_error($conn)]);
            exit;
        }
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Parâmetros inválidos.']);
        exit;
    }
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método inválido.']);
}