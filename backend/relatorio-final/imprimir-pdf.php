<?php
include_once '../../config.php';
include_once '../auth/verifica.php';
include_once '../helpers/db-connect.php';
include_once '../helpers/format.php';
include_once '../helpers/save-file.php'; // Este include não parece ser usado aqui

// 1. Filtragem da entrada
$cntr_id = filter_input(INPUT_GET, 'cntr_id', FILTER_VALIDATE_INT);

if (!$cntr_id) {
    $aviso = "ID de contrato inválido.";
    header("Location: ../../error.php?aviso=$aviso");
    exit();
}

try {
    // 2. Query Principal (Relatório) com Prepared Statement
    $sql = "SELECT * FROM contratos
            JOIN empresas ON cntr_id_empresa = empr_id
            JOIN usuarios ON cntr_id_usuario = user_id
            JOIN cursos ON user_id_curs = curs_id
            JOIN relatorio_final ON rfin_id = cntr_id_relatorio_final
            WHERE cntr_id = ?"; // Placeholder

    $stmt = $conexao->prepare($sql);
    $stmt->execute([$cntr_id]);

    $relatorio = $stmt->fetch(); // Substitui fetch_assoc()

    // 3. Verifica se o relatório foi encontrado
    if (!$relatorio) {
        $aviso = "Relatório não encontrado!";
        header("Location: ../../error.php?aviso=$aviso");
        exit();
    }

    // 4. Query Secundária (Atividades) com Prepared Statement
    $sql_atv = "SELECT * FROM atv_estagio_fin WHERE atvf_id_relatorio_fin = ?";
    
    $stmt_atv = $conexao->prepare($sql_atv);
    $stmt_atv->execute([$relatorio['rfin_id']]); // Usa o ID seguro da query anterior

    $atividades = $stmt_atv->fetchAll(); // Substitui fetch_all(MYSQLI_ASSOC)

} catch (PDOException $e) {
    // Tratamento de erro seguro
    error_log("Erro PDO ao imprimir relatório final: " . $e->getMessage());
    $aviso = "Erro interno ao buscar dados do relatório. Tente novamente mais tarde.";
    header("Location: ../../error.php?aviso=" . urlencode($aviso));
    exit();
}

// 5. Prepara dados para o HTML
$aluno = $relatorio['user_nome'];
$empresa = $relatorio['empr_nome'];
$curso = $relatorio['curs_nome'];

require_once '../../components/head.php';
?>
<body>
    <h1>Relatório Final</h1>
    <h2>Aluno: <?php echo htmlspecialchars($aluno, ENT_QUOTES, 'UTF-8'); ?></h2>
    <h2>Empresa: <?php echo htmlspecialchars($empresa, ENT_QUOTES, 'UTF-8'); ?></h2>
    <h2>Curso: <?php echo htmlspecialchars($curso, ENT_QUOTES, 'UTF-8'); ?></h2>

    <h3>Conteúdo do Relatório:</h3>
    <p><?php echo htmlspecialchars($relatorio['rfin_sintese_empresa'], ENT_QUOTES, 'UTF-8'); ?></p>
    
    <h3>Atividades desenvolvidas:</h3>
    <?php foreach ($atividades as $atividade) { ?>
        <p>• 
            <?php echo htmlspecialchars($atividade['atvf_atividade'], ENT_QUOTES, 'UTF-8'); ?> - 
            <?php echo htmlspecialchars($atividade['atvf_resumo'], ENT_QUOTES, 'UTF-8'); ?> - 
            <?php echo htmlspecialchars($atividade['atvf_disciplina_relacionada'], ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php } ?>

    <?php if ($relatorio['rfin_anexo_1'] != null) { ?>
        <p><a href="<?php echo BASE_URL . $relatorio['rfin_anexo_1']; ?>" target="_blank">Anexo 1</a></p>
    <?php } ?>

    <?php if ($relatorio['rfin_anexo_2'] != null) { ?>
        <p><a href="<?php echo BASE_URL . $relatorio['rfin_anexo_2']; ?>" target="_blank">Anexo 2</a></p>
    <?php } ?>

    <p><a href="javascript:window.print()">Imprimir</a></p>
    <p><a href="<?php echo BASE_URL ?>">Voltar</a></p>
    
</body>
</html>