<?php
include_once '../../config.php';
// Requer o arquivo de conexão PDO
include_once '../helpers/db-connect.php';

// Dados recebidos e filtragem de entrada (Segurança)
$rini_id = filter_input(INPUT_POST, 'rini_id', FILTER_VALIDATE_INT);

if (!$rini_id) {
    $aviso = "ID de relatório inválido.";
    header("Location: ../../error.php?aviso=$aviso");
    exit();
}

// Query com Prepared Statement PDO usando o placeholder (?)
$sql = "UPDATE relatorio_inicial SET rini_aprovado = 0, rini_assinatura = NULL WHERE rini_id = ?";

try {
    $stmt = $conexao->prepare($sql);

    // Execução segura, passando o ID como um array
    $execucao = $stmt->execute([$rini_id]);
    
    if ($execucao) {
        // Sucesso
        header("location: " . BASE_URL . "admin");
        exit();
    } else {
        // Falha na execução que não lançou exceção (Fallback)
        header("location: " . BASE_URL . "error.php?aviso=Erro ao reprovar relatório inicial: Falha inesperada na execução.");
        exit();
    }

} catch (PDOException $e) {
    // Tratamento de erro seguro: registra o erro (log) e mostra mensagem genérica
    error_log("Erro PDO ao reprovar relatório inicial: " . $e->getMessage());
    $aviso = "Erro interno ao reprovar relatório inicial. Tente novamente mais tarde.";
    header("location: " . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}

?>