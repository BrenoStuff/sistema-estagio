<?php
// Função para criar uma nova notificação
// Utilização novaNotificacao($conexao, $user_id, "Novo Contrato", "Um novo contrato foi cadastrado para você.", "meus-contratos.php");
function novaNotificacao($conexao, $user_id, $titulo, $mensagem, $link = '#') {
    try {
        $sql = "INSERT INTO notificacoes (not_user_id, not_titulo, not_mensagem, not_link) VALUES (?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->execute([$user_id, $titulo, $mensagem, $link]);
        return true;
    } catch (PDOException $e) {
        // Apenas registra o erro no log do servidor para não parar o sistema
        error_log("Erro ao criar notificacao: " . $e->getMessage());
        return false;
    }
}
?>