<?php
require_once '../../config.php';
require_once '../helpers/db-connect.php'; 
require_once '../helpers/save-file.php';

// Limpeza e filtragem dos dados de entrada
$cntr_id_usuario = filter_input(INPUT_POST, 'cntr_id_usuario', FILTER_VALIDATE_INT);
$cntr_id_empresa = filter_input(INPUT_POST, 'cntr_id_empresa', FILTER_VALIDATE_INT);
$cntr_data_inicio = filter_input(INPUT_POST, 'cntr_data_inicio', FILTER_SANITIZE_SPECIAL_CHARS);
$cntr_data_fim = filter_input(INPUT_POST, 'cntr_data_fim', FILTER_SANITIZE_SPECIAL_CHARS);
$cntr_escala_horario = filter_input(INPUT_POST, 'cntr_escala_horario', FILTER_SANITIZE_SPECIAL_CHARS);
// Os campos booleanos são definidos com base na existência no $_POST
$cntr_tipo_estagio = isset($_POST['cntr_tipo_estagio']) ? 1 : 0; 
$cntr_ativo = isset($_POST['cntr_ativo']) ? 1 : 0;

// --- Lógica de Upload de Arquivos (Aprimorada) ---

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

// Preparação do caminho relativo seguro (usando a recomendação de boas práticas)
$relative_dir_safe = rtrim($relative_dir, '/\\') . DIRECTORY_SEPARATOR;

$nome_base = 'contrato_';
if ($cntr_termo_contrato != null) {
    $resultado = uploadPDF($cntr_termo_contrato, $upload_dir, $nome_base . 'termo_' . $cntr_id_usuario);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $cntr_termo_contrato = $relative_dir_safe . $resultado['file_name'];
} else {
    $cntr_termo_contrato = null;
}

if ($cntr_anexo_extra != null) {
    $resultado = uploadPDF($cntr_anexo_extra, $upload_dir, $nome_base . 'anexo_' . $cntr_id_usuario);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $cntr_anexo_extra = $relative_dir_safe . $resultado['file_name'];
} else {
    $cntr_anexo_extra = null;
}

// --- Fim da Lógica de Upload de Arquivos ---

// Query com Prepared Statement PDO
$sql = "INSERT INTO contratos (cntr_id_usuario, cntr_id_empresa, cntr_data_inicio, cntr_data_fim, cntr_escala_horario, cntr_termo_contrato, cntr_anexo_extra, cntr_tipo_estagio, cntr_ativo) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

try {
    $stmt = $conexao->prepare($sql);
    
    // Executa a query passando os parâmetros em um array
    $execucao = $stmt->execute([
        $cntr_id_usuario, 
        $cntr_id_empresa, 
        $cntr_data_inicio, 
        $cntr_data_fim, 
        $cntr_escala_horario, 
        $cntr_termo_contrato, 
        $cntr_anexo_extra, 
        $cntr_tipo_estagio, 
        $cntr_ativo
    ]);
    
    if ($execucao) {
        // Sucesso
        header("location: " . BASE_URL . "admin");
        exit();
    } else {
        // Falha na execução que não lançou exceção (Fallback)
        header("location: " . BASE_URL . "error.php?aviso=Erro ao adicionar contrato: Falha inesperada na execução.");
        exit();
    }
} catch (PDOException $e) {
    // Tratamento de erro seguro: registra o erro (log) e mostra mensagem genérica
    error_log("Erro PDO ao criar contrato: " . $e->getMessage());
    $aviso = "Erro interno ao adicionar contrato. Tente novamente mais tarde.";
    header("location: " . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}
?>