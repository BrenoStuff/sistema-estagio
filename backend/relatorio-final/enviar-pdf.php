<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php';
include_once '../helpers/save-file.php';
include_once '../helpers/format.php';

// -----------------------------------------------
//  Coleta e Limpeza dos dados de entrada
// -----------------------------------------------

$rfin_id = filter_input(INPUT_POST, 'rfin_id', FILTER_VALIDATE_INT);
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$cntr_id = filter_input(INPUT_POST, 'cntr_id', FILTER_VALIDATE_INT); 

// Validação básica dos IDs usados
if (!$rfin_id || !$user_id) { 
    $aviso = "IDs de relatório ou usuário inválidos.";
    header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}

// -----------------------------------------------
// Lógica de Upload do PDF do Relatório Final
// -----------------------------------------------

// 1. Coleta do arquivo enviado
$file = $_FILES['relatorio_final'];

// 2. Diretórios de upload (Com caminho seguro)
$upload_dir = __DIR__ . '/../uploads/relatorio-final/'; // Caminho absoluto
$relative_dir = 'backend/uploads/relatorio-final/'; // Caminho relativo para o banco de dados
$relative_dir_safe = rtrim($relative_dir, '/\\') . DIRECTORY_SEPARATOR; // Garante o separador

// 3. Processamento do Upload
$nome_base = 'relatorio_assinado_' . $rfin_id . '_' . $user_id;
$resultado = uploadPDF($file, $upload_dir, $nome_base);

// Verificação de sucesso do upload
if (!$resultado['success']) {
    header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
    exit();
}

// Caminho relativo que será salvo no banco
$caminho_relativo = $relative_dir_safe . $resultado['file_name'];

// -----------------------------------------------
//  Atualização do Caminho no Banco de Dados
// -----------------------------------------------

$sql = "UPDATE relatorio_final
        SET rfin_assinatura = ?
        WHERE rfin_id = ?";

try {
    $stmt = $conexao->prepare($sql);
    
    $execucao = $stmt->execute([$caminho_relativo, $rfin_id]);

    if ($execucao) {
        header("Location:" . BASE_URL . "index.php?aviso=Relatório enviado com sucesso.");
        exit();
    } else {
        // Falha na execução que não lançou exceção (Fallback)
        header("Location:" . BASE_URL . "error.php?aviso=Erro ao enviar o relatório: Falha na execução.");
        exit();
    }
} catch (PDOException $e) {
    // Tratamento de erro
    error_log("Erro PDO ao enviar PDF do relatório final: " . $e->getMessage());
    $aviso = "Erro interno ao enviar o relatório. Tente novamente mais tarde.";
    header("Location: " . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}
?>