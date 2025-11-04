<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php';
include_once '../helpers/format.php';
include_once '../helpers/save-file.php';

// ------------------------------------------------------------------
// 1. Limpeza e filtragem dos dados de entrada
// ------------------------------------------------------------------
$rini_id = filter_input(INPUT_POST, 'rini_id_edit', FILTER_VALIDATE_INT);
$cntr_id = filter_input(INPUT_POST, 'cntr_id_edit', FILTER_VALIDATE_INT); // Não usado na query, mas mantido

$rini_como_ocorreu = filter_input(INPUT_POST, 'rini_como_ocorreu_edit', FILTER_SANITIZE_SPECIAL_CHARS);
$rini_dev_cronograma = filter_input(INPUT_POST, 'rini_dev_cronograma_edit', FILTER_SANITIZE_SPECIAL_CHARS);
$rini_preparacao_inicio = filter_input(INPUT_POST, 'rini_preparacao_inicio_edit', FILTER_SANITIZE_SPECIAL_CHARS);
$rini_dificul_encontradas = filter_input(INPUT_POST, 'rini_dificul_encontradas_edit', FILTER_SANITIZE_SPECIAL_CHARS);
$rini_aplic_conhecimento = filter_input(INPUT_POST, 'rini_aplic_conhecimento_edit', FILTER_SANITIZE_SPECIAL_CHARS);
$rini_novas_ferramentas = filter_input(INPUT_POST, 'rini_novas_ferramentas_edit', FILTER_SANITIZE_SPECIAL_CHARS);

$rini_comentarios = filter_input(INPUT_POST, 'rini_comentarios_edit', FILTER_SANITIZE_SPECIAL_CHARS);
if (empty($rini_comentarios) || trim($rini_comentarios) === '') {
    $rini_comentarios = null;
}

// ------------------------------------------------------------------
// 2. Lógica de Anexos (File Upload)
// ------------------------------------------------------------------

$rini_anexo_1_file = (isset($_FILES['rini_anexo_1_edit']) && $_FILES['rini_anexo_1_edit']['error'] != UPLOAD_ERR_NO_FILE) ? $_FILES['rini_anexo_1_edit'] : null;
$rini_anexo_2_file = (isset($_FILES['rini_anexo_2_edit']) && $_FILES['rini_anexo_2_edit']['error'] != UPLOAD_ERR_NO_FILE) ? $_FILES['rini_anexo_2_edit'] : null;

$upload_dir = __DIR__ . '/../uploads/relatorio-anexos/';
$relative_dir = 'backend/uploads/relatorio-anexos/';
$relative_dir_safe = rtrim($relative_dir, '/\\') . DIRECTORY_SEPARATOR;
$nome_base = 'relatorio_anexo_';

$rini_anexo_1_path = null;
if ($rini_anexo_1_file != null) {
    $resultado = uploadPDF($rini_anexo_1_file, $upload_dir, $nome_base . '_1_' . $rini_id);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $rini_anexo_1_path = $relative_dir_safe . $resultado['file_name'];
}

$rini_anexo_2_path = null;
if ($rini_anexo_2_file != null) {
    $resultado = uploadPDF($rini_anexo_2_file, $upload_dir, $nome_base . '_2_' . $rini_id);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $rini_anexo_2_path = $relative_dir_safe . $resultado['file_name'];
}

// ------------------------------------------------------------------
// 3. Lógica de Atividades (Coleta)
// ------------------------------------------------------------------
$atividades = array();
$comentariosAtv = array();

for ($i = 1; $i <= 10; $i++) {
    $atividade = filter_input(INPUT_POST, 'atividade' . $i . '_edit', FILTER_SANITIZE_SPECIAL_CHARS);
    if (!empty($atividade) && trim($atividade) !== '') {
        $atividades[] = $atividade;
        $comentario = filter_input(INPUT_POST, 'comentario' . $i . '_edit', FILTER_SANITIZE_SPECIAL_CHARS);
        $comentariosAtv[] = (empty($comentario) || trim($comentario) === '') ? null : $comentario;
    }
}

// ------------------------------------------------------------------
// 4. Conexão com Banco de Dados e Transações PDO
// ------------------------------------------------------------------

try {
    // Inicia a Transação PDO
    $conexao->beginTransaction();

    // A) Pega os caminhos dos arquivos antigos (para exclusão posterior)
    $sql_select = "SELECT rini_anexo_1, rini_anexo_2 FROM relatorio_inicial WHERE rini_id = ?";
    $stmt_select = $conexao->prepare($sql_select);
    $stmt_select->execute([$rini_id]);
    $relatorio_antigo = $stmt_select->fetch();

    // Lógica de Caminho: Usa o novo path se houver, senão, mantém o antigo.
    $final_anexo_1_path = $rini_anexo_1_path ?? $relatorio_antigo['rini_anexo_1'];
    $final_anexo_2_path = $rini_anexo_2_path ?? $relatorio_antigo['rini_anexo_2'];

    // B) Atualiza o relatório principal (Com Prepared Statement)
    $sql_update = "UPDATE relatorio_inicial SET 
                    rini_como_ocorreu = ?, rini_dev_cronograma = ?, rini_preparacao_inicio = ?,
                    rini_dificul_encontradas = ?, rini_aplic_conhecimento = ?, rini_novas_ferramentas = ?,
                    rini_comentarios = ?, rini_anexo_1 = ?, rini_anexo_2 = ?
                   WHERE rini_id = ?";
    $stmt_update = $conexao->prepare($sql_update);
    $stmt_update->execute([
        $rini_como_ocorreu, $rini_dev_cronograma, $rini_preparacao_inicio,
        $rini_dificul_encontradas, $rini_aplic_conhecimento, $rini_novas_ferramentas,
        $rini_comentarios, $final_anexo_1_path, $final_anexo_2_path, $rini_id
    ]);

    // C) Deleta as atividades antigas (Com Prepared Statement)
    $sql_delete = "DELETE FROM atv_estagio_ini WHERE atvi_id_relatorio_ini = ?";
    $stmt_delete = $conexao->prepare($sql_delete);
    $stmt_delete->execute([$rini_id]);

    // D) Insere as novas atividades (Com Prepared Statement no loop)
    $sql_insert_atv = "INSERT INTO atv_estagio_ini (atvi_atividade, atvi_comentario, atvi_id_relatorio_ini) VALUES (?, ?, ?)";
    $stmt_insert_atv = $conexao->prepare($sql_insert_atv);
    
    foreach ($atividades as $key => $atividade) {
        $stmt_insert_atv->execute([$atividade, $comentariosAtv[$key], $rini_id]);
    }

    // E) Exclui arquivos físicos antigos (APENAS se novos foram enviados)
    if ($rini_anexo_1_path && $relatorio_antigo['rini_anexo_1']) {
        $caminho_abs = __DIR__ . '/../../' . $relatorio_antigo['rini_anexo_1'];
        if (file_exists($caminho_abs) && !is_dir($caminho_abs)) {
            if (!unlink($caminho_abs)) throw new Exception("Falha ao remover anexo 1 antigo.");
        }
    }
    if ($rini_anexo_2_path && $relatorio_antigo['rini_anexo_2']) {
        $caminho_abs = __DIR__ . '/../../' . $relatorio_antigo['rini_anexo_2'];
        if (file_exists($caminho_abs) && !is_dir($caminho_abs)) {
            if (!unlink($caminho_abs)) throw new Exception("Falha ao remover anexo 2 antigo.");
        }
    }

    // F) Se tudo deu certo, confirma a transação
    $conexao->commit();
    
    header("location:" . BASE_URL . "index.php/?aviso=Relatório editado com sucesso!");
    exit();

} catch (Exception $e) { // Captura PDOException e Exception (de falha no unlink)
    // Em caso de qualquer erro, desfaz a transação inteira
    if ($conexao->inTransaction()) {
        $conexao->rollBack();
    }

    // Tratamento de erro seguro
    error_log("Erro PDO ao atualizar relatório inicial: " . $e->getMessage());
    $aviso = "Erro interno ao editar o relatório. Tente novamente mais tarde.";
    header("location:" . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}
?>