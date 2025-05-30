<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php';


// Dados recebidos
$rfin_id = $_POST['rfin_id'];

$sql = "UPDATE relatorio_final SET rfin_aprovado = 1 WHERE rfin_id = '$rfin_id'";
$stmt = $conexao->prepare($sql);
if ($stmt) {
    if ($stmt->execute()) {
        header("location: " . BASE_URL . "admin");
        exit();
    } else {
        header("location: " . BASE_URL . "error.php?aviso=Erro ao aprovar relatório final: " . $stmt->error);
        exit();
    }
    $stmt->close();
} else {
    header("location: " . BASE_URL . "error.php?aviso=Erro ao preparar a consulta: " . $conexao->error);
    exit();
}
$conexao->close();