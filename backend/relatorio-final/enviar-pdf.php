<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php';
include_once '../helpers/save-file.php';
include_once '../helpers/format.php';

// Dados recebidos
$rfin_id = $_POST['rfin_id'];
$user_id = $_POST['user_id'];
$cntr_id = $_POST['cntr_id'];

$file = $_FILES['relatorio_final'];

// Diretórios de upload
$upload_dir = __DIR__ . '/../uploads/relatorio-final/'; // Caminho absoluto
$relative_dir = 'backend/uploads/relatorio-final/'; // Caminho relativo para o banco de dados

$nome_base = 'relatorio_assinado_' . $rfin_id . '_' . $user_id;
$resultado = uploadPDF($file, $upload_dir, $nome_base);

if (!$resultado['success']) {
    header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
    exit();
}

// Caminho relativo que será salvo no banco
$caminho_relativo = $relative_dir . $resultado['file_name'];

// Atualiza no banco
$sql = "UPDATE relatorio_final
        SET rfin_assinatura = '$caminho_relativo'
        WHERE rfin_id = $rfin_id";

if ($conexao->query($sql) === TRUE) {
    header("Location:" . BASE_URL . "index.php?aviso=Relatório enviado com sucesso.");
    exit();
} else {
    header("Location:" . BASE_URL . "error.php?aviso=Erro ao enviar o relatório.");
    exit();
}


?>