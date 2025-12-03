<?php
session_start();
require '../helpers/db-connect.php';

if (!isset($_SESSION['acesso']) || $_SESSION['acesso'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$id = filter_input(INPUT_POST, 'curs_id', FILTER_SANITIZE_NUMBER_INT);
$nome = filter_input(INPUT_POST, 'curs_nome', FILTER_DEFAULT);

if ($id && $nome) {
    try {
        $sql = "UPDATE cursos SET curs_nome = :nome WHERE curs_id = :id";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            header("Location: ../../admin/cursos.php?msg=atualizado");
        } else {
            header("Location: ../../admin/cursos.php?error=erro_update");
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        header("Location: ../../admin/cursos.php?error=erro_bd");
    }
} else {
    header("Location: ../../admin/cursos.php?error=dados_invalidos");
}
?>