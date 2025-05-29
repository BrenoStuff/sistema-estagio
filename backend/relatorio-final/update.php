<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php';
include_once '../helpers/format.php';
include_once '../helpers/save-file.php';

$rfin_id = $_POST['rfin_id_edit'];
$cntr_id = $_POST['cntr_id_edit'];

$rfin_sintese_empresa = $_POST['rfin_sintese_empresa_edit'];

$rfin_anexo_1 = $_FILES['rfin_anexo_1_edit'];
$rfin_anexo_2 = $_FILES['rfin_anexo_2_edit'];

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
    $resultado = uploadPDF($rfin_anexo_1, $upload_dir, $nome_base . '_1_' . $rfin_id);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $rfin_anexo_1 = $relative_dir . $resultado['file_name'];
} else {
    $rfin_anexo_1 = null;
}

if ($rfin_anexo_2 != null) {
    $resultado = uploadPDF($rfin_anexo_2, $upload_dir, $nome_base . '_2_' . $rfin_id);
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
$disciplinas = array();
for ($i = 1; $i <= 10; $i++) {
    if (isset($_POST['atividade' . $i . '_final_edit']) && !empty($_POST['atividade' . $i . '_final_edit']) && trim($_POST['atividade' . $i . '_final_edit']) !== '') {
        $atividades[] = $_POST['atividade' . $i . '_final_edit'];
        $resumos[] = $_POST['resumo' . $i . '_final_edit'];
        $disciplinas[] = $_POST['disciplina' . $i . '_final_edit'];
    }
}

// Deleta as atividades antigas, se existirem
$sql = "DELETE FROM atv_estagio_fin WHERE atvf_id_relatorio_fin = $rfin_id";
if ($conexao->query($sql) === TRUE) {
    // Insere as novas atividades
    foreach ($atividades as $key => $atividade) {
        $resumo = $resumos[$key];
        $disciplina = $disciplinas[$key];

        $sql = "INSERT INTO atv_estagio_fin (atvf_atividade, atvf_resumo, atvf_disciplina_relacionada, atvf_id_relatorio_fin) VALUES ('$atividade', '$resumo', '$disciplina', $rfin_id)";
        if ($conexao->query($sql) !== TRUE) {
            header("Location:" . BASE_URL . "error.php?aviso=Erro ao inserir atividade: " . $conexao->error);
            exit();
        }
    }

    // Atualiza o relatório final
    $sql = "UPDATE relatorio_final SET rfin_sintese_empresa = '$rfin_sintese_empresa', rfin_anexo_1 = '$rfin_anexo_1', rfin_anexo_2 = '$rfin_anexo_2' WHERE rfin_id = $rfin_id";
    if ($conexao->query($sql) === TRUE) {
        // Atualiza o contrato com o ID do relatório final
        $sql = "UPDATE contratos SET cntr_id_relatorio_final = $rfin_id WHERE cntr_id = $cntr_id";
        if ($conexao->query($sql) === TRUE) {
            header("Location:" . BASE_URL . "index.php?aviso=Relatório final atualizado com sucesso!");
        } else {
            header("Location:" . BASE_URL . "error.php?aviso=Erro ao atualizar contrato: " . $conexao->error);
            exit();
        }
    } else {
        header("Location:" . BASE_URL . "error.php?aviso=Erro ao atualizar relatório final: " . $conexao->error);
        exit();
    }
} else {
    header("Location:" . BASE_URL . "error.php?aviso=Erro ao excluir atividades do relatório: " . $conexao->error);
    exit();
}


?>