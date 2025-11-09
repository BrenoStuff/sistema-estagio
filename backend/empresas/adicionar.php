<?php
require_once '../../config.php';
require_once '../helpers/db-connect.php'; 

// Limpeza e filtragem dos dados de entrada
$empr_nome = filter_input(INPUT_POST, 'empr_nome', FILTER_SANITIZE_SPECIAL_CHARS);
$empr_cnpj = filter_input(INPUT_POST, 'empr_cnpj', FILTER_SANITIZE_SPECIAL_CHARS);
$empr_tipo = filter_input(INPUT_POST, 'empr_tipo', FILTER_SANITIZE_SPECIAL_CHARS);
$empr_contato_1 = filter_input(INPUT_POST, 'empr_contato_1', FILTER_SANITIZE_SPECIAL_CHARS);
$empr_contato_2 = filter_input(INPUT_POST, 'empr_contato_2', FILTER_SANITIZE_SPECIAL_CHARS);
$empr_cidade = filter_input(INPUT_POST, 'empr_cidade', FILTER_SANITIZE_SPECIAL_CHARS);
$empr_endereco = filter_input(INPUT_POST, 'empr_endereco', FILTER_SANITIZE_SPECIAL_CHARS);

// Define como NULL se o campo opcional estiver vazio
if (empty($empr_contato_2)) {
    $empr_contato_2 = null; 
}

// Query com Prepared Statement usando o placeholder (?)
$sql = "INSERT INTO empresas (empr_nome, empr_cnpj, empr_tipo, empr_contato_1, empr_contato_2, empr_cidade, empr_endereco) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

try {
    $stmt = $conexao->prepare($sql);
    
    // Executa a query passando os valores em um array para o PDO
    $execucao = $stmt->execute([
        $empr_nome, 
        $empr_cnpj, 
        $empr_tipo,
        $empr_contato_1,
        $empr_contato_2,
        $empr_cidade,
        $empr_endereco
    ]);
    
    if ($execucao) {
        // Sucesso
        header("location: " . BASE_URL . "admin");
        exit();
    } else {
        // Falha na execução que não lançou exceção (Fallback)
        header("location: " . BASE_URL . "error.php?aviso=Erro ao adicionar empresa: Falha inesperada na execução.");
        exit();
    }
} catch (PDOException $e) {
    // Tratamento de erro seguro: registra o erro (log) e mostra mensagem genérica
    error_log("Erro PDO ao adicionar empresa: " . $e->getMessage());
    $aviso = "Erro interno ao adicionar empresa. Tente novamente mais tarde.";
    header("location: " . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}
?>