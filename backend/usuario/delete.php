<?php
session_start();
require '../helpers/db-connect.php';

if (!isset($_SESSION['acesso']) || $_SESSION['acesso'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);

if ($id) {
    try {
        // Verifica se o aluno tem contratos
        $check = $conexao->prepare("SELECT COUNT(*) FROM contratos WHERE cntr_id_usuario = ?");
        $check->execute([$id]);
        
        if ($check->fetchColumn() > 0) {
            header("Location: ../../admin/alunos.php?error=tem_contratos");
            exit();
        }

        // Deleta apenas se for nível 'aluno' (segurança extra)
        $sql = "DELETE FROM usuarios WHERE user_id = :id AND user_acesso = 'aluno'";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            header("Location: ../../admin/alunos.php?msg=deletado");
        } else {
            header("Location: ../../admin/alunos.php?error=erro_delete");
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        header("Location: ../../admin/alunos.php?error=erro_bd");
    }
} else {
    header("Location: ../../admin/alunos.php");
}
?>