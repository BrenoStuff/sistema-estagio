<?php
include_once '../../config.php';
include_once '../auth/verifica.php';
include_once '../helpers/db-connect.php';
include_once '../helpers/format.php';

// 1. Filtragem da entrada (Segurança)
$contrato_id = filter_input(INPUT_GET, 'cntr_id', FILTER_VALIDATE_INT);

if (!$contrato_id) {
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
            JOIN relatorio_inicial ON rini_id = cntr_id_relatorio_inicial
            WHERE cntr_id = ?"; // Placeholder

    $stmt = $conexao->prepare($sql);
    $stmt->execute([$contrato_id]);
    
    // Substitui fetch_assoc() e num_rows > 0
    $relatorio = $stmt->fetch(); 

    // 3. Verifica se o relatório foi encontrado
    if (!$relatorio) {
        $aviso = "Relatório não encontrado!";
        header("Location: ../../error.php?aviso=$aviso");
        exit();
    }

    // 4. Query Secundária (Atividades) com Prepared Statement
    $sql_atv = "SELECT * FROM atv_estagio_ini WHERE atvi_id_relatorio_ini = ?";
    
    $stmt_atv = $conexao->prepare($sql_atv);
    $stmt_atv->execute([$relatorio['rini_id']]); // Usa o ID seguro da query anterior

    $atividades = $stmt_atv->fetchAll(); // Substitui fetch_all(MYSQLI_ASSOC)

} catch (PDOException $e) {
    // Tratamento de erro seguro
    error_log("Erro PDO ao imprimir relatório inicial: " . $e->getMessage());
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
    <h1>Relatório Inicial</h1>
    <h2>Aluno: <?php echo htmlspecialchars($aluno, ENT_QUOTES, 'UTF-8'); ?></h2>
    <h2>Empresa: <?php echo htmlspecialchars($empresa, ENT_QUOTES, 'UTF-8'); ?></h2>
    <h2>Curso: <?php echo htmlspecialchars($curso, ENT_QUOTES, 'UTF-8'); ?></h2>

    <h3>Conteúdo do Relatório:</h3>
    <p><?php echo htmlspecialchars($relatorio['rini_como_ocorreu'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p><?php echo htmlspecialchars($relatorio['rini_dev_cronograma'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p><?php echo htmlspecialchars($relatorio['rini_preparacao_inicio'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p><?php echo htmlspecialchars($relatorio['rini_dificul_encontradas'], ENT_QUOTES, 'UTF-8'); ?></p>
    
    <h3> Atividades desenvolvidas:</h3>
    <?php foreach ($atividades as $atividade) { ?>
        <p>• 
            <?php echo htmlspecialchars($atividade['atvi_atividade'], ENT_QUOTES, 'UTF-8'); ?> - 
            <?php echo htmlspecialchars($atividade['atvi_comentario'], ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php } ?>
    
    <p><?php echo htmlspecialchars($relatorio['rini_aplic_conhecimento'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p><?php echo htmlspecialchars($relatorio['rini_novas_ferramentas'], ENT_QUOTES, 'UTF-8'); ?></p>
    
    <?php if (isset($relatorio['rini_comentarios'])) { ?>
        <p><?php echo htmlspecialchars($relatorio['rini_comentarios'], ENT_QUOTES, 'UTF-8'); ?></p>
    <?php } ?>
    
    <?php if (!empty($relatorio['rini_anexo_1'])) { ?>
        <p><a href="<?php echo BASE_URL . $relatorio['rini_anexo_1']; ?>" target="_blank">Anexo 1</a></p>
    <?php } ?>
    
    <?php if (!empty($relatorio['rini_anexo_2'])) { ?>
        <p><a href="<?php echo BASE_URL . $relatorio['rini_anexo_2']; ?>" target="_blank">Anexo 2</a></p>
    <?php } ?>
    
    <?php if ($relatorio['rini_aprovado'] == 1) { ?>
        <p>Status: Aprovado</p>
    <?php } else { ?>
        <p>Status: Aguardando aprovação</p>
    <?php } ?>

    <a href="../../" class="btn btn-primary">Voltar</a>

    <script src="../../js/bootstrap.js"></script>
    <script src="../../js/script.js"></script>
</body>
</html>