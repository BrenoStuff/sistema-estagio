<?php

include_once '../../config.php';
include_once '../auth/verifica.php';
include_once '../helpers/db-connect.php';
include_once '../helpers/format.php';

include_once '../helpers/save-file.php';

$cntr_id = $_POST['cntr_id'];
$rfin_sintese_empresa = $_POST['rfin_sintese_empresa'];

$rfin_anexo_1 = $_FILES['rfin_anexo_1'];
$rfin_anexo_2 = $_FILES['rfin_anexo_2'];

// Verifica se existe algum anexo na variavel $_FILES
if (isset($_FILES['rfin_anexo_1']) && $_FILES['rfin_anexo_1']['error'] == UPLOAD_ERR_NO_FILE) {
    $rfin_anexo_1 = null; // Nenhum arquivo enviado
}
if (isset($_FILES['rfin_anexo_2']) && $_FILES['rfin_anexo_2']['error'] == UPLOAD_ERR_NO_FILE) {
    $rfin_anexo_2 = null; // Nenhum arquivo enviado
}

// Diretórios de upload
$upload_dir = __DIR__ . '/../uploads/relatorio-anexos/'; // Caminho absoluto
$relative_dir = 'backend/uploads/relatorio-anexos/'; // Caminho relativo para o banco de dados

$nome_base = 'relatorio_anexo_';
if ($rfin_anexo_1 != null) {
    $resultado = uploadPDF($rfin_anexo_1, $upload_dir, $nome_base . '_1');
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $rfin_anexo_1 = $relative_dir . $resultado['file_name'];
} else {
    $rfin_anexo_1 = null;
}
if ($rfin_anexo_2 != null) {
    $resultado = uploadPDF($rfin_anexo_2, $upload_dir, $nome_base . '_2');
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $rfin_anexo_2 = $relative_dir . $resultado['file_name'];
} else {
    $rfin_anexo_2 = null;
}

// Atividades
$atividades = array();
$resumos = array();
$disciplina = array();
for ($i = 1; $i <= 10; $i++) {
    if (isset($_POST['atividade' . $i . '_final']) && !empty($_POST['atividade' . $i . '_final']) && trim($_POST['atividade' . $i . '_final']) !== '') {
        $atividades[] = $_POST['atividade' . $i . '_final'];
        $resumos[] = $_POST['resumo' . $i . '_final'];
        $disciplina[] = $_POST['disciplina' . $i . '_final'];
    }
}

// Conexao com banco de dados para inserir o relatorio final
$sql = "INSERT INTO relatorio_final (rfin_sintese_empresa, rfin_anexo_1, rfin_anexo_2) VALUES ( ?, ?, ?)";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("sss", $rfin_sintese_empresa, $rfin_anexo_1, $rfin_anexo_2);
if ($stmt->execute()) {
    $rfin_id = $conexao->insert_id;

    // Insere as atividades do relatório final
    foreach ($atividades as $key => $atividade) {
        $resumo = isset($resumos[$key]) ? $resumos[$key] : null;
        $disciplina_atv = isset($disciplina[$key]) ? $disciplina[$key] : null;

        $sql = "INSERT INTO atv_estagio_fin (atvf_atividade, atvf_resumo, atvf_disciplina_relacionada, atvf_id_relatorio_fin) VALUES (?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("sssi", $atividade, $resumo, $disciplina_atv, $rfin_id);
        if (!$stmt->execute()) {
            header("Location:" . BASE_URL . "error.php?aviso=Erro ao inserir atividades: " . $conexao->error);
            exit();
        }
    }

    // Atualiza o contrato com o ID do relatório final
    $sql = "UPDATE contratos SET cntr_id_relatorio_final = ? WHERE cntr_id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ii", $rfin_id, $cntr_id);
    if ($stmt->execute()) {
        header("Location:" . BASE_URL . "index.php?aviso=Relatório final criado com sucesso!");
    } else {
        header("Location:" . BASE_URL . "error.php?aviso=Erro ao atualizar contrato: " . $conexao->error);
    }
} else {
    header("Location:" . BASE_URL . "error.php?aviso=Erro ao inserir relatório final: " . $conexao->error);
}