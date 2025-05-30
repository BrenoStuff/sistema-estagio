<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php';


// Dados recebidos
$rini_id = $_POST['rini_id'];

$sql = "UPDATE relatorio_inicial SET rini_aprovado = 1 WHERE rini_id = '$rini_id'";
$stmt = $conexao->prepare($sql);
if ($stmt) {
    if ($stmt->execute()) {
        header("location: " . BASE_URL . "admin");
        exit();
    } else {
        header("location: " . BASE_URL . "error.php?aviso=Erro ao aprovar relatório inicial: " . $stmt->error);
        exit();
    }
    $stmt->close();
} else {
    header("location: " . BASE_URL . "error.php?aviso=Erro ao preparar a consulta: " . $conexao->error);
    exit();
}