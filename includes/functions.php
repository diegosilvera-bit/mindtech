<?php
require_once __DIR__ . '/../config/conexao.php';

/**
 * Sanitiza texto para evitar XSS ao exibir dados.
 */
function e($valor) {
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Busca todos os registros de uma tabela.
 */
function listarTodos($tabela, $ordem = 'id DESC') {
    global $pdo;
    $sql = "SELECT * FROM {$tabela} ORDER BY {$ordem}";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Busca um registro pelo ID.
 */
function buscarPorId($tabela, $campoId, $id) {
    global $pdo;
    $sql = "SELECT * FROM {$tabela} WHERE {$campoId} = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Insere um novo registro.
 * Exemplo: inserir('clientes', ['nome' => 'João', 'telefone' => '9999-9999']);
 */
function inserir($tabela, $dados) {
    global $pdo;

    $campos = array_keys($dados);
    $placeholders = array_map(fn($campo) => ':' . $campo, $campos);

    $sql = "INSERT INTO {$tabela} (" . implode(', ', $campos) . ")
            VALUES (" . implode(', ', $placeholders) . ")";

    $stmt = $pdo->prepare($sql);

    foreach ($dados as $campo => $valor) {
        $stmt->bindValue(':' . $campo, $valor);
    }

    $stmt->execute();
    return $pdo->lastInsertId();
}

/**
 * Atualiza um registro existente.
 * Exemplo: atualizar('clientes', 'id_cliente', 1, ['nome' => 'Maria']);
 */
function atualizar($tabela, $campoId, $id, $dados) {
    global $pdo;

    $sets = [];
    foreach ($dados as $campo => $valor) {
        $sets[] = "{$campo} = :{$campo}";
    }

    $sql = "UPDATE {$tabela}
            SET " . implode(', ', $sets) . "
            WHERE {$campoId} = :id";

    $stmt = $pdo->prepare($sql);

    foreach ($dados as $campo => $valor) {
        $stmt->bindValue(':' . $campo, $valor);
    }

    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    return $stmt->execute();
}

/**
 * Remove um registro.
 */
function excluir($tabela, $campoId, $id) {
    global $pdo;

    $sql = "DELETE FROM {$tabela} WHERE {$campoId} = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    return $stmt->execute();
}

/**
 * Conta a quantidade de registros de uma tabela.
 */
function contarRegistros($tabela) {
    global $pdo;

    $sql = "SELECT COUNT(*) AS total FROM {$tabela}";
    $stmt = $pdo->query($sql);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    return (int)$resultado['total'];
}

/**
 * Autentica usuário.
 * Compatível com senhas em texto puro e também com password_hash().
 */
function autenticar($login, $senha) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE login = :login LIMIT 1");
    $stmt->bindValue(':login', $login);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        return false;
    }

    // Senha criptografada com password_hash()
    if (password_verify($senha, $usuario['senha'])) {
        return $usuario;
    }

    // Compatibilidade com senhas antigas em texto puro
    if ($senha === $usuario['senha']) {
        return $usuario;
    }

    return false;
}

/**
 * Redireciona para outra página.
 */
function redirecionar($url) {
    header("Location: {$url}");
    exit;
}

/**
 * Exibe mensagens de sucesso/erro.
 */
function alerta($mensagem, $tipo = 'success') {
    return '<div class="alert alert-' . e($tipo) . '">' . e($mensagem) . '</div>';
}
?>
