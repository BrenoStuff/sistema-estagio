<?php
include_once '../../config.php';
include_once '../auth/verifica.php';
include_once '../helpers/db-connect.php';
include_once '../helpers/format.php';

include_once '../helpers/save-file.php';

$cntr_id = $_POST['cntr_id'];
$rini_como_ocorreu = $_POST['rini_como_ocorreu'];
$rini_dev_cronograma = $_POST['rini_dev_cronograma'];
$rini_preparacao_inicio = $_POST['rini_preparacao_inicio'];
$rini_dificul_encontradas = $_POST['rini_dificul_encontradas'];
$rini_aplic_conhecimento = $_POST['rini_aplic_conhecimento'];
$rini_novas_ferramentas = $_POST['rini_novas_ferramentas'];
if (isset($_POST['rini_comentarios']) && !empty($_POST['rini_comentarios']) && trim($_POST['rini_comentarios']) !== '') {
    $rini_comentarios = $_POST['rini_comentarios'];
} else {
    $rini_comentarios = null;
}

// 
//
// Anexos
//
//
$rini_anexo_1 = $_FILES['rini_anexo_1'];
$rini_anexo_2 = $_FILES['rini_anexo_2'];

// Verifica se existe algum anexo na variavel $_FILES
if (isset($_FILES['rini_anexo_1']) && $_FILES['rini_anexo_1']['error'] == UPLOAD_ERR_NO_FILE) {
    $rini_anexo_1 = null; // Nenhum arquivo enviado
}

if (isset($_FILES['rini_anexo_2']) && $_FILES['rini_anexo_2']['error'] == UPLOAD_ERR_NO_FILE) {
    $rini_anexo_2 = null; // Nenhum arquivo enviado
}

// Diretórios de upload
$upload_dir = __DIR__ . '/../uploads/relatorio-anexos/'; // Caminho absoluto
$relative_dir = 'backend/uploads/relatorio-anexos/'; // Caminho relativo para o banco de dados


$nome_base = 'relatorio_anexo_';

if ($rini_anexo_1 != null) {
    $resultado = uploadPDF($rini_anexo_1, $upload_dir, $nome_base . '_1' . $rini_id);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $rini_anexo_1 = $relative_dir . $resultado['file_name'];
} else {
    $rini_anexo_1 = null;
}

if ($rini_anexo_2 != null) {
    $resultado = uploadPDF($rini_anexo_2, $upload_dir, $nome_base . '_2' . $rini_id);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $rini_anexo_2 = $relative_dir . $resultado['file_name'];
} else {
    $rini_anexo_2 = null;
}

///////////////////////

$atividades = array();
for ($i = 1; $i <= 10; $i++) {
    if (isset($_POST['atividade' . $i]) && !empty($_POST['atividade' . $i]) && trim($_POST['atividade' . $i]) !== '') {
        $atividades[] = $_POST['atividade' . $i];
    }
}

$comentariosAtv = array();
for ($i = 1; $i <= 10; $i++) {
    if (isset($_POST['comentario' . $i]) && !empty($_POST['comentario' . $i]) && trim($_POST['comentario' . $i]) !== '') {
        $comentariosAtv[] = $_POST['comentario' . $i];
    }
}


// conexoa com banco de dados e inserção dos dados do relatorio inicial
$sql = "INSERT INTO relatorio_inicial (rini_como_ocorreu, rini_dev_cronograma, rini_preparacao_inicio, rini_dificul_encontradas, rini_aplic_conhecimento, rini_novas_ferramentas, rini_comentarios, rini_anexo_1, rini_anexo_2)
        VALUES ('$rini_como_ocorreu', '$rini_dev_cronograma', '$rini_preparacao_inicio', '$rini_dificul_encontradas', '$rini_aplic_conhecimento', '$rini_novas_ferramentas', '$rini_comentarios', '$rini_anexo_1', '$rini_anexo_2')";

if ($conexao->query($sql) === TRUE) {
    $rini_id = $conexao->insert_id;

    foreach ($atividades as $key => $atividade) {
        $comentario = isset($comentariosAtv[$key]) ? $comentariosAtv[$key] : null;
        $sql = "INSERT INTO atv_estagio_ini (atvi_atividade, atvi_comentario, atvi_id_relatorio_ini) VALUES ('$atividade', '$comentario', '$rini_id')";
        if ($conexao->query($sql) === FALSE) {
            header("Location: ../error.php?aviso=Erro ao inserir atividades: " . $conexao->error);
            exit();
        }
    }

    $sql = "UPDATE contratos SET cntr_id_relatorio_inicial = '$rini_id' WHERE cntr_id = '$cntr_id'";
    if ($conexao->query($sql) === FALSE) {
        header("Location: ../error.php?aviso=Erro ao atualizar contrato: " . $conexao->error);
        exit();
    }
} else {
    header("Location: ../error.php?aviso=Erro ao inserir relatório inicial: " . $conexao->error);
}

$conexao->close();
header("Location: ../../index.php?aviso=Relatório inicial inserido com sucesso!");
?>

