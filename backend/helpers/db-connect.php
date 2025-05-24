<?php

define('DB_HOST','localhost');
define('DB_NAME','sistema-estagio');
define('DB_USER','root');
define('DB_PASS','');

$conexao = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conexao->connect_error) {
    $aviso = "Erro de conexão: " . $conexao->connect_error;
    header("Location: ../error.php?aviso=$aviso");
    exit();
}

?>