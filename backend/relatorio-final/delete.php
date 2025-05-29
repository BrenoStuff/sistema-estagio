<?php

include_once '../../config.php';
include_once '../helpers/db-connect.php';
include_once '../helpers/format.php';

$rfin_id = $_POST['rfin_id'];
$cntr_id = $_POST['cntr_id'];

$sql = "DELETE FROM atv_estagio_fin WHERE atvf_id_relatorio_fin = $rfin_id";
if ($conexao->query($sql) === TRUE) {
    
    // Deleta os anexos antigos, se existirem
    $sql = "SELECT rfin_anexo_1, rfin_anexo_2 FROM relatorio_final WHERE rfin_id = $rfin_id";
    $dado = $conexao->query($sql);
    if ($dado->num_rows > 0) {
        $relatorio = $dado->fetch_assoc();
        if ($relatorio['rfin_anexo_1'] != null && file_exists(__DIR__ . '/../../' . $relatorio['rfin_anexo_1'])) {
            unlink(__DIR__ . '/../../' . $relatorio['rfin_anexo_1']);
        }
        if ($relatorio['rfin_anexo_2'] != null && file_exists(__DIR__ . '/../../' . $relatorio['rfin_anexo_2'])) {
            unlink(__DIR__ . '/../../' . $relatorio['rfin_anexo_2']);
        }
    }

    $sql = "UPDATE contratos SET cntr_id_relatorio_final = NULL WHERE cntr_id = $cntr_id";
    if ($conexao->query($sql) === TRUE) {
        $sql = "DELETE FROM relatorio_final WHERE rfin_id = $rfin_id";
        if ($conexao->query($sql) === TRUE) {
            header("Location:" . BASE_URL . "index.php?aviso=Relatório final excluído com sucesso!");
        } else {
            header("Location:" . BASE_URL . "error.php?aviso=Erro ao excluir relatório final: " . $conexao->error);
            exit();
        }
    } else {
        header("Location:" . BASE_URL . "error.php?aviso=Erro ao atualizar contrato: " . $conexao->error);
        exit();
    }
} else {
    header("Location:" . BASE_URL . "error.php?aviso=Erro ao excluir atividades do relatório: " . $conexao->error);
    exit();
}
