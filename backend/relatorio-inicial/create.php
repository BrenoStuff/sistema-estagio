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
$rini_como_ocorreu = filter_input(INPUT_POST, 'rini_como_ocorreu', FILTER_SANITIZE_SPECIAL_CHARS);
$rini_dev_cronograma = filter_input(INPUT_POST, 'rini_dev_cronograma', FILTER_SANITIZE_SPECIAL_CHARS);
$rini_preparacao_inicio = filter_input(INPUT_POST, 'rini_preparacao_inicio', FILTER_SANITIZE_SPECIAL_CHARS);
$rini_dificul_encontradas = filter_input(INPUT_POST, 'rini_dificul_encontradas', FILTER_SANITIZE_SPECIAL_CHARS);
$rini_aplic_conhecimento = filter_input(INPUT_POST, 'rini_aplic_conhecimento', FILTER_SANITIZE_SPECIAL_CHARS);
$rini_novas_ferramentas = filter_input(INPUT_POST, 'rini_novas_ferramentas', FILTER_SANITIZE_SPECIAL_CHARS);

$rini_comentarios = filter_input(INPUT_POST, 'rini_comentarios', FILTER_SANITIZE_SPECIAL_CHARS);
if (empty($rini_comentarios) || trim($rini_comentarios) === '') {
    $rini_comentarios = null;
}

// ------------------------------------------------------------------
// 2. Lógica de Anexos (File Upload)
// ------------------------------------------------------------------

$rini_anexo_1 = (isset($_FILES['rini_anexo_1']) && $_FILES['rini_anexo_1']['error'] != UPLOAD_ERR_NO_FILE) ? $_FILES['rini_anexo_1'] : null;
$rini_anexo_2 = (isset($_FILES['rini_anexo_2']) && $_FILES['rini_anexo_2']['error'] != UPLOAD_ERR_NO_FILE) ? $_FILES['rini_anexo_2'] : null;

$upload_dir = __DIR__ . '/../uploads/relatorio-anexos/';
$relative_dir = 'backend/uploads/relatorio-anexos/';
$relative_dir_safe = rtrim($relative_dir, '/\\') . DIRECTORY_SEPARATOR;
$nome_base = 'relatorio_anexo_';

if ($rini_anexo_1 != null) {
    $resultado = uploadPDF($rini_anexo_1, $upload_dir, $nome_base . '_1_' . $cntr_id);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $rini_anexo_1_path = $relative_dir_safe . $resultado['file_name'];
} else {
    $rini_anexo_1_path = null;
}

if ($rini_anexo_2 != null) {
    $resultado = uploadPDF($rini_anexo_2, $upload_dir, $nome_base . '_2_' . $cntr_id);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $rini_anexo_2_path = $relative_dir_safe . $resultado['file_name'];
} else {
    $rini_anexo_2_path = null;
}

// ------------------------------------------------------------------
// 3. Lógica de Atividades e Comentários (Arrays Sincronizados)
// ------------------------------------------------------------------

$atividades = array();
$comentariosAtv = array();

for ($i = 1; $i <= 10; $i++) {
    $atividade = filter_input(INPUT_POST, 'atividade' . $i, FILTER_SANITIZE_SPECIAL_CHARS);
    $comentario = filter_input(INPUT_POST, 'comentario' . $i, FILTER_SANITIZE_SPECIAL_CHARS);

    if (!empty($atividade)) {
        $atividades[] = $atividade;
        $comentariosAtv[] = (empty($comentario) || trim($comentario) === '') ? null : $comentario;
    }
}

// ------------------------------------------------------------------
// 4. Conexão com Banco de Dados e Transações PDO
// ------------------------------------------------------------------

try {
    // Inicia uma transação
    $conexao->beginTransaction(); 
    
    // A) INSERT na tabela relatorio_inicial
    $sql_rini = "INSERT INTO relatorio_inicial (rini_como_ocorreu, rini_dev_cronograma, rini_preparacao_inicio, rini_dificul_encontradas, rini_aplic_conhecimento, rini_novas_ferramentas, rini_comentarios, rini_anexo_1, rini_anexo_2)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_rini = $conexao->prepare($sql_rini);
    $stmt_rini->execute([
        $rini_como_ocorreu, 
        $rini_dev_cronograma, 
        $rini_preparacao_inicio, 
        $rini_dificul_encontradas, 
        $rini_aplic_conhecimento, 
        $rini_novas_ferramentas, 
        $rini_comentarios, 
        $rini_anexo_1_path,
        $rini_anexo_2_path
    ]);

    // Obtém o ID do relatório inicial inserido
    $rini_id = $conexao->lastInsertId();

    // B) INSERT das atividades (Prepara uma vez, executa várias)
    $sql_atv = "INSERT INTO atv_estagio_ini (atvi_atividade, atvi_comentario, atvi_id_relatorio_ini) VALUES (?, ?, ?)";
    $stmt_atv = $conexao->prepare($sql_atv);
    
    foreach ($atividades as $key => $atividade) {
        $comentario = $comentariosAtv[$key]; 
        $stmt_atv->execute([$atividade, $comentario, $rini_id]);
    }

    // C) UPDATE na tabela contratos
    $sql_update_cntr = "UPDATE contratos SET cntr_id_relatorio_inicial = ? WHERE cntr_id = ?";
    
    $stmt_update_cntr = $conexao->prepare($sql_update_cntr);
    $stmt_update_cntr->execute([$rini_id, $cntr_id]);

    // D) Confirma a transação
    $conexao->commit(); 

    // E) Sucesso: Redireciona
    header("Location: ../../index.php?aviso=Relatório inicial inserido com sucesso!");
    exit();

} catch (PDOException $e) {
    // Em caso de erro, desfaz a transação
    if ($conexao->inTransaction()) { $conexao->rollBack(); }

    // Tratamento de erro seguro
    error_log("Erro PDO ao criar relatório inicial: " . $e->getMessage());
    $aviso = "Erro interno ao inserir relatório inicial. Tente novamente mais tarde.";
    header("Location: ../../error.php?aviso=" . urlencode($aviso));
    exit();
}
?>