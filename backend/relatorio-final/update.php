<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php';
include_once '../helpers/format.php';
include_once '../helpers/save-file.php';

// ------------------------------------------------------------------
// 1. Limpeza e filtragem dos dados de entrada
// ------------------------------------------------------------------
$rfin_id = filter_input(INPUT_POST, 'rfin_id_edit', FILTER_VALIDATE_INT);
$cntr_id = filter_input(INPUT_POST, 'cntr_id_edit', FILTER_VALIDATE_INT);
$rfin_sintese_empresa = filter_input(INPUT_POST, 'rfin_sintese_empresa_edit', FILTER_DEFAULT);

// ------------------------------------------------------------------
// 2. Lógica de Anexos (File Upload)
// ------------------------------------------------------------------

// Nomes de arquivo vêm de '..._edit'
$rfin_anexo_1_file = (isset($_FILES['rfin_anexo_1_edit']) && $_FILES['rfin_anexo_1_edit']['error'] != UPLOAD_ERR_NO_FILE) ? $_FILES['rfin_anexo_1_edit'] : null;
$rfin_anexo_2_file = (isset($_FILES['rfin_anexo_2_edit']) && $_FILES['rfin_anexo_2_edit']['error'] != UPLOAD_ERR_NO_FILE) ? $_FILES['rfin_anexo_2_edit'] : null;

// Diretórios de upload
$upload_dir = __DIR__ . '/../uploads/relatorio-anexos/'; // Caminho absoluto
$relative_dir = 'backend/uploads/relatorio-anexos/'; // Caminho relativo para o banco de dados
$relative_dir_safe = rtrim($relative_dir, '/\\') . DIRECTORY_SEPARATOR; // Garante o separador
$nome_base = 'relatorio_anexo_';

$rfin_anexo_1_path = null;
if ($rfin_anexo_1_file != null) {
    $resultado = uploadPDF($rfin_anexo_1_file, $upload_dir, $nome_base . '_1_' . $rfin_id);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $rfin_anexo_1_path = $relative_dir_safe . $resultado['file_name'];
}

$rfin_anexo_2_path = null;
if ($rfin_anexo_2_file != null) {
    $resultado = uploadPDF($rfin_anexo_2_file, $upload_dir, $nome_base . '_2_' . $rfin_id);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $rfin_anexo_2_path = $relative_dir_safe . $resultado['file_name'];
}
// Nota: A lógica original define o path como NULL se nenhum arquivo for enviado.

// ------------------------------------------------------------------
// 3. Lógica de Atividades (Arrays Sincronizados)
// ------------------------------------------------------------------
$atividades = array();
$resumos = array();
$disciplinas = array();
for ($i = 1; $i <= 10; $i++) {
    $atividade = filter_input(INPUT_POST, 'atividade' . $i . '_final_edit', FILTER_DEFAULT);
    if (!empty($atividade) && trim($atividade) !== '') {
        $atividades[] = $atividade;
        $resumos[] = filter_input(INPUT_POST, 'resumo' . $i . '_final_edit', FILTER_DEFAULT);
        $disciplinas[] = filter_input(INPUT_POST, 'disciplina' . $i . '_final_edit', FILTER_DEFAULT);
    }
}

// ------------------------------------------------------------------
// 4. Conexão com Banco de Dados e Transações PDO
// ------------------------------------------------------------------

try {
    // Inicia a Transação PDO
    $conexao->beginTransaction();

    // A) Deleta as atividades antigas (Com Prepared Statement)
    $sql_delete = "DELETE FROM atv_estagio_fin WHERE atvf_id_relatorio_fin = ?";
    $stmt_delete = $conexao->prepare($sql_delete);
    $stmt_delete->execute([$rfin_id]);

    // B) Insere as novas atividades (Com Prepared Statement dentro do loop)
    $sql_insert_atv = "INSERT INTO atv_estagio_fin (atvf_atividade, atvf_resumo, atvf_disciplina_relacionada, atvf_id_relatorio_fin) 
                       VALUES (?, ?, ?, ?)";
    $stmt_insert_atv = $conexao->prepare($sql_insert_atv);
    
    foreach ($atividades as $key => $atividade) {
        $stmt_insert_atv->execute([
            $atividade, 
            $resumos[$key], 
            $disciplinas[$key], 
            $rfin_id
        ]);
    }

    // C) Atualiza o relatório final (Com Prepared Statement)
    $sql_update_rfin = "UPDATE relatorio_final SET 
                            rfin_sintese_empresa = ?, 
                            rfin_anexo_1 = ?, 
                            rfin_anexo_2 = ? 
                        WHERE rfin_id = ?";
    $stmt_update_rfin = $conexao->prepare($sql_update_rfin);
    $stmt_update_rfin->execute([
        $rfin_sintese_empresa, 
        $rfin_anexo_1_path, 
        $rfin_anexo_2_path, 
        $rfin_id
    ]);

    // D) Atualiza o contrato (Com Prepared Statement)
    // (A lógica original atualiza o contrato mesmo se o ID não mudou)
    $sql_update_cntr = "UPDATE contratos SET cntr_id_relatorio_final = ? WHERE cntr_id = ?";
    $stmt_update_cntr = $conexao->prepare($sql_update_cntr);
    $stmt_update_cntr->execute([$rfin_id, $cntr_id]);

    // E) Se tudo deu certo, confirma a transação
    $conexao->commit();
    
    header("Location:" . BASE_URL . "index.php?aviso=Relatório final atualizado com sucesso!");
    exit();

} catch (PDOException $e) {
    // Em caso de qualquer erro, desfaz a transação inteira
    if ($conexao->inTransaction()) {
        $conexao->rollBack();
    }

    // Tratamento de erro seguro
    error_log("Erro PDO ao atualizar relatório final: " . $e->getMessage());
    $aviso = "Erro interno ao atualizar o relatório. Tente novamente mais tarde.";
    header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}
?>