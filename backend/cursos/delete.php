<?php
session_start();
require '../helpers/db-connect.php';

if (!isset($_SESSION['acesso']) || $_SESSION['acesso'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$id = filter_input(INPUT_POST, 'curs_id', FILTER_SANITIZE_NUMBER_INT);

if ($id) {
    try {
        // 1. Verifica se tem alunos vinculados a este curso
        $check = $conexao->prepare("SELECT COUNT(*) FROM usuarios WHERE user_id_curs = ?");
        $check->execute([$id]);
        
        if ($check->fetchColumn() > 0) {
            header("Location: ../../admin/cursos.php?error=tem_alunos");
            exit();
        }

        // 2. Se não tiver alunos, pode apagar
        $sql = "DELETE FROM cursos WHERE curs_id = :id";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            header("Location: ../../admin/cursos.php?msg=deletado");
        } else {
            header("Location: ../../admin/cursos.php?error=erro_delete");
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        header("Location: ../../admin/cursos.php?error=erro_bd");
    }
} else {
    header("Location: ../../admin/cursos.php");
}
?>