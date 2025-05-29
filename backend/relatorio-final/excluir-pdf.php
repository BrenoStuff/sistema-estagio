<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php';

// Recebe o ID do relatório
$rfin_id = $_POST['rfin_id'];

// Busca o caminho dos anexos no banco
$sql = "SELECT rfin_assinatura FROM relatorio_final WHERE rfin_id = $rfin_id";
$result = $conexao->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $caminho_relativo = $row['rfin_assinatura'];

    if ($caminho_relativo) {
        // Monta o caminho absoluto no servidor
        $caminho_arquivo = __DIR__ . '/../../' . $caminho_relativo;

        // Verifica se o arquivo existe e deleta
        if (file_exists($caminho_arquivo)) {
            if (!unlink($caminho_arquivo)) {
                header("Location:" . BASE_URL . "error.php?aviso=Erro ao excluir o arquivo do servidor.");
                exit();
            }
        }
    }
    

    // Atualiza o banco, remove os caminhos dos anexos
    $sql_update = "UPDATE relatorio_final
                   SET rfin_assinatura = NULL
                   WHERE rfin_id = $rfin_id";

    if ($conexao->query($sql_update) === TRUE) {
        header("Location:" . BASE_URL . "index.php?aviso=Anexos excluídos com sucesso.");
        exit();
    } else {
        header("Location:" . BASE_URL . "error.php?aviso=Erro ao atualizar o banco.");
        exit();
    }
} else {
    header("Location:" . BASE_URL . "error.php?aviso=Relatório não encontrado.");
    exit();
}

?>