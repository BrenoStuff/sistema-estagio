<?php
session_start();
require '../helpers/db-connect.php';

if ($_SESSION['acesso'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$id = filter_input(INPUT_POST, 'cntr_id', FILTER_SANITIZE_NUMBER_INT);
$data_inicio = filter_input(INPUT_POST, 'cntr_data_inicio', FILTER_SANITIZE_SPECIAL_CHARS);
$data_fim = filter_input(INPUT_POST, 'cntr_data_fim', FILTER_SANITIZE_SPECIAL_CHARS);
$hora_inicio = filter_input(INPUT_POST, 'cntr_hora_inicio', FILTER_SANITIZE_SPECIAL_CHARS);
$hora_fim = filter_input(INPUT_POST, 'cntr_hora_final', FILTER_SANITIZE_SPECIAL_CHARS);
$ativo = filter_input(INPUT_POST, 'cntr_ativo', FILTER_SANITIZE_NUMBER_INT);
$tipo = filter_input(INPUT_POST, 'cntr_tipo_estagio', FILTER_SANITIZE_NUMBER_INT);

// Nota: Upload de novos arquivos (termo/anexo) na edição requer lógica extra de upload. 
// Por simplicidade, focaremos nos dados textuais aqui.

try {
    $sql = "UPDATE contratos SET 
            cntr_data_inicio = :inicio, 
            cntr_data_fim = :fim, 
            cntr_hora_inicio = :hinicio, 
            cntr_hora_final = :hfim,
            cntr_ativo = :ativo,
            cntr_tipo_estagio = :tipo
            WHERE cntr_id = :id";
    
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':inicio', $data_inicio);
    $stmt->bindParam(':fim', $data_fim);
    $stmt->bindParam(':hinicio', $hora_inicio);
    $stmt->bindParam(':hfim', $hora_fim);
    $stmt->bindParam(':ativo', $ativo);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        header("Location: ../../admin/index.php?msg=contrato_atualizado");
    } else {
        header("Location: ../../admin/index.php?error=erro_update");
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    header("Location: ../../admin/index.php?error=erro_bd");
}
?>