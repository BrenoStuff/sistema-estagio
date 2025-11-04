<?php
require_once '../../config.php';
include_once '../helpers/db-connect.php';

// ------------------------------------------------------------------
// 1. Limpeza e filtragem dos dados de entrada
// ------------------------------------------------------------------
$user_nome = filter_input(INPUT_POST, 'user_nome', FILTER_SANITIZE_SPECIAL_CHARS);
$user_login = filter_input(INPUT_POST, 'user_login', FILTER_SANITIZE_SPECIAL_CHARS);
$user_senha_raw = $_POST['user_senha']; // Senha bruta
$user_curso = filter_input(INPUT_POST, 'user_id_curs', FILTER_VALIDATE_INT);
$user_ra = filter_input(INPUT_POST, 'user_ra', FILTER_SANITIZE_SPECIAL_CHARS);
$user_contato = filter_input(INPUT_POST, 'user_contato', FILTER_SANITIZE_SPECIAL_CHARS);
$user_tipo = "aluno"; // Tipo padrão para novos usuários

// ------------------------------------------------------------------
// 2. Segurança de Senha (CRÍTICO)
// ------------------------------------------------------------------
// Gera o hash seguro da senha. Nunca salve a senha bruta ($user_senha_raw).
$user_senha_hash = password_hash($user_senha_raw, PASSWORD_DEFAULT);

// ------------------------------------------------------------------
// 3. Conexão com Banco de Dados e Transações PDO
// ------------------------------------------------------------------

// Query com Prepared Statement usando placeholders '?'
$sql = "INSERT INTO usuarios (user_nome, user_login, user_senha, user_id_curs, user_ra, user_contato, user_acesso) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

try {
    $stmt = $conexao->prepare($sql);
    
    // Executa a query passando os valores em um array
    $execucao = $stmt->execute([
        $user_nome, 
        $user_login, 
        $user_senha_hash, // Salva o HASH da senha
        $user_curso, 
        $user_ra, 
        $user_contato, 
        $user_tipo
    ]);
    
    if ($execucao) {
        header("location: " . BASE_URL . "admin");
        exit();
    } else {
        header("location: " . BASE_URL . "error.php?aviso=Erro ao adicionar usuário: Falha inesperada.");
        exit();
    }
} catch (PDOException $e) {
    // Tratamento de erro seguro
    error_log("Erro PDO ao adicionar usuário: " . $e->getMessage());
    $aviso = "Erro interno ao adicionar usuário. Tente novamente mais tarde.";
    
    // Verifica se é um erro de duplicidade (ex: login já existe)
    if ($e->errorInfo[1] == 1062) {
        $aviso = "Erro ao adicionar usuário: O login ou RA informado já existe.";
    }
    
    header("location: " . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}
?>