<?php
session_start();
require '../helpers/db-connect.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit();
}

$id_notificacao = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$link_destino = filter_input(INPUT_GET, 'link', FILTER_SANITIZE_URL);

if ($id_notificacao) {
    // Marca como lida, mas garante que a notificação pertence ao usuário logado (segurança)
    $sql = "UPDATE notificacoes SET not_lida = 1 WHERE not_id = ? AND not_user_id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$id_notificacao, $_SESSION['usuario']]);
}

// Se não tiver link, volta para a home
$destino = $link_destino ? '../../' . $link_destino : '../../index.php';
header("Location: " . $destino);
exit();
?>