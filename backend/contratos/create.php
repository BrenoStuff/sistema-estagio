<?php
require_once '../../config.php';
require_once '../helpers/db-connect.php';

require_once '../helpers/save-file.php';

// Dados recebidos
$cntr_id_usuario = $_POST['cntr_id_usuario'];
$cntr_id_empresa = $_POST['cntr_id_empresa'];
$cntr_data_inicio = $_POST['cntr_data_inicio'];
$cntr_data_fim = $_POST['cntr_data_fim'];
$cntr_escala_horario = $_POST['cntr_escala_horario'];
$cntr_remunerado = isset($_POST['cntr_remunerado']) ? 1 : 0;
$cntr_ativo = isset($_POST['cntr_ativo']) ? 1 : 0;

// Verifica se existe algum anexo na variavel $_FILES
if (isset($_FILES['cntr_termo_contrato']) && $_FILES['cntr_termo_contrato']['error'] == UPLOAD_ERR_NO_FILE) {
    $cntr_termo_contrato = null; // Nenhum arquivo enviado
} else {
    $cntr_termo_contrato = $_FILES['cntr_termo_contrato'];
}
if (isset($_FILES['cntr_anexo_extra']) && $_FILES['cntr_anexo_extra']['error'] == UPLOAD_ERR_NO_FILE) {
    $cntr_anexo_extra = null; // Nenhum arquivo enviado
} else {
    $cntr_anexo_extra = $_FILES['cntr_anexo_extra'];
}

// Diretórios de upload
$upload_dir = __DIR__ . '/../uploads/contratos/'; // Caminho absoluto
$relative_dir = 'backend/uploads/contratos/'; // Caminho relativo para o banco de dados

$nome_base = 'contrato_';
if ($cntr_termo_contrato != null) {
    $resultado = uploadPDF($cntr_termo_contrato, $upload_dir, $nome_base . 'termo_' . $cntr_id_usuario);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $cntr_termo_contrato = $relative_dir . $resultado['file_name'];
} else {
    $cntr_termo_contrato = null;
}

if ($cntr_anexo_extra != null) {
    $resultado = uploadPDF($cntr_anexo_extra, $upload_dir, $nome_base . 'anexo_' . $cntr_id_usuario);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $cntr_anexo_extra = $relative_dir . $resultado['file_name'];
} else {
    $cntr_anexo_extra = null;
}

$sql = "INSERT INTO contratos (cntr_id_usuario, cntr_id_empresa, cntr_data_inicio, cntr_data_fim, cntr_escala_horario, cntr_termo_contrato, cntr_anexo_extra, cntr_remunerado, cntr_ativo) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conexao->prepare($sql);
if ($stmt) {
    $stmt->bind_param("iissssiii", $cntr_id_usuario, $cntr_id_empresa, $cntr_data_inicio, $cntr_data_fim, $cntr_escala_horario, $cntr_termo_contrato, $cntr_anexo_extra, $cntr_remunerado, $cntr_ativo);
    if ($stmt->execute()) {
        header("location: " . BASE_URL . "admin");
        exit();
    } else {
        header("location: " . BASE_URL . "error.php?aviso=Erro ao adicionar contrato: " . $stmt->error);
        exit();
    }
    $stmt->close();
} else {
    header("location: " . BASE_URL . "error.php?aviso=Erro ao preparar a consulta: " . $conexao->error);
    exit();
}




