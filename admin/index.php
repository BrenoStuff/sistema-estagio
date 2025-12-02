<?php
// Configurações da Página
require '../config.php';
require '../backend/auth/verifica.php';
$title = SIS_NAME . ' - Area do Administrador';
$navActive = 'home';

// Verifica se o usuário é admin
if ($_SESSION['acesso'] !== 'admin') {
    header("Location:" . BASE_URL . "error.php?aviso=" . urlencode("Acesso negado. Área restrita ao administrador."));
    exit();
}

require_once '../backend/helpers/db-connect.php';

try {
    // Usuario 
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
    error_log("Erro PDO no Admin Dashboard: " . $e->getMessage());
    die("Erro ao carregar dados do painel. Contate o administrador.");
}

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
                                <div class="text-xs font-weight-bold text-white text-uppercase mb-1">Alunos Estagiando</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($alunos_estagiando); ?></div>
                            </div>
                            <div class="col-auto"><i class="fas fa-user-tie fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-white text-uppercase mb-1">Alunos Sem Estágio</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($alunos_nao_estagiando); ?></div>
                            </div>
                            <div class="col-auto"><i class="fas fa-user-slash fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-white text-uppercase mb-1">Relatórios Pendentes</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($relatorio_ini_esperando) + count($relatorio_fin_esperando); ?></div>
                            </div>
                            <div class="col-auto"><i class="fas fa-file-signature fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-white text-uppercase mb-1">Contratos Finalizados</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($contratos_finalizados); ?></div>
                            </div>
                            <div class="col-auto"><i class="fas fa-clipboard-check fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-md-5 mt-4">
        
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h4 mb-0 text-gray-800"><i class="fas fa-file-alt me-2"></i> Relatórios Pendentes</h2>
            <span class="badge bg-warning text-dark fs-6"><?php echo count($relatorio_ini_esperando) + count($relatorio_fin_esperando); ?> Pendentes</span>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
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
                                                    <form action="../backend/relatorio-inicial/reprovar.php" method="POST" onsubmit="return confirm('Tem certeza que deseja reprovar este relatório?');" class="d-inline me-1">
                                                        <input type="hidden" name="rini_id" value="<?php echo h($row['rini_id']); ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Reprovar Relatório"><i class="fas fa-times"></i></button>
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
                <div class="card shadow-sm h-100">
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
                                                    <form action="../backend/relatorio-final/reprovar.php" method="POST" onsubmit="return confirm('Tem certeza que deseja reprovar este relatório?');" class="d-inline me-1">
                                                        <input type="hidden" name="rfin_id" value="<?php echo h($row['rfin_id']); ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Reprovar Relatório"><i class="fas fa-times"></i></button>
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

    <?php require '../components/footer.php'; ?>

</body>
</html>