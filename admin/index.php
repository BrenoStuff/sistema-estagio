<?php
// Configurações da Página
require '../config.php';
require '../backend/auth/verifica.php';
$title = SIS_NAME . ' - Area do Aluno';
$navActive = 'home';

// Verifica se é admin
verifica_acesso('admin');

// 
// BANCO DE DADOS
//
require_once '../backend/helpers/db-connect.php';

// Usuario
$sql = "SELECT user_nome FROM usuarios WHERE user_id = '" . $_SESSION['usuario'] . "'";
$dado = $conexao->query($sql);
if ($dado->num_rows > 0) {
    $usuario = $dado->fetch_assoc();
} else {
    $usuario = [];
}

// Contratos
$sql = "SELECT * FROM contratos";
$dado = $conexao->query($sql);
if ($dado->num_rows > 0) {
    $contratos = $dado->fetch_all(MYSQLI_ASSOC);
} else {
    $contratos = [];
}

// Cursos
$sql = "SELECT * FROM cursos";
$dado = $conexao->query($sql);
if ($dado->num_rows > 0) {
    $cursos = $dado->fetch_all(MYSQLI_ASSOC);
} else {
    $cursos = [];
}

// Empresas
$sql = "SELECT * FROM empresas";
$dado = $conexao->query($sql);
if ($dado->num_rows > 0) {
    $empresas = $dado->fetch_all(MYSQLI_ASSOC);
} else {
    $empresas = [];
}

// Tabela tudo
$sql = "SELECT * FROM contratos
        JOIN empresas ON cntr_id_empresa = empr_id
        JOIN usuarios ON cntr_id_usuario = user_id
        JOIN cursos ON user_id_curs = curs_id";
$tabela_tudo = $conexao->query($sql);

// Alunos estagiando
$sql = "SELECT * FROM usuarios
        JOIN contratos ON usuarios.user_id = contratos.cntr_id_usuario
        WHERE contratos.cntr_ativo = 1 AND usuarios.user_acesso = 'aluno'";
$alunos_estagiando = $conexao->query($sql);

// Alunos sem estágio
$sql = "SELECT * FROM usuarios
         WHERE user_acesso = 'aluno' AND user_id NOT IN (SELECT cntr_id_usuario FROM contratos WHERE cntr_ativo = 1)";
$alunos_nao_estagiando = $conexao->query($sql);

// Relatórios inicial esperando aprovação
$sql = "SELECT * FROM contratos
        JOIN relatorio_inicial ON cntr_id_relatorio_inicial = rini_id
        JOIN usuarios ON cntr_id_usuario = user_id
        JOIN empresas ON cntr_id_empresa = empr_id
        WHERE rini_assinatura IS NOT NULL AND rini_aprovado = 0";
$relatorio_ini_esperando = $conexao->query($sql);

// Relatórios final esperando aprovação
$sql = "SELECT * FROM contratos
        JOIN relatorio_final ON cntr_id_relatorio_final = rfin_id
        JOIN usuarios ON cntr_id_usuario = user_id
        JOIN empresas ON cntr_id_empresa = empr_id
        WHERE rfin_assinatura IS NOT NULL AND rfin_aprovado = 0";
$relatorio_fin_esperando = $conexao->query($sql);

// Contratos finalizados
$sql = "SELECT * FROM contratos
        JOIN empresas ON cntr_id_empresa = empr_id
        JOIN usuarios ON cntr_id_usuario = user_id
        WHERE cntr_ativo = 0";
$contratos_finalizados = $conexao->query($sql);

?>

<?php require '../components/head.php'; ?>
<body>
    <?php require '../components/navbar.php'; ?>

    <section class="container-fluid py-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4">Dashboard</h1>
                <p>Bem vindo, <?php echo $usuario['user_nome']; ?>!</p>
            </div>
        </div>
    </section>

    <section class="container-fluid" id="info-cards">
        <div class="row">

            <!-- Alunos Estagiando -->
            <div class="col-md-3 mb-4">
                <div class="card text-white bg-primary h-100">
                    <div class="card-header">
                        <h5 class="card-title">Alunos estagiando</h5>
                    </div>
                    <div class="card-body">
                        <h3 class="fs-1"><?php echo $alunos_estagiando->num_rows; ?></h3>
                        <p class="card-text">Total de alunos atualmente estagiando.</p>
                    </div>
                </div>
            </div>

            <!-- Alunos Sem Estágio -->
            <div class="col-md-3 mb-4">
                <div class="card text-white bg-danger h-100">
                    <div class="card-header">
                        <h5 class="card-title">Alunos sem estágio</h5>
                    </div>
                    <div class="card-body">
                        <h3 class="fs-1"><?php echo $alunos_nao_estagiando->num_rows; ?></h3>
                        <p class="card-text">Alunos que ainda não estão estagiando.</p>
                    </div>
                </div>
            </div>

            <!-- Relatórios Iniciais e Finais Esperando Aprovação -->
            <div class="col-md-3 mb-4">
                <div class="card text-white bg-warning h-100">
                    <div class="card-header">
                        <h5 class="card-title">Relatórios pendentes</h5>
                    </div>
                    <div class="card-body">
                        <h3 class="fs-1">
                            <?php echo $relatorio_ini_esperando->num_rows + $relatorio_fin_esperando->num_rows; ?>
                        </h3>
                        <p class="card-text">Total de relatórios pendentes de aprovação.</p>
                    </div>
                </div>
            </div>

            <!-- Contratos Finalizados -->
            <div class="col-md-3 mb-4">
                <div class="card text-white bg-success h-100">
                    <div class="card-header">
                        <h5 class="card-title">Contratos finalizados</h5>
                    </div>
                    <div class="card-body">
                        <h3 class="fs-1"><?php echo $contratos_finalizados->num_rows; ?></h3>
                        <p class="card-text">Total de contratos finalizados.</p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <section class="container-fluid mt-5">
        <div class="col-md-12">
            <h2> Cadastramento </h2>
            <!-- button para adicionar novo contrato -->
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContratoModal">Adicionar Contrato</button>

            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmpresaModal">Adicionar Empresa</button>

            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCursoModal">Adicionar Curso</button>

            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addAlunoModal">Adicionar Aluno</button>

            <!-- Modal para adicionar contrato -->
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
                                            <option value="<?php echo $aluno['user_id']; ?>"><?php echo $aluno['user_nome']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="cntr_id_empresa" class="form-label">Empresa</label>
                                    <select class="form-select" name="cntr_id_empresa" required>
                                        <option value="">Selecione uma empresa</option>
                                        <?php foreach ($empresas as $empresa) { ?>
                                            <option value="<?php echo $empresa['empr_id']; ?>"><?php echo $empresa['empr_nome']; ?></option>
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

            <!-- Modal para adicionar empresa -->
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

            <!-- Modal para adicionar curso -->
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

            <!-- Modal para adicionar aluno -->
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
                                            <option value="<?php echo $curso['curs_id']; ?>"><?php echo $curso['curs_nome']; ?></option>
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
        <!-- Relatorios Iniciais esperando aprovação -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h2>Relatórios Iniciais Pendentes</h2>
                <?php if ($relatorio_ini_esperando->num_rows > 0) { ?>
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
                            <?php while ($row = $relatorio_ini_esperando->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $row['rini_id']; ?></td>
                                    <td><?php echo $row['user_nome']; ?></td>
                                    <td><?php echo $row['empr_nome']; ?></td>
                                    <td><a href="<?php echo BASE_URL . $row['rini_assinatura']; ?>" target="_blank">Ver Relatório</a></td>
                                    <td> Aguardando Aprovação</td>
                                    <td>
                                        <form action="../backend/relatorio-inicial/excluir-pdf.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este relatório?');">
                                            <input type="hidden" name="rini_id" value="<?php echo $row['rini_id']; ?>">
                                            <button type="submit" class="btn btn-danger">Excluir Relatório</button>
                                        </form>
                                        <form action="../backend/relatorio-inicial/aprovar.php" method="POST" class="d-inline">
                                            <input type="hidden" name="rini_id" value="<?php echo $row['rini_id']; ?>">
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
        <!-- Relatorios Finais esperndo aprovação -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h2>Relatórios Finais Pendentes</h2>
                <?php if ($relatorio_fin_esperando->num_rows > 0) { ?>
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
                            <?php while ($row = $relatorio_fin_esperando->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $row['rfin_id']; ?></td>
                                    <td><?php echo $row['user_nome']; ?></td>
                                    <td><?php echo $row['empr_nome']; ?></td>
                                    <td><a href="<?php echo BASE_URL . $row['rfin_assinatura']; ?>" target="_blank">Ver Relatório</a></td>
                                    <td> Aguardando Aprovação</td>
                                    <td>
                                        <form action="../backend/relatorio-final/excluir-pdf.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este relatório?');">
                                            <input type="hidden" name="rfin_id" value="<?php echo $row['rfin_id']; ?>">
                                            <button type="submit" class="btn btn-danger">Excluir Relatório</button>
                                        </form>
                                        <form action="../backend/relatorio-final/aprovar.php" method="POST" class="d-inline">
                                            <input type="hidden" name="rfin_id" value="<?php echo $row['rfin_id']; ?>">
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
        <!-- Tabela de Contratos -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h2>Contratos</h2>
                <?php if ($tabela_tudo->num_rows > 0) { ?>
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
                            <?php while ($row = $tabela_tudo->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $row['cntr_id']; ?></td>
                                    <td><?php echo $row['user_nome']; ?></td>
                                    <td><?php echo $row['empr_nome']; ?></td>
                                    <td><?php echo $row['curs_nome']; ?></td>
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
