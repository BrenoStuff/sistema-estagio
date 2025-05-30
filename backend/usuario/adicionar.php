<?php
require_once '../../config.php';
require_once '../helpers/db-connect.php';

// Dados recebidos
$user_nome = $_POST['user_nome'];
$user_login = $_POST['user_login'];
$user_senha = $_POST['user_senha'];
$user_curso = $_POST['user_id_curs'];
$user_ra = $_POST['user_ra'];
$user_contato = $_POST['user_contato'];
$user_tipo = "aluno"; // Tipo padrão para novos usuários

$sql = "INSERT INTO usuarios (user_nome, user_login, user_senha, user_id_curs, user_ra, user_contato, user_acesso) VALUES ('$user_nome', '$user_login', '$user_senha', '$user_curso', '$user_ra', '$user_contato', '$user_tipo')";
$stmt = $conexao->prepare($sql);
if ($stmt) {
    if ($stmt->execute()) {
        header("location: " . BASE_URL . "admin");
        exit();
    } else {
        header("location: " . BASE_URL . "error.php?aviso=Erro ao adicionar usuário: " . $stmt->error);
        exit();
    }
    $stmt->close();
} else {
    header("location: " . BASE_URL . "error.php?aviso=Erro ao preparar a consulta: " . $conexao->error);
    exit();
}