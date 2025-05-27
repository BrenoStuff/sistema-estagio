<?php
require '../../config.php';

session_start();
session_destroy();

header("location:" . BASE_URL . "login.php?aviso=Você foi desconectado com sucesso.");

?>
