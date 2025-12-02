<?php
session_start();
require '../helpers/db-connect.php';

if (!isset($_SESSION['acesso']) || $_SESSION['acesso'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$id = filter_input(INPUT_POST, 'empr_id', FILTER_SANITIZE_NUMBER_INT);
$nome = filter_input(INPUT_POST, 'empr_nome', FILTER_DEFAULT);
$cnpj = filter_input(INPUT_POST, 'empr_cnpj', FILTER_DEFAULT);
$tipo = filter_input(INPUT_POST, 'empr_tipo', FILTER_DEFAULT);
$contato1 = filter_input(INPUT_POST, 'empr_contato_1', FILTER_DEFAULT);
$contato2 = filter_input(INPUT_POST, 'empr_contato_2', FILTER_DEFAULT);
$cidade = filter_input(INPUT_POST, 'empr_cidade', FILTER_DEFAULT);
$endereco = filter_input(INPUT_POST, 'empr_endereco', FILTER_DEFAULT);

try {
    $sql = "UPDATE empresas SET 
            empr_nome = :nome, 
            empr_cnpj = :cnpj, 
            empr_tipo = :tipo, 
            empr_contato_1 = :contato1,
            empr_contato_2 = :contato2,
            empr_cidade = :cidade,
            empr_endereco = :endereco
            WHERE empr_id = :id";
    
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':cnpj', $cnpj);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':contato1', $contato1);
    $stmt->bindParam(':contato2', $contato2);
    $stmt->bindParam(':cidade', $cidade);
    $stmt->bindParam(':endereco', $endereco);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        header("Location: ../../admin/empresas.php?msg=atualizado");
    } else {
        header("Location: ../../admin/empresas.php?error=erro_update");
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    header("Location: ../../admin/empresas.php?error=erro_bd");
}
?>