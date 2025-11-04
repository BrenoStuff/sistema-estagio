<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php';

// 1. Dados recebidos e filtragem de entrada
$rfin_id = filter_input(INPUT_POST, 'rfin_id', FILTER_VALIDATE_INT);
$cntr_id = filter_input(INPUT_POST, 'cntr_id', FILTER_VALIDATE_INT);

// Validação básica
if (!$rfin_id || !$cntr_id) {
    $aviso = "IDs de relatório ou contrato inválidos.";
    header("Location: " . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}

try {
    // Inicia a Transação PDO
    // Garante que todas as operações sejam concluídas com sucesso ou nenhuma seja aplicada.
    $conexao->beginTransaction(); 

    // 2. A) UPDATE: Resetar a referência do Relatório Final no Contrato
    $sql_update = "UPDATE contratos SET cntr_id_relatorio_final = NULL WHERE cntr_id = ?";
    $stmt_update = $conexao->prepare($sql_update);
    $stmt_update->execute([$cntr_id]);
    
    // 3. B) DELETE: Excluir o registro principal do Relatório Final
    $sql_delete = "DELETE FROM relatorio_final WHERE rfin_id = ?";
    $stmt_delete = $conexao->prepare($sql_delete);
    $execucao = $stmt_delete->execute([$rfin_id]);

    if ($execucao) {
        // Confirma a Transação: Aplica ambas as mudanças
        $conexao->commit(); 
        // Redireciona para o admin (conforme lógica original)
        header("location: " . BASE_URL . "admin"); 
        exit();
    } else {
        // Se a execução falhar (improvável com exceções ativas)
        $conexao->rollBack();
        header("location: " . BASE_URL . "error.php?aviso=Erro ao deletar relatório: Falha inesperada.");
        exit();
    }

} catch (PDOException $e) {
    // Em caso de qualquer erro, desfaz a transação
    if ($conexao->inTransaction()) {
        $conexao->rollBack();
    }

    // Tratamento de erro seguro: registra o erro (log) e mostra mensagem genérica
    error_log("Erro PDO ao deletar relatório final: " . $e->getMessage());
    $aviso = "Erro interno ao deletar relatório final. Tente novamente mais tarde.";
    header("Location: " . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}
?>