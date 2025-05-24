<?php

require_once '../helpers/db-connect.php';

$login = $_POST['login'];
$senha = $_POST['senha'];

$sql = "SELECT * FROM usuarios WHERE user_login = '$login' AND user_senha = '$senha'";
$dado = $conexao->query($sql);
if ($dado->num_rows > 0) {
    session_start();

    $usuario = $dado->fetch_assoc();
    $_SESSION['usuario'] = $usuario['user_id'];
    $_SESSION['acesso'] = $usuario['user_acesso'];

    if ($_SESSION['acesso'] == 'aluno') {
        header("location:../../");
    } else {
        header("location:../../" . $_SESSION['acesso'] . "/");
    }
} else {
    $aviso = "Usuário ou senha inválidos!";
    header("location:../../login.php?aviso=$aviso");
}