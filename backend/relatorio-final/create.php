<?php
include_once '../../config.php';
include_once '../auth/verifica.php';
include_once '../helpers/db-connect.php';
// include_once '../helpers/format.php'; // Removido se não estiver sendo usado
include_once '../helpers/save-file.php';

// ------------------------------------------------------------------
// Coleta e Limpeza dos Dados de Entrada
// ------------------------------------------------------------------

$cntr_id = filter_input(INPUT_POST, 'cntr_id', FILTER_VALIDATE_INT);
$rfin_sintese_empresa = filter_input(INPUT_POST, 'rfin_sintese_empresa', FILTER_DEFAULT);

// Validação básica
if (!$cntr_id || !$rfin_sintese_empresa) {
    $aviso = "Dados do formulário inválidos. Contrato ID ou Síntese estão faltando.";
    header("Location: " . BASE_URL . "error.php?aviso=" . urlencode($aviso));
    exit();
}

// ------------------------------------------------------------------
// Lógica de Anexos (File Upload)
// ------------------------------------------------------------------

// Inicializa variáveis de arquivo
$rfin_anexo_1 = (isset($_FILES['rfin_anexo_1']) && $_FILES['rfin_anexo_1']['error'] != UPLOAD_ERR_NO_FILE) ? $_FILES['rfin_anexo_1'] : null;
$rfin_anexo_2 = (isset($_FILES['rfin_anexo_2']) && $_FILES['rfin_anexo_2']['error'] != UPLOAD_ERR_NO_FILE) ? $_FILES['rfin_anexo_2'] : null;

// Diretórios de upload
$upload_dir = __DIR__ . '/../uploads/relatorio-anexos/'; // Caminho absoluto
$relative_dir = 'backend/uploads/relatorio-anexos/'; // Caminho relativo para o banco de dados
$relative_dir_safe = rtrim($relative_dir, '/\\') . DIRECTORY_SEPARATOR; // Garante o separador

$nome_base = 'relatorio_final_anexo_';

// Processamento do Anexo 1
$rfin_anexo_1_path = null;
if ($rfin_anexo_1 != null) {
    $resultado = uploadPDF($rfin_anexo_1, $upload_dir, $nome_base . '_1_' . $cntr_id);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    // Armazena o caminho relativo (do DB)
    $rfin_anexo_1_path = $relative_dir . $resultado['file_name'];
}

// Processamento do Anexo 2
$rfin_anexo_2_path = null;
if ($rfin_anexo_2 != null) {
    $resultado = uploadPDF($rfin_anexo_2, $upload_dir, $nome_base . '_2_' . $cntr_id);
    if (!$resultado['success']) {
        header("Location:" . BASE_URL . "error.php?aviso=" . urlencode($resultado['error']));
        exit();
    }
    // Armazena o caminho relativo (do DB)
    $rfin_anexo_2_path = $relative_dir . $resultado['file_name'];
}


// ------------------------------------------------------------------
// Coleta das Atividades de Estágio Final
// ------------------------------------------------------------------

$atividades = array();
$resumos = array();
$disciplina = array();

// O formulário (modal) deve enviar campos como atividade1_final, resumo1_final, etc.
for ($i = 1; $i <= 10; $i++) {
    $atividade_key = 'atividade' . $i . '_final';
    
    if (isset($_POST[$atividade_key]) && !empty($_POST[$atividade_key]) && trim($_POST[$atividade_key]) !== '') {
        
        $resumo_key = 'resumo' . $i . '_final';
        $disciplina_key = 'disciplina' . $i . '_final';

        // Filtra e armazena os dados
        $atividades[] = filter_input(INPUT_POST, $atividade_key, FILTER_DEFAULT);
        $resumos[] = filter_input(INPUT_POST, $resumo_key, FILTER_DEFAULT);
        $disciplina[] = filter_input(INPUT_POST, $disciplina_key, FILTER_DEFAULT);
    }
}


// ------------------------------------------------------------------
// Conexão com Banco de Dados e Transações PDO
// ------------------------------------------------------------------

try {
    // Inicia uma transação
    $conexao->beginTransaction(); 
    
    $sql_rfin = "INSERT INTO relatorio_final (rfin_sintese_empresa, rfin_anexo_1, rfin_anexo_2, rfin_aprovado)
                 VALUES (?, ?, ?, 0)"; // Inicia como não aprovado (0)
    
    $stmt_rfin = $conexao->prepare($sql_rfin);
    $stmt_rfin->execute([
        $rfin_sintese_empresa, 
        $rfin_anexo_1_path, 
        $rfin_anexo_2_path
    ]);

    // Obtém o ID do relatório final inserido
    $rfin_id = $conexao->lastInsertId();

    // INSERT na tabela atv_estagio_fin
    if (!empty($atividades)) {
        $sql_atv = "INSERT INTO atv_estagio_fin (atvf_atividade, atvf_resumo, atvf_disciplina_relacionada, atvf_id_relatorio_fin) 
                    VALUES (?, ?, ?, ?)";
        $stmt_atv = $conexao->prepare($sql_atv);

        foreach ($atividades as $key => $atividade) {
            $resumo = $resumos[$key] ?? null;
            $disciplina_atv = $disciplina[$key] ?? null;

            $stmt_atv->execute([$atividade, $resumo, $disciplina_atv, $rfin_id]);
        }
    }

    // UPDATE na tabela contratos
    $sql_update = "UPDATE contratos SET cntr_id_relatorio_final = ? WHERE cntr_id = ?";
    $stmt_update = $conexao->prepare($sql_update);
    $stmt_update->execute([$rfin_id, $cntr_id]);

    // Confirma a Transação (Aplica as mudanças no banco)
    $conexao->commit(); 

    // Sucesso: Redireciona
    header("Location: ../../index.php?aviso=Relatório final criado com sucesso!");
    exit();

} catch (PDOException $e) {
    // Em caso de qualquer erro, desfaz a transação
    if ($conexao->inTransaction()) {
        $conexao->rollBack();
    }

    // Tratamento de erro seguro: registra o erro (log) e mostra mensagem genérica
    error_log("Erro PDO ao criar relatório final: " . $e->getMessage());
    $aviso = "Erro interno ao inserir relatório final. Tente novamente mais tarde.";
    header("Location: ../../error.php?aviso=" . urlencode($aviso));
    exit();
}
?>