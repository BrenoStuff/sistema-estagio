<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php'; 

// 1. Dados recebidos e filtragem de entrada (Segurança)
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
    $conexao->beginTransaction(); 

    // **** NOVO PASSO 1: Buscar os caminhos dos arquivos ANTES de deletar o registro ****
    $sql_select = "SELECT rfin_assinatura, rfin_anexo_1, rfin_anexo_2 FROM relatorio_final WHERE rfin_id = ?";
    $stmt_select = $conexao->prepare($sql_select);
    $stmt_select->execute([$rfin_id]);
    $relatorio = $stmt_select->fetch();

    $arquivos_para_deletar = [];
    if ($relatorio) {
        // O script 'delete.php' está em 'backend/relatorio-final/', então subimos (../../) para o ROOT.
        if (!empty($relatorio['rfin_assinatura'])) {
            $arquivos_para_deletar[] = '../../' . $relatorio['rfin_assinatura'];
        }
        if (!empty($relatorio['rfin_anexo_1'])) {
            $arquivos_para_deletar[] = '../../' . $relatorio['rfin_anexo_1'];
        }
        if (!empty($relatorio['rfin_anexo_2'])) {
            $arquivos_para_deletar[] = '../../' . $relatorio['rfin_anexo_2'];
        }
    }

    // 2. A) DELETE: Excluir atividades relacionadas (Tabela Filha)
    $sql_delete_atv = "DELETE FROM atv_estagio_fin WHERE atvf_id_relatorio_fin = ?";
    $stmt_atv = $conexao->prepare($sql_delete_atv);
    $stmt_atv->execute([$rfin_id]);
    
    // 3. B) UPDATE: Resetar a referência do Relatório Final no Contrato
    $sql_update_cntr = "UPDATE contratos SET cntr_id_relatorio_final = NULL WHERE cntr_id = ?";
    $stmt_update = $conexao->prepare($sql_update_cntr);
    $stmt_update->execute([$cntr_id]);
    
    // 4. C) DELETE: Excluir o registro principal do Relatório Final (Tabela Pai)
    $sql_delete_rfin = "DELETE FROM relatorio_final WHERE rfin_id = ?";
    $stmt_rfin = $conexao->prepare($sql_delete_rfin);
    $execucao = $stmt_rfin->execute([$rfin_id]);

    // **** NOVO PASSO 5: Deletar os arquivos físicos do servidor ****
    $erro_arquivo = false;
    foreach ($arquivos_para_deletar as $arquivo) {
        if (file_exists($arquivo)) {
            // Tenta deletar o arquivo
            if (!unlink($arquivo)) {
                // Se falhar, marca o erro e registra no log
                $erro_arquivo = true;
                error_log("Falha ao deletar arquivo físico: " . $arquivo);
            }
        }
    }

    if ($execucao && !$erro_arquivo) {
        // Confirma a Transação: Aplica todas as mudanças no banco
        $conexao->commit(); 
        header("Location: ../../index.php?aviso=Relatório final e arquivos deletados com sucesso!");
        exit();
    } else {
        // Se a execução do DB falhou OU a exclusão do arquivo falhou, desfaz tudo.
        $conexao->rollBack();
        if ($erro_arquivo) {
             header("location: " . BASE_URL . "error.php?aviso=Erro ao deletar os arquivos PDF do servidor. A exclusão do relatório foi cancelada.");
        } else {
             header("location: " . BASE_URL . "error.php?aviso=Erro ao deletar relatório: Falha inesperada.");
        }
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