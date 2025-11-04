<?php
// Configurações da Página
require '../config.php';
require '../backend/auth/verifica.php';
$title = SIS_NAME . ' - Area do Administrador'; // Alterado o título para "Area do Administrador"
$navActive = 'home';

// Verifica se é admin
// verifica_acesso('admin');

// 
// BANCO DE DADOS (Migrado para PDO)
//
require_once '../backend/helpers/db-connect.php';

try {
    // Usuario (Corrigido SQL Injection)
    $sql = "SELECT user_nome FROM usuarios WHERE user_id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$_SESSION['usuario']]);
    $usuario = $stmt->fetch();
    if (!$usuario) {
        $usuario = ['user_nome' => 'Desconhecido']; // Fallback
    }

    // Cursos
    $sql_cursos = "SELECT * FROM cursos";
    $cursos = $conexao->query($sql_cursos)->fetchAll();

    // Empresas
    $sql_empresas = "SELECT * FROM empresas";
    $empresas = $conexao->query($sql_empresas)->fetchAll();

    // Tabela tudo
    // Query modificada para pegar todos os dados das tabelas relacionadas
    $sql_tabela = "SELECT * FROM contratos
                   JOIN empresas ON cntr_id_empresa = empr_id
                   JOIN usuarios ON cntr_id_usuario = user_id
                   JOIN cursos ON user_id_curs = curs_id";
    $tabela_tudo = $conexao->query($sql_tabela)->fetchAll();

    // Alunos estagiando
    $sql_estagiando = "SELECT user_id FROM usuarios
                       JOIN contratos ON usuarios.user_id = contratos.cntr_id_usuario
                       WHERE contratos.cntr_ativo = ? AND usuarios.user_acesso = ?";
    $stmt_estagiando = $conexao->prepare($sql_estagiando);
    $stmt_estagiando->execute([1, 'aluno']);
    $alunos_estagiando = $stmt_estagiando->fetchAll();

    // Alunos sem estágio
    $sql_nao_estagiando = "SELECT user_id, user_nome FROM usuarios
                           WHERE user_acesso = ? AND user_id NOT IN (SELECT cntr_id_usuario FROM contratos WHERE cntr_ativo = ?)";
    $stmt_nao_estagiando = $conexao->prepare($sql_nao_estagiando);
    $stmt_nao_estagiando->execute(['aluno', 1]);
    $alunos_nao_estagiando = $stmt_nao_estagiando->fetchAll();

    // Relatórios inicial esperando aprovação
    $sql_rini = "SELECT * FROM contratos
                 JOIN relatorio_inicial ON cntr_id_relatorio_inicial = rini_id
                 JOIN usuarios ON cntr_id_usuario = user_id
                 JOIN empresas ON cntr_id_empresa = empr_id
                 WHERE rini_assinatura IS NOT NULL AND rini_aprovado = ?";
    $stmt_rini = $conexao->prepare($sql_rini);
    $stmt_rini->execute([0]);
    $relatorio_ini_esperando = $stmt_rini->fetchAll();

    // Relatórios final esperando aprovação
    $sql_rfin = "SELECT * FROM contratos
                 JOIN relatorio_final ON cntr_id_relatorio_final = rfin_id
                 JOIN usuarios ON cntr_id_usuario = user_id
                 JOIN empresas ON cntr_id_empresa = empr_id
                 WHERE rfin_assinatura IS NOT NULL AND rfin_aprovado = ?";
    $stmt_rfin = $conexao->prepare($sql_rfin);
    $stmt_rfin->execute([0]);
    $relatorio_fin_esperando = $stmt_rfin->fetchAll();

    // Contratos finalizados
    $sql_finalizados = "SELECT cntr_id FROM contratos
                        JOIN empresas ON cntr_id_empresa = empr_id
                        JOIN usuarios ON cntr_id_usuario = user_id
                        WHERE cntr_ativo = ?";
    $stmt_finalizados = $conexao->prepare($sql_finalizados);
    $stmt_finalizados->execute([0]);
    $contratos_finalizados = $stmt_finalizados->fetchAll();

} catch (PDOException $e) {
    // Tratamento de erro seguro
    error_log("Erro PDO no Admin Dashboard: " . $e->getMessage());
    die("Erro ao carregar dados do painel. Contate o administrador.");
}

// Helper para escapar HTML (Prevenção de XSS)
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<?php require '../components/head.php'; ?>
<body>
    <?php require '../components/navbar.php'; ?>

    <div class="container-fluid p-4 p-md-5">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-tachometer-alt"></i> Dashboard Administrativo</h1>
            <p class="text-muted d-none d-md-block">Bem-vindo, <?php echo h($usuario['user_nome']); ?>!</p>
        </div>
    </div>

    <div class="container-fluid px-md-5" id="info-cards">
        <div class="row">

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                    Alunos Estagiando
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($alunos_estagiando); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                    Alunos Sem Estágio
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($alunos_nao_estagiando); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-slash fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                    Relatórios Pendentes
                                </div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                            <?php echo count($relatorio_ini_esperando) + count($relatorio_fin_esperando); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-signature fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                    Contratos Finalizados
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($contratos_finalizados); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-md-5 mt-4">
        <div class="accordion" id="adminAccordion">

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingRelatorios">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRelatorios" aria-expanded="true" aria-controls="collapseRelatorios">
                        <i class="fas fa-file-alt me-2"></i> Relatórios Pendentes
                        <span class="badge bg-warning text-dark ms-2"><?php echo count($relatorio_ini_esperando) + count($relatorio_fin_esperando); ?></span>
                    </button>
                </h2>
                <div id="collapseRelatorios" class="accordion-collapse collapse" aria-labelledby="headingRelatorios" data-bs-parent="#adminAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="card shadow-sm">
                                    <div class="card-header py-3">
                                        <h2 class="h5 m-0 font-weight-bold text-primary"><i class="fas fa-file-alt"></i> Iniciais</h2>
                                    </div>
                                    <div class="card-body">
                                        <?php if (count($relatorio_ini_esperando) > 0) { ?>
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover small">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Aluno</th>
                                                            <th>Empresa</th>
                                                            <th>Ações</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($relatorio_ini_esperando as $row) { ?>
                                                            <tr>
                                                                <td><?php echo h($row['rini_id']); ?></td>
                                                                <td><?php echo h($row['user_nome']); ?></td>
                                                                <td><?php echo h($row['empr_nome']); ?></td>
                                                                <td>
                                                                    <a href="<?php echo BASE_URL . h($row['rini_assinatura']); ?>" target="_blank" class="btn btn-sm btn-info text-white" title="Ver Relatório"><i class="fas fa-external-link-alt"></i></a>
                                                                    <form action="../backend/relatorio-inicial/excluir-pdf.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este relatório?');" class="d-inline me-1">
                                                                        <input type="hidden" name="rini_id" value="<?php echo h($row['rini_id']); ?>">
                                                                        <button type="submit" class="btn btn-sm btn-danger" title="Excluir Relatório"><i class="fas fa-trash-alt"></i></button>
                                                                    </form>
                                                                    <form action="../backend/relatorio-inicial/aprovar.php" method="POST" class="d-inline">
                                                                        <input type="hidden" name="rini_id" value="<?php echo h($row['rini_id']); ?>">
                                                                        <button type="submit" class="btn btn-sm btn-success" title="Aprovar"><i class="fas fa-check"></i></button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } else { ?>
                                            <div class="alert alert-info" role="alert"><i class="fas fa-info-circle"></i> Nenhum relatório inicial pendente.</div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="card shadow-sm">
                                    <div class="card-header py-3">
                                        <h2 class="h5 m-0 font-weight-bold text-primary"><i class="fas fa-file-medical"></i> Finais</h2>
                                    </div>
                                    <div class="card-body">
                                        <?php if (count($relatorio_fin_esperando) > 0) { ?>
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover small">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Aluno</th>
                                                            <th>Empresa</th>
                                                            <th>Ações</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($relatorio_fin_esperando as $row) { ?>
                                                            <tr>
                                                                <td><?php echo h($row['rfin_id']); ?></td>
                                                                <td><?php echo h($row['user_nome']); ?></td>
                                                                <td><?php echo h($row['empr_nome']); ?></td>
                                                                <td>
                                                                    <a href="<?php echo BASE_URL . h($row['rfin_assinatura']); ?>" target="_blank" class="btn btn-sm btn-info text-white" title="Ver Relatório"><i class="fas fa-external-link-alt"></i></a>
                                                                    <form action="../backend/relatorio-final/excluir-pdf.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este relatório?');" class="d-inline me-1">
                                                                        <input type="hidden" name="rfin_id" value="<?php echo h($row['rfin_id']); ?>">
                                                                        <button type="submit" class="btn btn-sm btn-danger" title="Excluir Relatório"><i class="fas fa-trash-alt"></i></button>
                                                                    </form>
                                                                    <form action="../backend/relatorio-final/aprovar.php" method="POST" class="d-inline">
                                                                        <input type="hidden" name="rfin_id" value="<?php echo h($row['rfin_id']); ?>">
                                                                        <button type="submit" class="btn btn-sm btn-success" title="Aprovar"><i class="fas fa-check"></i></button>
                                                                    </form>
                                                                </td>   
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } else { ?>
                                            <div class="alert alert-info" role="alert"><i class="fas fa-info-circle"></i> Nenhum relatório final pendente.</div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingAcoes">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAcoes" aria-expanded="false" aria-controls="collapseAcoes">
                        <i class="fas fa-plus-circle me-2"></i> Cadastros Rápidos (Adicionar)
                    </button>
                </h2>
                <div id="collapseAcoes" class="accordion-collapse collapse" aria-labelledby="headingAcoes" data-bs-parent="#adminAccordion">
                    <div class="accordion-body">
                        <div class="card shadow-sm p-3">
                            <div class="d-flex flex-wrap justify-content-start">
                                <button class="btn btn-primary me-2 mb-2 d-inline-flex align-items-center justify-content-center" style="width: 200px;" data-bs-toggle="modal" data-bs-target="#addContratoModal">
                                    <i class="fas fa-file-contract me-2"></i> Contrato
                                </button>
                                <button class="btn btn-primary me-2 mb-2 d-inline-flex align-items-center justify-content-center" style="width: 200px;" data-bs-toggle="modal" data-bs-target="#addEmpresaModal">
                                    <i class="fas fa-building me-2"></i> Empresa
                                </button>
                                <button class="btn btn-primary me-2 mb-2 d-inline-flex align-items-center justify-content-center" style="width: 200px;" data-bs-toggle="modal" data-bs-target="#addCursoModal">
                                    <i class="fas fa-graduation-cap me-2"></i> Curso
                                </button>
                                <button class="btn btn-secondary me-2 mb-2 d-inline-flex align-items-center justify-content-center" style="width: 200px;" data-bs-toggle="modal" data-bs-target="#addAlunoModal">
                                    <i class="fas fa-user-plus me-2"></i> Aluno
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-md-5 mt-4">
        <div class="accordion" id="adminAccordion2">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingContratos">
                    <button class="accordion-button collapse show" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContratos" aria-expanded="false" aria-controls="collapseContratos">
                        <i class="fas fa-table me-2"></i> Visão Geral dos Contratos
                    </button>
                </h2>
                <div id="collapseContratos" class="accordion-collapse collapse show" aria-labelledby="headingContratos" data-bs-parent="#adminAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <div class="card shadow-sm">
                                    <div class="card-header py-3">
                                        <h2 class="h5 m-0 font-weight-bold text-primary"><i class="fas fa-table"></i> Todos os Contratos</h2>
                                    </div>
                                    <div class="card-body">
                                        <?php if (count($tabela_tudo) > 0) { ?>
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Aluno</th>
                                                            <th>Empresa</th>
                                                            <th>Curso</th>
                                                            <th>Status</th>
                                                            <th>Ações</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($tabela_tudo as $row) { ?>
                                                            <tr>
                                                                <td><?php echo h($row['cntr_id']); ?></td>
                                                                <td><?php echo h($row['user_nome']); ?></td>
                                                                <td><?php echo h($row['empr_nome']); ?></td>
                                                                <td><?php echo h($row['curs_nome']); ?></td>
                                                                <td>
                                                                    <?php 
                                                                        $status = $row['cntr_ativo'];
                                                                        $badge_class = $status ? 'bg-success' : 'bg-secondary';
                                                                        $badge_text = $status ? 'Ativo' : 'Finalizado';
                                                                    ?>
                                                                    <span class="badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
                                                                </td>
                                                                <td>
                                                                    <button type="button" class="btn btn-sm btn-info" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#contratoDetalhesModal"
                                                                        data-bs-id="<?php echo h($row['cntr_id']); ?>"
                                                                        data-bs-aluno-nome="<?php echo h($row['user_nome']); ?>"
                                                                        data-bs-aluno-ra="<?php echo h($row['user_ra']); ?>"
                                                                        data-bs-aluno-contato="<?php echo h($row['user_contato']); ?>"
                                                                        data-bs-curso-nome="<?php echo h($row['curs_nome']); ?>"
                                                                        data-bs-empresa-nome="<?php echo h($row['empr_nome']); ?>"
                                                                        data-bs-empresa-contato="<?php echo h($row['empr_contato_1']); ?>"
                                                                        data-bs-empresa-cidade="<?php echo h($row['empr_cidade']); ?>"
                                                                        data-bs-empresa-endereco="<?php echo h($row['empr_endereco']); ?>"
                                                                        data-bs-data-inicio="<?php echo date('d/m/Y', strtotime(h($row['cntr_data_inicio']))); ?>"
                                                                        data-bs-data-fim="<?php echo date('d/m/Y', strtotime(h($row['cntr_data_fim']))); ?>"
                                                                        data-bs-horario="<?php echo h($row['cntr_escala_horario']); ?>"
                                                                        data-bs-remunerado="<?php echo $row['cntr_remunerado'] ? 'Sim' : 'Não'; ?>"
                                                                        data-bs-status="<?php echo $badge_text; ?>"
                                                                        data-bs-link-termo="<?php echo BASE_URL . h($row['cntr_termo_contrato']); ?>"
                                                                        data-bs-link-anexo="<?php echo $row['cntr_anexo_extra'] ? BASE_URL . h($row['cntr_anexo_extra']) : ''; ?>"
                                                                    >
                                                                        <i class="fas fa-eye"></i> Ver Mais
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } else { ?>
                                            <div class="alert alert-warning" role="alert"><i class="fas fa-exclamation-triangle"></i> Nenhum contrato encontrado.</div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-md-5 mt-4">
        <div class="accordion" id="adminAccordion3">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingEmpresas">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEmpresas" aria-expanded="false" aria-controls="collapseEmpresas">
                        <i class="fas fa-building me-2"></i> Gerenciamento de Empresas
                    </button>
                </h2>
                <div id="collapseEmpresas" class="accordion-collapse collapse" aria-labelledby="headingEmpresas" data-bs-parent="#adminAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-12 mb-4">
                                <div class="card shadow-sm">
                                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                        <h2 class="h5 m-0 font-weight-bold text-primary">Empresas Cadastradas</h2>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEmpresaModal"><i class="fas fa-plus"></i> Adicionar Nova</button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                            <table class="table table-striped table-hover small">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Nome</th>
                                                        <th>Cidade</th>
                                                        <th>Ação</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($empresas as $empresa) { ?>
                                                    <tr>
                                                        <td><?php echo h($empresa['empr_id']); ?></td>
                                                        <td><?php echo h($empresa['empr_nome']); ?></td>
                                                        <td><?php echo h($empresa['empr_cidade']); ?></td>
                                                        <td>
                                                            <form action="../backend/empresas/deletar.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta empresa? Isso pode afetar contratos existentes.');" class="d-inline">
                                                                <input type="hidden" name="empr_id" value="<?php echo h($empresa['empr_id']); ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger" title="Deletar Empresa"><i class="fas fa-trash-alt"></i></button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingCursos">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCursos" aria-expanded="false" aria-controls="collapseCursos">
                        <i class="fas fa-graduation-cap me-2"></i> Gerenciamento de Cursos
                    </button>
                </h2>
                <div id="collapseCursos" class="accordion-collapse collapse" aria-labelledby="headingCursos" data-bs-parent="#adminAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-12 mb-4">
                                <div class="card shadow-sm">
                                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                        <h2 class="h5 m-0 font-weight-bold text-primary">Cursos Cadastrados</h2>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCursoModal"><i class="fas fa-plus"></i> Adicionar Novo</button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                            <table class="table table-striped table-hover small">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Nome</th>
                                                        <th>Ação</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($cursos as $curso) { ?>
                                                    <tr>
                                                        <td><?php echo h($curso['curs_id']); ?></td>
                                                        <td><?php echo h($curso['curs_nome']); ?></td>
                                                        <td>
                                                            <form action="../backend/cursos/deletar.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este curso? Isso pode afetar alunos e contratos existentes.');" class="d-inline">
                                                                <input type="hidden" name="curs_id" value="<?php echo h($curso['curs_id']); ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger" title="Deletar Curso"><i class="fas fa-trash-alt"></i></button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div> </div>

    <div class="modal fade" id="addContratoModal" tabindex="-1" aria-labelledby="addContratoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addContratoModalLabel">Adicionar Novo Contrato</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="../backend/contratos/create.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="cntr_id_usuario" class="form-label">Aluno</label>
                            <select class="form-select" name="cntr_id_usuario" required>
                                <option value="">Selecione um aluno</option>
                                <?php foreach ($alunos_nao_estagiando as $aluno) { ?>
                                    <option value="<?php echo h($aluno['user_id']); ?>"><?php echo h($aluno['user_nome']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="cntr_id_empresa" class="form-label">Empresa</label>
                            <select class="form-select" name="cntr_id_empresa" required>
                                <option value="">Selecione uma empresa</option>
                                <?php foreach ($empresas as $empresa) { ?>
                                    <option value="<?php echo h($empresa['empr_id']); ?>"><?php echo h($empresa['empr_nome']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="cntr_data_inicio" class="form-label">Data de Início</label>
                            <input type="date" class="form-control" name="cntr_data_inicio" required>
                        </div>
                        <div class="mb-3">
                            <label for="cntr_data_fim" class="form-label">Data de Fim</label>
                            <input type="date" class="form-control" name="cntr_data_fim" required>
                        </div>
                        <div class="mb-3">
                            <label for="cntr_escala_horario" class="form-label">Escala de Horário</label>
                            <input type="text" class="form-control" name="cntr_escala_horario" placeholder="Ex: 12h às 18h" required>
                        </div>
                        <div class="mb-3">
                            <label for="cntr_termo_contrato" class="form-label">Termo de Contrato</label>
                            <input type="file" class="form-control" name="cntr_termo_contrato" accept=".pdf" required>
                        </div>
                        <div class="mb-3">
                            <label for="cntr_anexo_extra" class="form-label">Anexo Extra (opcional)</label>
                            <input type="file" class="form-control" name="cntr_anexo_extra" accept=".pdf">
                        </div>
                        <div class="mb-3">
                            <label for="cntr_remunerado" class="form-label">Remunerado</label>
                            <select class="form-select" name="cntr_remunerado" required>
                                <option value="1">Sim</option>
                                <option value="0">Não</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="cntr_ativo" class="form-label">Ativo</label>
                            <select class="form-select" name="cntr_ativo" required>
                                <option value="1">Sim</option>
                                <option value="0">Não</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Adicionar Contrato</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> 
    </div>

    <div class="modal fade" id="addEmpresaModal" tabindex="-1" aria-labelledby="addEmpresaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEmpresaModalLabel">Adicionar Nova Empresa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="../backend/empresas/adicionar.php" method="POST">
                        <div class="mb-3">
                            <label for="empr_nome" class="form-label">Nome da Empresa</label>
                            <input type="text" class="form-control" name="empr_nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="empr_contato_1" class="form-label">Contato 1</label>
                            <input type="text" class="form-control" name="empr_contato_1" required>
                        </div>
                        <div class="mb-3">
                            <label for="empr_contato_2" class="form-label">Contato 2 (opcional)</label>
                            <input type="text" class="form-control" name="empr_contato_2">
                        </div>
                        <div class="mb-3">
                            <label for="empr_cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" name="empr_cidade" required>
                        </div>
                        <div class="mb-3">
                            <label for="empr_endereco" class="form-label">Endereço</label>
                            <input type="text" class="form-control" name="empr_endereco" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Adicionar Empresa</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCursoModal" tabindex="-1" aria-labelledby="addCursoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCursoModalLabel">Adicionar Novo Curso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="../backend/cursos/adicionar.php" method="POST">
                        <div class="mb-3">
                            <label for="curs_nome" class="form-label">Nome do Curso</label>
                            <input type="text" class="form-control" name="curs_nome" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Adicionar Curso</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addAlunoModal" tabindex="-1" aria-labelledby="addAlunoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAlunoModalLabel">Adicionar Novo Aluno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                </div>
                <div class="modal-body">
                    <form action="../backend/usuario/adicionar.php" method="POST">
                        <div class="mb-3">
                            <label for="user_nome" class="form-label">Nome do Aluno</label>
                            <input type="text" class="form-control" name="user_nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="user_login" class="form-label">Login</label>
                            <input type="text" class="form-control" name="user_login" required>
                        </div>
                        <div class="mb-3">
                            <label for="user_senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" name="user_senha" required>
                        </div>
                        <div class="mb-3">
                            <label for="user_id_curs" class="form-label">Curso</label>
                            <select class="form-select" name="user_id_curs" required>
                                <option value="">Selecione um curso</option>
                                <?php foreach ($cursos as $curso) { ?>
                                    <option value="<?php echo h($curso['curs_id']); ?>"><?php echo h($curso['curs_nome']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="user_ra" class="form-label">RA</label>
                            <input type="text" class="form-control" name="user_ra" required>
                        </div>
                        <div class="mb-3">
                            <label for="user_contato" class="form-label">Contato</label>
                            <input type="text" class="form-control" name="user_contato" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Adicionar Aluno</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="contratoDetalhesModal" tabindex="-1" aria-labelledby="contratoDetalhesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contratoDetalhesModalLabel">Detalhes do Contrato (ID: <span id="modal-contrato-id"></span>)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4><i class="fas fa-user-graduate"></i> Aluno</h4>
                            <dl class="row">
                                <dt class="col-sm-4">Nome:</dt>
                                <dd class="col-sm-8" id="modal-aluno-nome"></dd>
                                
                                <dt class="col-sm-4">RA:</dt>
                                <dd class="col-sm-8" id="modal-aluno-ra"></dd>
                                
                                <dt class="col-sm-4">Contato:</dt>
                                <dd class="col-sm-8" id="modal-aluno-contato"></dd>
                                
                                <dt class="col-sm-4">Curso:</dt>
                                <dd class="col-sm-8" id="modal-curso-nome"></dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <h4><i class="fas fa-building"></i> Empresa</h4>
                            <dl class="row">
                                <dt class="col-sm-4">Nome:</dt>
                                <dd class="col-sm-8" id="modal-empresa-nome"></dd>
                                
                                <dt class="col-sm-4">Contato:</dt>
                                <dd class="col-sm-8" id="modal-empresa-contato"></dd>
                                
                                <dt class="col-sm-4">Cidade:</dt>
                                <dd class="col-sm-8" id="modal-empresa-cidade"></dd>
                                
                                <dt class="col-sm-4">Endereço:</dt>
                                <dd class="col-sm-8" id="modal-empresa-endereco"></dd>
                            </dl>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h4><i class="fas fa-file-contract"></i> Contrato</h4>
                    <dl class="row">
                        <dt class="col-sm-3">Data Início:</dt>
                        <dd class="col-sm-3" id="modal-data-inicio"></dd>
                        
                        <dt class="col-sm-3">Data Fim:</dt>
                        <dd class="col-sm-3" id="modal-data-fim"></dd>
                        
                        <dt class="col-sm-3">Horário:</dt>
                        <dd class="col-sm-3" id="modal-horario"></dd>
                        
                        <dt class="col-sm-3">Status:</dt>
                        <dd class="col-sm-3" id="modal-status"></dd>
                        
                        <dt class="col-sm-3">Remunerado:</dt>
                        <dd class="col-sm-3" id="modal-remunerado"></dd>
                    </dl>
                    
                    <h4><i class="fas fa-folder-open"></i> Documentos</h4>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <a href="#" id="modal-link-termo" target="_blank" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Ver Termo de Contrato</a>
                        </li>
                        <li class="list-group-item" id="modal-anexo-item">
                            <a href="#" id="modal-link-anexo" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Ver Anexo Extra</a>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <?php require '../components/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var contratoModal = document.getElementById('contratoDetalhesModal');
        
        contratoModal.addEventListener('show.bs.modal', function (event) {
            // Botão que acionou o modal
            var button = event.relatedTarget;
            
            // Extrair dados dos atributos data-bs-*
            var id = button.getAttribute('data-bs-id');
            var alunoNome = button.getAttribute('data-bs-aluno-nome');
            var alunoRa = button.getAttribute('data-bs-aluno-ra');
            var alunoContato = button.getAttribute('data-bs-aluno-contato');
            var cursoNome = button.getAttribute('data-bs-curso-nome');
            var empresaNome = button.getAttribute('data-bs-empresa-nome');
            var empresaContato = button.getAttribute('data-bs-empresa-contato');
            var empresaCidade = button.getAttribute('data-bs-empresa-cidade');
            var empresaEndereco = button.getAttribute('data-bs-empresa-endereco');
            var dataInicio = button.getAttribute('data-bs-data-inicio');
            var dataFim = button.getAttribute('data-bs-data-fim');
            var horario = button.getAttribute('data-bs-horario');
            var remunerado = button.getAttribute('data-bs-remunerado');
            var status = button.getAttribute('data-bs-status');
            var linkTermo = button.getAttribute('data-bs-link-termo');
            var linkAnexo = button.getAttribute('data-bs-link-anexo');

            // Atualizar o conteúdo do modal
            contratoModal.querySelector('#modal-contrato-id').textContent = id;
            
            // Aluno
            contratoModal.querySelector('#modal-aluno-nome').textContent = alunoNome;
            contratoModal.querySelector('#modal-aluno-ra').textContent = alunoRa;
            contratoModal.querySelector('#modal-aluno-contato').textContent = alunoContato;
            contratoModal.querySelector('#modal-curso-nome').textContent = cursoNome;
            
            // Empresa
            contratoModal.querySelector('#modal-empresa-nome').textContent = empresaNome;
            contratoModal.querySelector('#modal-empresa-contato').textContent = empresaContato;
            contratoModal.querySelector('#modal-empresa-cidade').textContent = empresaCidade;
            contratoModal.querySelector('#modal-empresa-endereco').textContent = empresaEndereco;
            
            // Contrato
            contratoModal.querySelector('#modal-data-inicio').textContent = dataInicio;
            contratoModal.querySelector('#modal-data-fim').textContent = dataFim;
            contratoModal.querySelector('#modal-horario').textContent = horario;
            contratoModal.querySelector('#modal-status').textContent = status;
            contratoModal.querySelector('#modal-remunerado').textContent = remunerado;
            
            // Links
            contratoModal.querySelector('#modal-link-termo').href = linkTermo;
            
            var anexoItem = contratoModal.querySelector('#modal-anexo-item');
            var anexoLink = contratoModal.querySelector('#modal-link-anexo');
            
            if (linkAnexo) {
                anexoLink.href = linkAnexo;
                anexoItem.style.display = 'block'; // Mostra o item
            } else {
                anexoItem.style.display = 'none'; // Esconde o item se não houver anexo
            }
        });
    });
    </script>

</body>
</html>