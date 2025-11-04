<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php'; 

// 1. Dados recebidos e filtragem de entrada (Segurança)
// Precisamos do ID do relatório (para excluir) e do contrato (para atualizar)
$rini_id = filter_input(INPUT_POST, 'rini_id', FILTER_VALIDATE_INT);
$cntr_id = filter_input(INPUT_POST, 'cntr_id', FILTER_VALIDATE_INT);

// Validação básica
if (!$rini_id || !$cntr_id) {
    $aviso = "IDs de relatório ou contrato inválidos.";
    header("Location: " . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}

try {
    // Inicia a Transação PDO
    // Garante que todas as operações sejam concluídas com sucesso ou nenhuma seja aplicada.
    $conexao->beginTransaction(); 

    // 2. A) DELETE: Excluir atividades relacionadas (Tabela Filha)
    $sql_delete_atv = "DELETE FROM atv_estagio_ini WHERE atvi_id_relatorio_ini = ?";
    $stmt_atv = $conexao->prepare($sql_delete_atv);
    $stmt_atv->execute([$rini_id]);
    
    // 3. B) UPDATE: Resetar a referência do Relatório Inicial no Contrato
    $sql_update_cntr = "UPDATE contratos SET cntr_id_relatorio_inicial = NULL WHERE cntr_id = ?";
    $stmt_update = $conexao->prepare($sql_update_cntr);
    $stmt_update->execute([$cntr_id]);
    
    // 4. C) DELETE: Excluir o registro principal do Relatório Inicial (Tabela Pai)
    $sql_delete_rini = "DELETE FROM relatorio_inicial WHERE rini_id = ?";
    $stmt_rini = $conexao->prepare($sql_delete_rini);
    $execucao = $stmt_rini->execute([$rini_id]);

    if ($execucao) {
        // Confirma a Transação: Aplica todas as mudanças no banco
        $conexao->commit(); 
        header("Location: ../../index.php?aviso=Relatório inicial deletado com sucesso!");
        exit();
    } else {
        // Se a execução principal falhar (improvável com exceções ativas)
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
    error_log("Erro PDO ao deletar relatório inicial: " . $e->getMessage());
    $aviso = "Erro interno ao deletar relatório inicial. Tente novamente mais tarde.";
    header("Location: " . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}
?>