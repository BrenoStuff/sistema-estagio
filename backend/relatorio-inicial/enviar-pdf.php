<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php'; 
include_once '../helpers/save-file.php';
include_once '../helpers/format.php'; 

// 1. Dados recebidos e filtragem de entrada (Segurança)
$rini_id = filter_input(INPUT_POST, 'rini_id', FILTER_VALIDATE_INT);
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$cntr_id = filter_input(INPUT_POST, 'cntr_id', FILTER_VALIDATE_INT); // Mantido por contexto

// Validação básica do ID
if (!$rini_id) {
    $aviso = "ID de relatório inválido.";
    header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}

$file = $_FILES['relatorio_inicial'];

// 2. Diretórios de upload (Com caminho seguro)
$upload_dir = __DIR__ . '/../uploads/relatorio-inicial/'; // Caminho absoluto
$relative_dir = 'backend/uploads/relatorio-inicial/'; // Caminho relativo para o banco de dados
$relative_dir_safe = rtrim($relative_dir, '/\\') . DIRECTORY_SEPARATOR; // Garante o separador

// 3. Processamento do Upload
$nome_base = 'relatorio_assinado_' . $rini_id . '_' . $user_id;
$resultado = uploadPDF($file, $upload_dir, $nome_base);

if (!$resultado['success']) {
    header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
    exit();
}

// Caminho relativo que será salvo no banco
$caminho_relativo = $relative_dir_safe . $resultado['file_name'];

// 4. Atualiza no banco com Prepared Statement PDO
$sql = "UPDATE relatorio_inicial
        SET rini_assinatura = ?
        WHERE rini_id = ?";

try {
    $stmt = $conexao->prepare($sql);
    
    // Execução segura, passando o caminho e o ID como um array
    $execucao = $stmt->execute([$caminho_relativo, $rini_id]);

    if ($execucao) {
        header("Location:" . BASE_URL . "index.php?aviso=Relatório enviado com sucesso.");
        exit();
    } else {
        // Falha na execução que não lançou exceção (Fallback)
        header("Location:" . BASE_URL . "error.php?aviso=Erro ao enviar o relatório: Falha na execução.");
        exit();
    }
} catch (PDOException $e) {
    // Tratamento de erro seguro: registra o erro (log) e mostra mensagem genérica
    error_log("Erro PDO ao enviar PDF do relatório inicial: " . $e->getMessage());
    $aviso = "Erro interno ao enviar o relatório. Tente novamente mais tarde.";
    header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}
?>