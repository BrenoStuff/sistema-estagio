<?php

session_start();

if (!isset($_SESSION['usuario'])) {
    header("location:" . BASE_URL . "login.php?aviso=Você precisa estar logado para acessar esta página!");
    exit();
}

function verifica_acesso($nivel_acesso) {
    if ($_SESSION['acesso'] != $nivel_acesso) {
        header("location:" . BASE_URL . "error.php?aviso=Você não tem permissão para acessar esta página!");
        exit();
    }
}

?>