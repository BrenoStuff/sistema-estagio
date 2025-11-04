<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema-estagio');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // String de conexão DSN (Data Source Name)
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    // Opções de PDO
    $opcoes = [
        // Lança exceções em caso de erros SQL
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
        // Desativa a emulação de prepared statements (melhora a segurança)
        PDO::ATTR_EMULATE_PREPARES   => false,
        // Define o modo de retorno padrão como array associativo
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
    ];

    // Conexão
    $conexao = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
    
} catch (PDOException $e) {
    // Registrar o erro de forma segura (apenas para o log do servidor)
    error_log("Erro de conexão com o banco de dados PDO: " . $e->getMessage());
    
    // Redireciona com uma mensagem genérica para o usuário
    $aviso = "Erro de conexão com o banco de dados. Tente mais tarde.";
    header("Location: ../error.php?aviso=$aviso");
    exit();
}

// A variável $conexao agora é o objeto PDO que será usado nas outras páginas.
?>