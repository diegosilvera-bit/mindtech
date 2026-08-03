<?php
header('Content-Type: application/json');
require_once '../config/conexao.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pedido = isset($_POST['id_pedido']) ? (int)$_POST['id_pedido'] : 0;

    if ($id_pedido > 0) {
        $sql = "UPDATE pedidos_reposicao SET status = 'COMPRADO' WHERE id_pedido = $id_pedido AND status = 'PENDENTE'";

        if (mysqli_query($conn, $sql)) {
            if (mysqli_affected_rows($conn) > 0) {
                echo json_encode(['sucesso' => true]);
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Pedido não encontrado ou já foi marcado como comprado.']);
            }
            exit;
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => mysqli_error($conn)]);
            exit;
        }
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Parâmetros inválidos.']);
    }
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método inválido.']);
}