<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php';

// Recebe o ID do relatório
$rini_id = $_POST['rini_id'];

// Busca o caminho do arquivo no banco
$sql = "SELECT rini_assinatura FROM relatorio_inicial WHERE rini_id = $rini_id";
$result = $conexao->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $caminho_relativo = $row['rini_assinatura'];

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

    // Atualiza o banco, remove o caminho do PDF
    $sql_update = "UPDATE relatorio_inicial
                   SET rini_assinatura = ''
                   WHERE rini_id = $rini_id";

    if ($conexao->query($sql_update) === TRUE) {
        header("Location:" . BASE_URL . "index.php?aviso=Arquivo excluído com sucesso.");
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
