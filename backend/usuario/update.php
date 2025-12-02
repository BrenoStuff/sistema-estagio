<?php
session_start();
require '../helpers/db-connect.php';

if (!isset($_SESSION['acesso']) || $_SESSION['acesso'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
$nome = filter_input(INPUT_POST, 'user_nome', FILTER_DEFAULT);
$ra = filter_input(INPUT_POST, 'user_ra', FILTER_SANITIZE_NUMBER_INT);
$contato = filter_input(INPUT_POST, 'user_contato', FILTER_DEFAULT);
$login = filter_input(INPUT_POST, 'user_login', FILTER_DEFAULT);
$curso_id = filter_input(INPUT_POST, 'user_id_curs', FILTER_SANITIZE_NUMBER_INT);
$nova_senha = filter_input(INPUT_POST, 'user_senha'); // Não sanitizar senha para não quebrar caracteres especiais permitidos

try {
    // Se digitou senha nova, atualiza tudo. Se não, atualiza exceto a senha.
    if (!empty($nova_senha)) {
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET 
                user_nome = :nome, 
                user_ra = :ra, 
                user_contato = :contato, 
                user_login = :login,
                user_id_curs = :curso,
                user_senha = :senha
                WHERE user_id = :id AND user_acesso = 'aluno'";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':senha', $senha_hash);
    } else {
        $sql = "UPDATE usuarios SET 
                user_nome = :nome, 
                user_ra = :ra, 
                user_contato = :contato, 
                user_login = :login,
                user_id_curs = :curso
                WHERE user_id = :id AND user_acesso = 'aluno'";
        $stmt = $conexao->prepare($sql);
    }
    
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':ra', $ra);
    $stmt->bindParam(':contato', $contato);
    $stmt->bindParam(':login', $login);
    $stmt->bindParam(':curso', $curso_id);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        header("Location: ../../admin/alunos.php?msg=atualizado");
    } else {
        header("Location: ../../admin/alunos.php?error=erro_update");
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    header("Location: ../../admin/alunos.php?error=erro_bd");
}
?>