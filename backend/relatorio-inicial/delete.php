<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php';
include_once '../helpers/format.php';

$rini_id = $_POST['rini_id'];
$cntr_id = $_POST['cntr_id'];

$sql = "DELETE FROM atv_estagio_ini WHERE atvi_id_relatorio_ini = $rini_id";
if ($conexao->query($sql) === TRUE) {
    
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


    $sql = "UPDATE contratos SET cntr_id_relatorio_inicial = NULL WHERE cntr_id = $cntr_id";
    if ($conexao->query($sql) === TRUE) {
        $sql = "DELETE FROM relatorio_inicial WHERE rini_id = $rini_id";
        if ($conexao->query($sql) === TRUE) {
            header("Location:" . BASE_URL . "index.php?aviso=Relatório inicial excluído com sucesso!");
        } else {
            header("Location:" . BASE_URL . "error.php?aviso=Erro ao excluir relatório inicial: " . $conexao->error);
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