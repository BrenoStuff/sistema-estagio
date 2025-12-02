<?php
include_once '../../config.php';
include_once '../helpers/db-connect.php';
include_once '../helpers/notificacoes.php';

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
        $sql = "SELECT cntr_id_usuario FROM contratos WHERE cntr_id_relatorio_inicial = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->execute([$rini_id]);
        $user = $stmt->fetch();
        novaNotificacao($conexao, $user['cntr_id_usuario'], "Relatório Inicial Reprovado", "Seu relatório inicial foi reprovado. Por favor, faça as correções necessárias e envie novamente.", "index.php");

        header("location: " . BASE_URL . "admin");
        exit();
    } else {
        // Falha na execução que não lançou exceção (Fallback)
        header("location: " . BASE_URL . "error.php?aviso=Erro ao reprovar relatório inicial: Falha inesperada na execução.");
        exit();
    }

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    die();
    // Tratamento de erro seguro: registra o erro (log) e mostra mensagem genérica
    error_log("Erro PDO ao reprovar relatório inicial: " . $e->getMessage());
    $aviso = "Erro interno ao reprovar relatório inicial. Tente novamente mais tarde.";
    header("location: " . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}

?>