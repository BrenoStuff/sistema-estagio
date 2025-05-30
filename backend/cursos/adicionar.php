<?php
require_once '../../config.php';
require_once '../helpers/db-connect.php';

// Dados recebidos
$curs_nome = $_POST['curs_nome'];

$sql = "INSERT INTO cursos (curs_nome) VALUES ('$curs_nome')";
if ($conexao->query($sql) === TRUE) {
    header("location: " . BASE_URL . "admin");
    exit();
} else {
    header("location: " . BASE_URL . "error.php?aviso=Erro ao adicionar curso: " . $conexao->error);
    exit();
}