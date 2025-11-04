<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php';

// 1. Dados recebidos e filtragem de entrada
$rfin_id = filter_input(INPUT_POST, 'rfin_id', FILTER_VALIDATE_INT);

if (!$rfin_id) {
    $aviso = "ID de relatório inválido.";
    header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}

try {
    // Inicia a Transação PDO
    // Garante que o arquivo só seja excluído se o banco for atualizado (e vice-versa).
    $conexao->beginTransaction();

    // 2. A) Seleciona o caminho do arquivo ANTES de deletar (Com Prepared Statement)
    $sql_select = "SELECT rfin_assinatura FROM relatorio_final WHERE rfin_id = ?";
    $stmt_select = $conexao->prepare($sql_select);
    $stmt_select->execute([$rfin_id]);
    $resultado = $stmt_select->fetch();

    $caminho_relativo = $resultado ? $resultado['rfin_assinatura'] : null;

    // 3. B) Atualiza o banco de dados (define o campo como NULL) (Com Prepared Statement)
    $sql_update = "UPDATE relatorio_final SET rfin_assinatura = NULL WHERE rfin_id = ?";
    $stmt_update = $conexao->prepare($sql_update);
    $execucao = $stmt_update->execute([$rfin_id]);

    if (!$execucao) {
        // Se a atualização do DB falhar, lança uma exceção para o rollback
        throw new PDOException("Falha ao atualizar o banco de dados.");
    }

    // 4. C) Exclui o arquivo físico do servidor
    if ($caminho_relativo) {
        // Constrói o caminho absoluto no servidor (corrigido para a estrutura de diretórios)
        $caminho_absoluto = __DIR__ . '/../../' . $caminho_relativo;
        
        // Verifica se o arquivo existe e não é um diretório
        if (file_exists($caminho_absoluto) && !is_dir($caminho_absoluto)) {
            if (!unlink($caminho_absoluto)) {
                // Se a exclusão do arquivo falhar, lança exceção para desfaz a transação do banco
                throw new Exception("Falha ao excluir o arquivo físico no servidor.");
            }
        }
    }

    // 5. Confirma a Transação (Aplica as mudanças no banco)
    $conexao->commit();
    
    header("Location:" . BASE_URL . "index.php?aviso=PDF de assinatura excluído com sucesso.");
    exit();

} catch (Exception $e) { // Captura PDOException (DB) e Exception (unlink)
    // Desfaz a Transação em caso de qualquer erro
    if ($conexao->inTransaction()) {
        $conexao->rollBack();
    }

    // Tratamento de erro seguro: registra o erro (log) e mostra mensagem genérica
    error_log("Erro PDO ao excluir PDF do relatório final: " . $e->getMessage());
    $aviso = "Erro interno ao excluir o PDF. Tente novamente mais tarde.";
    header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}
?>