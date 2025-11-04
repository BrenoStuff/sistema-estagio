<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php'; 

// 1. Dados recebidos e filtragem de entrada
$rini_id = filter_input(INPUT_POST, 'rini_id', FILTER_VALIDATE_INT);

if (!$rini_id) {
    $aviso = "ID de relatório inválido.";
    header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}

try {
    // Inicia a Transação PDO
    $conexao->beginTransaction();

    // 2. A) Seleciona o caminho do arquivo ANTES de deletar
    $sql_select = "SELECT rini_assinatura FROM relatorio_inicial WHERE rini_id = ?";
    $stmt_select = $conexao->prepare($sql_select);
    $stmt_select->execute([$rini_id]);
    $resultado = $stmt_select->fetch();

    $caminho_relativo = $resultado ? $resultado['rini_assinatura'] : null;

    // 3. B) Atualiza o banco de dados (define o campo como NULL)
    $sql_update = "UPDATE relatorio_inicial SET rini_assinatura = NULL WHERE rini_id = ?";
    $stmt_update = $conexao->prepare($sql_update);
    $execucao = $stmt_update->execute([$rini_id]);

    if (!$execucao) {
        throw new PDOException("Falha ao atualizar o banco de dados.");
    }

    // 4. C) Exclui o arquivo físico do servidor
    if ($caminho_relativo) {
        // Constrói o caminho absoluto no servidor
        $caminho_absoluto = __DIR__ . '/../../' . $caminho_relativo;
        
        if (file_exists($caminho_absoluto) && !is_dir($caminho_absoluto)) {
            if (!unlink($caminho_absoluto)) {
                // Se a exclusão do arquivo falhar, desfaz a transação do banco
                throw new Exception("Falha ao excluir o arquivo físico no servidor.");
            }
        }
    }

    // 5. Confirma a Transação (Aplica as mudanças no banco)
    $conexao->commit();
    
    header("Location:" . BASE_URL . "index.php?aviso=PDF de assinatura excluído com sucesso.");
    exit();

} catch (Exception $e) { // Captura PDOException e Exception (de falha no unlink)
    // Desfaz a Transação em caso de qualquer erro
    if ($conexao->inTransaction()) {
        $conexao->rollBack();
    }

    // Tratamento de erro seguro: registra o erro (log) e mostra mensagem genérica
    error_log("Erro PDO ao excluir PDF do relatório inicial: " . $e->getMessage());
    $aviso = "Erro interno ao excluir o PDF. Tente novamente mais tarde.";
    header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}
?>