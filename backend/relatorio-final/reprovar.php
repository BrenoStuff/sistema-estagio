<?php
include_once '../../config.php';
// Requer o arquivo de conexão PDO
include_once '../helpers/db-connect.php'; 

// Dados recebidos e filtragem de entrada (Segurança)
$rfin_id = filter_input(INPUT_POST, 'rfin_id', FILTER_VALIDATE_INT);

if (!$rfin_id) {
    $aviso = "ID de relatório inválido.";
    header("Location: ../../error.php?aviso=$aviso");
    exit();
}

// Query com Prepared Statement PDO usando o placeholder (?)
$sql = "UPDATE relatorio_final SET rfin_aprovado = 0, rfin_assinatura = NULL WHERE rfin_id = ?";
try {
    $stmt = $conexao->prepare($sql);

    // Execução segura, passando o ID como um array
    $execucao = $stmt->execute([$rfin_id]);
    
    if ($execucao) {
        // Sucesso
        header("location: " . BASE_URL . "admin");
        exit();
    } else {
        // Falha na execução que não lançou exceção (Fallback)
        header("location: " . BASE_URL . "error.php?aviso=Erro ao reprovar relatório final: Falha inesperada na execução.");
        exit();
    }

} catch (PDOException $e) {
    // Tratamento de erro seguro: registra o erro (log) e mostra mensagem genérica
    error_log("Erro PDO ao reprovar relatório final: " . $e->getMessage());
    $aviso = "Erro interno ao reprovar relatório final. Tente novamente mais tarde.";
    header("location: " . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}

?>