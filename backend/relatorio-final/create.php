<?php
include_once '../../config.php';
include_once '../auth/verifica.php';
include_once '../helpers/db-connect.php';
include_once '../helpers/format.php';
include_once '../helpers/save-file.php';

// ------------------------------------------------------------------
// 1. Limpeza e filtragem dos dados de entrada
// ------------------------------------------------------------------
$cntr_id = filter_input(INPUT_POST, 'cntr_id', FILTER_VALIDATE_INT);
$rfin_atv_concluidas = filter_input(INPUT_POST, 'rfin_atv_concluidas', FILTER_SANITIZE_SPECIAL_CHARS);
$rfin_dificuldades = filter_input(INPUT_POST, 'rfin_dificuldades', FILTER_SANITIZE_SPECIAL_CHARS);
$rfin_sugestoes = filter_input(INPUT_POST, 'rfin_sugestoes', FILTER_SANITIZE_SPECIAL_CHARS);
$rfin_comentarios = filter_input(INPUT_POST, 'rfin_comentarios', FILTER_SANITIZE_SPECIAL_CHARS);

// ------------------------------------------------------------------
// 2. Lógica de Anexos (File Upload)
// ------------------------------------------------------------------

// Inicializa variáveis de arquivo
$rfin_anexo_1 = (isset($_FILES['rfin_anexo_1']) && $_FILES['rfin_anexo_1']['error'] != UPLOAD_ERR_NO_FILE) ? $_FILES['rfin_anexo_1'] : null;
$rfin_anexo_2 = (isset($_FILES['rfin_anexo_2']) && $_FILES['rfin_anexo_2']['error'] != UPLOAD_ERR_NO_FILE) ? $_FILES['rfin_anexo_2'] : null;

// Diretórios de upload
$upload_dir = __DIR__ . '/../uploads/relatorio-anexos/'; // Caminho absoluto
$relative_dir = 'backend/uploads/relatorio-anexos/'; // Caminho relativo para o banco de dados
$relative_dir_safe = rtrim($relative_dir, '/\\') . DIRECTORY_SEPARATOR; // Garante o separador

$nome_base = 'relatorio_final_anexo_';

// Processamento do Anexo 1
if ($rfin_anexo_1 != null) {
    $resultado = uploadPDF($rfin_anexo_1, $upload_dir, $nome_base . '_1_' . $cntr_id);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $rfin_anexo_1_path = $relative_dir_safe . $resultado['file_name'];
} else {
    $rfin_anexo_1_path = null;
}

// Processamento do Anexo 2
if ($rfin_anexo_2 != null) {
    $resultado = uploadPDF($rfin_anexo_2, $upload_dir, $nome_base . '_2_' . $cntr_id);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $rfin_anexo_2_path = $relative_dir_safe . $resultado['file_name'];
} else {
    $rfin_anexo_2_path = null;
}

// ------------------------------------------------------------------
// 3. Conexão com Banco de Dados e Transações PDO
// ------------------------------------------------------------------

try {
    // Inicia uma transação para garantir que ambas as queries (INSERT e UPDATE) ocorram
    $conexao->beginTransaction(); 
    
    // A) INSERT na tabela relatorio_final (Com Prepared Statement)
    $sql_rfin = "INSERT INTO relatorio_final (rfin_atv_concluidas, rfin_dificuldades, rfin_sugestoes, rfin_comentarios, rfin_anexo_1, rfin_anexo_2)
                 VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt_rfin = $conexao->prepare($sql_rfin);
    $stmt_rfin->execute([
        $rfin_atv_concluidas, 
        $rfin_dificuldades, 
        $rfin_sugestoes, 
        $rfin_comentarios, 
        $rfin_anexo_1_path, 
        $rfin_anexo_2_path
    ]);

    // Obtém o ID do relatório final inserido
    $rfin_id = $conexao->lastInsertId();

    // B) UPDATE na tabela contratos (Com Prepared Statement)
    $sql_update = "UPDATE contratos SET cntr_id_relatorio_final = ? WHERE cntr_id = ?";
    
    $stmt_update = $conexao->prepare($sql_update);
    $stmt_update->execute([$rfin_id, $cntr_id]);

    // C) Confirma a Transação (Aplica as mudanças no banco)
    $conexao->commit(); 

    // D) Sucesso: Redireciona
    header("Location: ../../index.php?aviso=Relatório final inserido com sucesso!");
    exit();

} catch (PDOException $e) {
    // Em caso de qualquer erro, desfaz a transação
    if ($conexao->inTransaction()) {
        $conexao->rollBack();
    }

    // Tratamento de erro seguro: registra o erro (log) e mostra mensagem genérica
    error_log("Erro PDO ao criar relatório final: " . $e->getMessage());
    $aviso = "Erro interno ao inserir relatório final. Tente novamente mais tarde.";
    header("Location: ../../error.php?aviso=" . urlencode($aviso));
    exit();
}
?>