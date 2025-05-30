<?php
require_once '../../config.php';
require_once '../helpers/db-connect.php';

// Dados recebidos
$empr_nome = $_POST['empr_nome'];
$empr_contato_1 = $_POST['empr_contato_1'];
$empr_contato_2 = $_POST['empr_contato_2'];
if (isset($empr_contato_2) && $empr_contato_2 == "") {
    $empr_contato_2 = null; // Define como NULL se o campo estiver vazio
}
$empr_cidade = $_POST['empr_cidade'];
$empr_endereco = $_POST['empr_endereco'];

$sql = "INSERT INTO empresas (empr_nome, empr_contato_1, empr_contato_2, empr_cidade, empr_endereco) VALUES (?, ?, ?, ?, ?)";
$stmt = $conexao->prepare($sql);
if ($stmt) {
    $stmt->bind_param("sssss", $empr_nome, $empr_contato_1, $empr_contato_2, $empr_cidade, $empr_endereco);
    if ($stmt->execute()) {
        header("location: " . BASE_URL . "admin");
        exit();
    } else {
        header("location: " . BASE_URL . "error.php?aviso=Erro ao adicionar empresa: " . $stmt->error);
        exit();
    }
    $stmt->close();
} else {
    header("location: " . BASE_URL . "error.php?aviso=Erro ao preparar a consulta: " . $conexao->error);
    exit();
}