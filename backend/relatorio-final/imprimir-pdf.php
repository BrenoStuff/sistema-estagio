<?php
include_once '../../config.php';
include_once '../auth/verifica.php';
include_once '../helpers/db-connect.php';
include_once '../helpers/format.php';

include_once '../helpers/save-file.php';

$cntr_id = $_GET['cntr_id'];

$sql = "SELECT * FROM contratos
    JOIN empresas ON cntr_id_empresa = empr_id
    JOIN usuarios ON cntr_id_usuario = user_id
    JOIN cursos ON user_id_curs = curs_id
    JOIN relatorio_final ON rfin_id = cntr_id_relatorio_final
    WHERE cntr_id = $cntr_id";

$dado = $conexao->query($sql);
if ($dado->num_rows > 0) {
    $relatorio = $dado->fetch_assoc();
} else {
    $aviso = "Relatório não encontrado!";
    header("Location: ../../error.php?aviso=$aviso");
    exit();
}

$sql = "SELECT * FROM atv_estagio_fin WHERE atvf_id_relatorio_fin = " . $relatorio['rfin_id'];
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
    <h1>Relatório Final</h1>
    <h2>Aluno: <?php echo $aluno; ?></h2>
    <h2>Empresa: <?php echo $empresa; ?></h2>
    <h2>Curso: <?php echo $curso; ?></h2>

    <h3>Conteúdo do Relatório:</h3>
    <p><?php echo $relatorio['rfin_sintese_empresa']; ?></p>
    
    <h3>Atividades desenvolvidas:</h3>
    <?php foreach ($atividades as $atividade) { ?>
        <p>• <?php echo $atividade['atvf_atividade']; ?> - <?php echo $atividade['atvf_resumo']; ?> - <?php echo $atividade['atvf_disciplina_relacionada']; ?></p>
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