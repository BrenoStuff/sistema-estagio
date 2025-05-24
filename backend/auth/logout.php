<?php

session_start();
session_destroy();

header("location:../../login.php?aviso=Você saiu com sucesso!");

?>
