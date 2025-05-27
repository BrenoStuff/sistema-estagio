<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php';
include_once '../helpers/format.php';
include_once '../helpers/save-file.php';

$rini_id = $_POST['rini_id_edit'];
$cntr_id = $_POST['cntr_id_edit'];

$rini_como_ocorreu = $_POST['rini_como_ocorreu_edit'];
$rini_dev_cronograma = $_POST['rini_dev_cronograma_edit'];
$rini_preparacao_inicio = $_POST['rini_preparacao_inicio_edit'];
$rini_dificul_encontradas = $_POST['rini_dificul_encontradas_edit'];
$rini_aplic_conhecimento = $_POST['rini_aplic_conhecimento_edit'];
$rini_novas_ferramentas = $_POST['rini_novas_ferramentas_edit'];

if (isset($_POST['rini_comentarios_edit']) && !empty($_POST['rini_comentarios_edit']) && trim($_POST['rini_comentarios_edit']) !== '') {
    $rini_comentarios = $_POST['rini_comentarios_edit'];
} else {
    $rini_comentarios = null;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Anexos
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rini_anexo_1 = $_FILES['rini_anexo_1_edit'];
$rini_anexo_2 = $_FILES['rini_anexo_2_edit'];

// Verifica se existe algum anexo na variavel $_FILES
if (isset($_FILES['rini_anexo_1_edit']) && $_FILES['rini_anexo_1_edit']['error'] == UPLOAD_ERR_NO_FILE) {
    $rini_anexo_1 = null; // Nenhum arquivo enviado
}

if (isset($_FILES['rini_anexo_2_edit']) && $_FILES['rini_anexo_2_edit']['error'] == UPLOAD_ERR_NO_FILE) {
    $rini_anexo_2 = null; // Nenhum arquivo enviado
}

// Diretórios de upload
$upload_dir = __DIR__ . '/../uploads/relatorio-anexos/'; // Caminho absoluto
$relative_dir = 'backend/uploads/relatorio-anexos/'; // Caminho relativo para o banco de dados

$nome_base = 'relatorio_anexo';

if ($rini_anexo_1 != null) {
    $resultado = uploadPDF($rini_anexo_1, $upload_dir, $nome_base . '_1_' . $rini_id);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $rini_anexo_1 = $relative_dir . $resultado['file_name'];
} else {
    $rini_anexo_1 = null;
}

if ($rini_anexo_2 != null) {
    $resultado = uploadPDF($rini_anexo_2, $upload_dir, $nome_base . '_2_' . $rini_id);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    $rini_anexo_2 = $relative_dir . $resultado['file_name'];
} else {
    $rini_anexo_2 = null;
}

// Deleta os anexos antigos, se existirem
$sql = "SELECT rini_anexo_1, rini_anexo_2 FROM relatorio_inicial WHERE rini_id = $rini_id";
$dado = $conexao->query($sql);
if ($dado->num_rows > 0) {
    $relatorio = $dado->fetch_assoc();
    if ($relatorio['rini_anexo_1'] != null && file_exists(__DIR__ . '/../../' . $relatorio['rini_anexo_1'])) {
        unlink(__DIR__ . '/../../' . $relatorio['rini_anexo_1']);
    }
    if ($relatorio['rini_anexo_2'] != null && file_exists(__DIR__ . '/../../' . $relatorio['rini_anexo_2'])) {
        unlink(__DIR__ . '/../../' . $relatorio['rini_anexo_2']);
    }
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$atividades = array();
for ($i = 1; $i <= 10; $i++) {
    if (isset($_POST['atividade' . $i . '_edit']) && !empty($_POST['atividade' . $i . '_edit']) && trim($_POST['atividade' . $i . '_edit']) !== '') {
        $atividades[] = $_POST['atividade' . $i . '_edit'];
        echo "Atividade $i: " . $_POST['atividade' . $i . '_edit'] . "<br>";
    }
}

$comentariosAtv = array();
for ($i = 1; $i <= 10; $i++) {
    if (isset($_POST['comentario' . $i . '_edit']) && !empty($_POST['comentario' . $i . '_edit']) && trim($_POST['comentario' . $i . '_edit']) !== '') {
        $comentariosAtv[] = $_POST['comentario' . $i . '_edit'];
        echo "Comentário da Atividade $i: " . $_POST['comentario' . $i . '_edit'] . "<br>";
    }
}

$sql = "UPDATE relatorio_inicial SET 
        rini_como_ocorreu = '$rini_como_ocorreu',
        rini_dev_cronograma = '$rini_dev_cronograma',
        rini_preparacao_inicio = '$rini_preparacao_inicio',
        rini_dificul_encontradas = '$rini_dificul_encontradas',
        rini_aplic_conhecimento = '$rini_aplic_conhecimento',
        rini_novas_ferramentas = '$rini_novas_ferramentas',
        rini_comentarios = '$rini_comentarios',
        rini_anexo_1 = '$rini_anexo_1',
        rini_anexo_2 = '$rini_anexo_2'
        WHERE rini_id = $rini_id";
if ($conexao->query($sql) === TRUE) {
    // Atualiza as atividades
    $sql = "DELETE FROM atv_estagio_ini WHERE atvi_id_relatorio_ini = $rini_id";
    $conexao->query($sql);
    foreach ($atividades as $key => $atividade) {
        $comentario = isset($comentariosAtv[$key]) ? $comentariosAtv[$key] : null;
        
        $sql = "INSERT INTO atv_estagio_ini (atvi_atividade, atvi_comentario, atvi_id_relatorio_ini) VALUES ('$atividade', '$comentario', '$rini_id')";
        if ($conexao->query($sql) === FALSE) {
            header("Location: ../error.php?aviso=Erro ao inserir atividades: " . $conexao->error);
            exit();
        }
    }
    header("location:" . BASE_URL . "index.php/?aviso=Relatório editado com sucesso!");
} else {
    header("location:" . BASE_URL . "error.php?aviso=Erro ao editar relatório: " . $conexao->error);
}