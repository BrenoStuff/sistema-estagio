<?php

include_once '../helpers/db-connect.php';
include_once '../helpers/format.php';
include_once '../helpers/save-file.php';

$contrato_id = $_POST['cntr_id'];
$ocorreu = $_POST['ocorreu'];
$cronograma = $_POST['cronograma'];
$preparacao = $_POST['preparacao'];
$dificuldades = $_POST['dificuldades'];
$aplicacoes = $_POST['aplicacoes'];
$novas_ferramentas = $_POST['novas_ferramentas'];
if (isset($_POST['comentarios'])) {
    $comentarios = $_POST['comentarios'];
} else {
    $comentarios = null;
}

if (isset($_POST['anexo1'])) {
    $anexo1 = saveFile($_POST['anexo1'], 'relatorio-inicial', 'anexo1-' . $contrato_id);
    if ($anexo1 == false) {
        header("Location: ../error.php?aviso=$aviso");
        exit();
    }
} else {
    $anexo1 = null;
}

if (isset($_POST['anexo2'])) {
    $anexo2 = saveFile($_POST['anexo2'], 'relatorio-inicial', 'anexo2-' . $contrato_id);
    if ($anexo2 == false) {
        header("Location: ../error.php?aviso=$aviso");
        exit();
    }
} else {
    $anexo2 = null;
}

$atividades = array();
for ($i = 1; $i <= 10; $i++) {
    if (isset($_POST['atividade' . $i])) {
        $atividades[] = $_POST['atividade' . $i];
    }
}

$comentariosAtv = array();
for ($i = 1; $i <= 10; $i++) {
    if (isset($_POST['comentario' . $i])) {
        $comentariosAtv[] = $_POST['comentario' . $i];
    }
}


// conexoa com banco de dados e inserção dos dados do relatorio inicial
$sql = "INSERT INTO relatorio_inicial (rini_como_ocorreu, rini_dev_cronograma, rini_preparacao_inicio, rini_dificul_encontradas, rini_aplic_conhecimento, rini_novas_ferramentas, rini_comentarios, rini_anexo_1, rini_anexo_2) VALUES ('$ocorreu', '$cronograma', '$preparacao', '$dificuldades', '$aplicacoes', '$novas_ferramentas', '$comentarios', '$anexo1', '$anexo2')";

if ($conexao->query($sql) === TRUE) {
    $rini_id = $conexao->insert_id;

    foreach ($atividades as $key => $atividade) {
        $comentario = isset($comentarios[$key]) ? $comentariosAtv[$key] : null;
        $sql = "INSERT INTO atv_estagio_ini (atvi_atividade, atvi_comentario, atvi_id_relatorio_ini) VALUES ('$atividade', '$comentario', '$rini_id')";
        if ($conexao->query($sql) === FALSE) {
            header("Location: ../error.php?aviso=Erro ao inserir atividades: " . $conexao->error);
            exit();
        }
    }

    $sql = "UPDATE contratos SET cntr_id_relatorio_inicial = '$rini_id' WHERE cntr_id = '$contrato_id'";
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

