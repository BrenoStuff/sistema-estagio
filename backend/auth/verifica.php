<?php

session_start();

if (!isset($_SESSION['usuario'])) {
    header("location:login.php?aviso=Você não está logado!");
    exit();
}

function verifica_acesso($nivel_acesso) {
    if ($_SESSION['acesso'] != $nivel_acesso) {
        header("location:../../error.php?aviso=Você não tem permissão para acessar esta página!");
        exit();
    }
}

?>