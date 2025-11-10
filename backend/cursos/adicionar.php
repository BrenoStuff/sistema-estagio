<?php
require_once '../../config.php';
require_once '../helpers/db-connect.php'; 

// -----------------------------------------------
//  Coleta e Limpeza dos dados de entrada
// -----------------------------------------------
$curs_nome = filter_input(INPUT_POST, 'curs_nome', FILTER_SANITIZE_SPECIAL_CHARS);

// -----------------------------------------------
//  Inserção no Banco de Dados
// -----------------------------------------------

$sql = "INSERT INTO cursos (curs_nome) VALUES (?)";

try {
    $stmt = $conexao->prepare($sql);
    
    // Executa a query passando os valores em um array.
    $execucao = $stmt->execute([$curs_nome]);
    
    if ($execucao) {
        // Sucesso
        header("location: " . BASE_URL . "admin");
        exit();
    } else {
        // Falha na execução que não lançou exceção (Fallback)
        header("location: " . BASE_URL . "error.php?aviso=Erro ao adicionar curso: Falha inesperada na execução.");
        exit();
    }
} catch (PDOException $e) {
    // Tratamento de erro
    error_log("Erro PDO ao adicionar curso: " . $e->getMessage());
    $aviso = "Erro interno ao adicionar curso. Tente novamente mais tarde.";
    header("location: " . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}
?>