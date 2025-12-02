<?php
require_once '../../config.php';
require_once '../helpers/db-connect.php';

// Limpeza básica dos dados de entrada
$login = filter_input(INPUT_POST, 'login', FILTER_DEFAULT);
$senha = $_POST['senha']; // Senha bruta para verificação

$sql = "SELECT user_id, user_acesso, user_senha FROM usuarios WHERE user_login = ?";

try {
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$login]);

    $usuario = $stmt->fetch();

    // Verifica se o usuário foi encontrado E se a senha confere com o HASH
    if ($usuario && password_verify($senha, $usuario['user_senha'])) {
        // Login bem-sucedido
        session_start();

        $_SESSION['usuario'] = $usuario['user_id'];
        $_SESSION['acesso'] = $usuario['user_acesso'];

        // Redireciona com base no nível de acesso
        if ($_SESSION['acesso'] == 'aluno') {
            header("location:../../");
        } else {
            header("location:../../" . $_SESSION['acesso'] . "/");
        }
        exit();

    } else {
        // Usuário não encontrado OU senha inválida
        $aviso = "Usuário ou senha inválidos!";
        header("location:../../login.php?aviso=$aviso");
        exit();
    }
} catch (PDOException $e) {
    // Trata erro de execução da consulta (em caso de falha no PDO)
    error_log("Erro de login PDO: " . $e->getMessage());
    $aviso = "Erro interno no sistema. Tente novamente mais tarde.";
    header("location:../../login.php?aviso=$aviso");
    exit();
}
?>