<?php
// Configurações da Página
require '../config.php';
require '../backend/auth/verifica.php';
$title = SIS_NAME . ' - Area do Aluno';
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

    <section class="container-fluid py-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4">Dashboard</h1>
                <p>Bem vindo, <?php echo h($usuario['user_nome']); ?>!</p>
            </div>
        </div>
    </section>

    <section class="container-fluid" id="info-cards">
        <div class="row">

            <div class="col-md-3 mb-4">
                <div class="card text-white bg-primary h-100">
                    <div class="card-header">
                        <h5 class="card-title">Alunos estagiando</h5>
                    </div>
                    <div class="card-body">
                        <h3 class="fs-1"><?php echo count($alunos_estagiando); ?></h3>
                        <p class="card-text">Total de alunos atualmente estagiando.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card text-white bg-danger h-100">
                    <div class="card-header">
                        <h5 class="card-title">Alunos sem estágio</h5>
                    </div>
                    <div class="card-body">
                        <h3 class="fs-1"><?php echo count($alunos_nao_estagiando); ?></h3>
                        <p class="card-text">Alunos que ainda não estão estagiando.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card text-white bg-warning h-100">
                    <div class="card-header">
                        <h5 class="card-title">Relatórios pendentes</h5>
                    </div>
                    <div class="card-body">
                        <h3 class="fs-1">
                            <?php echo count($relatorio_ini_esperando) + count($relatorio_fin_esperando); ?>
                        </h3>
                        <p class="card-text">Total de relatórios pendentes de aprovação.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card text-white bg-success h-100">
                    <div class="card-header">
                        <h5 class="card-title">Contratos finalizados</h5>
                    </div>
                    <div class="card-body">
                        <h3 class="fs-1"><?php echo count($contratos_finalizados); ?></h3>
                        <p class="card-text">Total de contratos finalizados.</p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <section class="container-fluid mt-5">
        <div class="col-md-12">
            <h2> Cadastramento </h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContratoModal">Adicionar Contrato</button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmpresaModal">Adicionar Empresa</button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCursoModal">Adicionar Curso</button>
            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addAlunoModal">Adicionar Aluno</button>
            <button class="btn btn-secondary" id="secreto" onclick="window.location.href='quebra.php'">Secreto</button>

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
        </div>
    </section>

    <section class="container-fluid mt-5">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2>Relatórios Iniciais Pendentes</h2>
                <?php if (count($relatorio_ini_esperando) > 0) { ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Aluno</th>
                                <th>Empresa</th>
                                <th>Relatório</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($relatorio_ini_esperando as $row) { ?>
                                <tr>
                                    <td><?php echo h($row['rini_id']); ?></td>
                                    <td><?php echo h($row['user_nome']); ?></td>
                                    <td><?php echo h($row['empr_nome']); ?></td>
                                    <td><a href="<?php echo BASE_URL . h($row['rini_assinatura']); ?>" target="_blank">Ver Relatório</a></td>
                                    <td> Aguardando Aprovação</td>
                                    <td>
                                        <form action="../backend/relatorio-inicial/excluir-pdf.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este relatório?');" class="d-inline">
                                            <input type="hidden" name="rini_id" value="<?php echo h($row['rini_id']); ?>">
                                            <button type="submit" class="btn btn-danger">Excluir Relatório</button>
                                        </form>
                                        <form action="../backend/relatorio-inicial/aprovar.php" method="POST" class="d-inline">
                                            <input type="hidden" name="rini_id" value="<?php echo h($row['rini_id']); ?>">
                                            <button type="submit" class="btn btn-success">Aprovar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p>Nenhum relatório inicial pendente.</p>
                <?php } ?>
            </div>
        </div>
    </section>

    <section class="container-fluid mt-5">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2>Relatórios Finais Pendentes</h2>
                <?php if (count($relatorio_fin_esperando) > 0) { ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Aluno</th>
                                <th>Empresa</th>
                                <th>Relatório</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($relatorio_fin_esperando as $row) { ?>
                                <tr>
                                    <td><?php echo h($row['rfin_id']); ?></td>
                                    <td><?php echo h($row['user_nome']); ?></td>
                                    <td><?php echo h($row['empr_nome']); ?></td>
                                    <td><a href="<?php echo BASE_URL . h($row['rfin_assinatura']); ?>" target="_blank">Ver Relatório</a></td>
                                    <td> Aguardando Aprovação</td>
                                    <td>
                                        <form action="../backend/relatorio-final/excluir-pdf.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este relatório?');" class="d-inline">
                                            <input type="hidden" name="rfin_id" value="<?php echo h($row['rfin_id']); ?>">
                                            <button type="submit" class="btn btn-danger">Excluir Relatório</button>
                                        </form>
                                        <form action="../backend/relatorio-final/aprovar.php" method="POST" class="d-inline">
                                            <input type="hidden" name="rfin_id" value="<?php echo h($row['rfin_id']); ?>">
                                            <button type="submit" class="btn btn-success">Aprovar</button>
                                        </form>
                                    </td>   
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p>Nenhum relatório final pendente.</p>
                <?php } ?>
            </div>
        </div>
    </section>

    <section class="container-fluid mt-5">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2>Contratos</h2>
                <?php if (count($tabela_tudo) > 0) { ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Aluno</th>
                                <th>Empresa</th>
                                <th>Curso</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tabela_tudo as $row) { ?>
                                <tr>
                                    <td><?php echo h($row['cntr_id']); ?></td>
                                    <td><?php echo h($row['user_nome']); ?></td>
                                    <td><?php echo h($row['empr_nome']); ?></td>
                                    <td><?php echo h($row['curs_nome']); ?></td>
                                    <td><?php echo $row['cntr_ativo'] ? 'Ativo' : 'Finalizado'; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p>Nenhum contrato encontrado.</p>
                <?php } ?>
            </div>
        </div>
    </section>

    <?php require '../components/footer.php'; ?>
</body>
</html>