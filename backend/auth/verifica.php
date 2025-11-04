<?php
session_start();

// Se a sessão do usuário NÃO estiver definida, redireciona para o login.
if (!isset($_SESSION['usuario'])) {
    $aviso = "Você precisa estar logado para acessar esta página.";
    header("Location: " . BASE_URL . "login.php?aviso=$aviso");
    exit();
}
?>