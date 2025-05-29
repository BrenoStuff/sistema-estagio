<?php
include_once '../../config.php';
include_once '../auth/verifica.php';
include_once '../helpers/db-connect.php';
include_once '../helpers/format.php';

$contrato_id = $_GET['cntr_id'];


$sql = "SELECT * FROM contratos
    JOIN empresas ON cntr_id_empresa = empr_id
    JOIN usuarios ON cntr_id_usuario = user_id
    JOIN cursos ON user_id_curs = curs_id
    JOIN relatorio_inicial ON rini_id = cntr_id_relatorio_inicial
    WHERE cntr_id = $contrato_id";
$dado = $conexao->query($sql);
if ($dado->num_rows > 0) {
    $relatorio = $dado->fetch_assoc();
} else {
    $aviso = "Relatório não encontrado!";
    header("Location: ../../error.php?aviso=$aviso");
    exit();
}

$sql = "SELECT * FROM atv_estagio_ini WHERE atvi_id_relatorio_ini = " . $relatorio['rini_id'];
$dado = $conexao->query($sql);
if ($dado->num_rows > 0) {
    $atividades = $dado->fetch_all(MYSQLI_ASSOC);
} else {
    $atividades = [];
}

$aluno = $relatorio['user_nome'];
$empresa = $relatorio['empr_nome'];
$curso = $relatorio['curs_nome'];


require_once '../../components/head.php';
?>

<body>
    <h1>Relatório Inicial</h1>
    <h2>Aluno: <?php echo $aluno; ?></h2>
    <h2>Empresa: <?php echo $empresa; ?></h2>
    <h2>Curso: <?php echo $curso; ?></h2>

    <h3>Conteúdo do Relatório:</h3>
    <p><?php echo $relatorio['rini_como_ocorreu']; ?></p>
    <p><?php echo $relatorio['rini_dev_cronograma']; ?></p>
    <p><?php echo $relatorio['rini_preparacao_inicio']; ?></p>
    <p><?php echo $relatorio['rini_dificul_encontradas']; ?></p>
    <h3> Atividades desenvolvidas:</h3>
    <?php foreach ($atividades as $atividade) { ?>
        <p>• <?php echo $atividade['atvi_atividade']; ?> - <?php echo $atividade['atvi_comentario']; ?></p>
    <?php } ?>
    <p><?php echo $relatorio['rini_aplic_conhecimento']; ?></p>
    <p><?php echo $relatorio['rini_novas_ferramentas']; ?></p>
    <?php if (isset($relatorio['rini_comentarios'])) { ?>
        <p><?php echo $relatorio['rini_comentarios']; ?></p>
    <?php } ?>
    <?php if ($relatorio['rini_anexo_1'] != '') { ?>
        <p><a href="<?php echo BASE_URL . $relatorio['rini_anexo_1']; ?>" target="_blank">Anexo 1</a></p>
    <?php } ?>
    <?php if ($relatorio['rini_anexo_2'] != '') { ?>
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

