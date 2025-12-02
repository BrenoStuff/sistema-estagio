<?php
session_start();
require '../helpers/db-connect.php';

if (!isset($_SESSION['acesso']) || $_SESSION['acesso'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$id = filter_input(INPUT_POST, 'empr_id', FILTER_SANITIZE_NUMBER_INT);

if ($id) {
    try {
        // Verifica se existem contratos vinculados antes de deletar
        $check = $conexao->prepare("SELECT COUNT(*) FROM contratos WHERE cntr_id_empresa = ?");
        $check->execute([$id]);
        
        if ($check->fetchColumn() > 0) {
            header("Location: ../../admin/empresas.php?error=tem_contratos");
            exit();
        }

        $sql = "DELETE FROM empresas WHERE empr_id = :id";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            header("Location: ../../admin/empresas.php?msg=deletado");
        } else {
            header("Location: ../../admin/empresas.php?error=erro_delete");
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        header("Location: ../../admin/empresas.php?error=erro_bd");
    }
} else {
    header("Location: ../../admin/empresas.php");
}
?>