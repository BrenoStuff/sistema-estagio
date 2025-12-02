<?php
require '../config.php';
require '../backend/auth/verifica.php';

if ($_SESSION['acesso'] !== 'admin') {
    header("Location:" . BASE_URL . "error.php?aviso=" . urlencode("Acesso negado."));
    exit();
}

require_once '../backend/helpers/db-connect.php';

try {
    // Busca alunos e seus cursos
    $sql = "SELECT usuarios.*, cursos.curs_nome FROM usuarios 
            LEFT JOIN cursos ON user_id_curs = curs_id 
            WHERE user_acesso = 'aluno' 
            ORDER BY user_nome ASC";
    $alunos = $conexao->query($sql)->fetchAll();

    // Busca lista de cursos para os selects dos modais
    $cursos = $conexao->query("SELECT * FROM cursos ORDER BY curs_nome ASC")->fetchAll();

} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

function h($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
?>

<?php require '../components/head.php'; ?>
<body>
    <?php require '../components/navbar.php'; ?>

    <div class="container-fluid p-4 p-md-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 text-gray-800"><i class="fas fa-user-graduate"></i> Gestão de Alunos</h1>
                <p class="text-muted mb-0">Administre o cadastro acadêmico dos estagiários.</p>
            </div>
            <div>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addAlunoModal"><i class="fas fa-user-plus fa-sm text-white-50 me-2"></i>Novo Aluno</button>
                <a href="../backend/exportar.php?tipo=alunos_ativos" class="btn btn-success shadow-sm me-2"><i class="fas fa-file-excel fa-sm text-white-50 me-2"></i>Exportar Excel</a>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Alunos Cadastrados</h6>
                <div class="input-group" style="width: 300px;">
                    <input type="text" id="campoBusca" class="form-control form-control-sm" placeholder="Buscar por nome, RA ou curso...">
                    <span class="input-group-text bg-primary text-white"><i class="fas fa-search"></i></span>
                </div>
            </div>
            
            <div class="card-body">
                <?php if (isset($_GET['msg'])) { ?>
                    <?php if ($_GET['msg'] == 'atualizado') { ?>
                        <div class="alert alert-success alert-dismissible fade show">Dados do aluno atualizados!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php } elseif ($_GET['msg'] == 'deletado') { ?>
                        <div class="alert alert-success alert-dismissible fade show">Aluno removido com sucesso!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php } ?>
                <?php } ?>

                <?php if (isset($_GET['error']) && $_GET['error'] == 'tem_contratos') { ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-lock"></i> Não é possível excluir este aluno pois ele possui <strong>contratos/histórico</strong> no sistema.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php } ?>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle" id="tabelaAlunos">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 10%;">RA</th>
                                <th>Nome Completo</th>
                                <th>Curso</th>
                                <th>Contato</th>
                                <th>Login</th>
                                <th class="text-center" style="width: 15%;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alunos as $row) { ?>
                            <tr class="linha-tabela">
                                <td><span class="badge bg-body-tertiary text-dark border"><?php echo h($row['user_ra']); ?></span></td>
                                <td class="fw-bold text-primary"><?php echo h($row['user_nome']); ?></td>
                                <td><?php echo h($row['curs_nome']); ?></td>
                                <td class="small"><i class="fas fa-phone-alt text-muted me-1"></i><?php echo h($row['user_contato']); ?></td>
                                <td class="small text-muted"><?php echo h($row['user_login']); ?></td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-warning" 
                                            title="Editar"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editAlunoModal"
                                            data-bs-id="<?php echo h($row['user_id']); ?>"
                                            data-bs-nome="<?php echo h($row['user_nome']); ?>"
                                            data-bs-ra="<?php echo h($row['user_ra']); ?>"
                                            data-bs-contato="<?php echo h($row['user_contato']); ?>"
                                            data-bs-login="<?php echo h($row['user_login']); ?>"
                                            data-bs-curso="<?php echo h($row['user_id_curs']); ?>"
                                        >
                                            <i class="fas fa-user-edit"></i>
                                        </button>

                                        <form action="../backend/usuario/delete.php" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja remover este aluno?');">
                                            <input type="hidden" name="user_id" value="<?php echo h($row['user_id']); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addAlunoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Cadastrar Novo Aluno</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../backend/usuario/adicionar.php" method="POST">
                         <input type="hidden" name="redirect" value="alunos">

                        <div class="mb-3">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" name="user_nome" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">RA (Registro Acadêmico)</label>
                                <input type="number" class="form-control" name="user_ra" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Curso</label>
                                <select class="form-select" name="user_id_curs" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($cursos as $c) { ?>
                                        <option value="<?php echo $c['curs_id']; ?>"><?php echo h($c['curs_nome']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contato (Tel/Cel)</label>
                            <input type="text" class="form-control" name="user_contato" required>
                        </div>
                        <hr>
                        <h6 class="text-secondary mb-3">Dados de Acesso</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Login</label>
                                <input type="text" class="form-control" name="user_login" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Senha Inicial</label>
                                <input type="password" class="form-control" name="user_senha" required>
                            </div>
                        </div>
                        <div class="modal-footer px-0 pb-0">
                            <button type="submit" class="btn btn-primary w-100">Salvar Aluno</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editAlunoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark">Editar Aluno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../backend/usuario/update.php" method="POST">
                        <input type="hidden" name="user_id" id="edit-id">

                        <div class="mb-3">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" name="user_nome" id="edit-nome" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">RA</label>
                                <input type="number" class="form-control" name="user_ra" id="edit-ra" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Curso</label>
                                <select class="form-select" name="user_id_curs" id="edit-curso" required>
                                    <?php foreach ($cursos as $c) { ?>
                                        <option value="<?php echo $c['curs_id']; ?>"><?php echo h($c['curs_nome']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contato</label>
                            <input type="text" class="form-control" name="user_contato" id="edit-contato" required>
                        </div>
                        
                        <div class="alert alert-secondary mt-3 pt-2 pb-2">
                            <small><i class="fas fa-info-circle"></i> Deixe a senha em branco para manter a atual.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Login</label>
                                <input type="text" class="form-control" name="user_login" id="edit-login" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" name="user_senha" placeholder="(Opcional)">
                            </div>
                        </div>

                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-warning">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require '../components/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Busca Dinâmica
        const campoBusca = document.getElementById('campoBusca');
        const linhas = document.querySelectorAll('#tabelaAlunos tbody tr');

        campoBusca.addEventListener('keyup', function() {
            const termo = this.value.toLowerCase();
            linhas.forEach(linha => {
                const texto = linha.textContent.toLowerCase();
                linha.style.display = texto.includes(termo) ? '' : 'none';
            });
        });

        // Preencher Modal de Edição
        var editModal = document.getElementById('editAlunoModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            
            editModal.querySelector('#edit-id').value = button.getAttribute('data-bs-id');
            editModal.querySelector('#edit-nome').value = button.getAttribute('data-bs-nome');
            editModal.querySelector('#edit-ra').value = button.getAttribute('data-bs-ra');
            editModal.querySelector('#edit-contato').value = button.getAttribute('data-bs-contato');
            editModal.querySelector('#edit-login').value = button.getAttribute('data-bs-login');
            editModal.querySelector('#edit-curso').value = button.getAttribute('data-bs-curso');
        });
    });
    </script>
</body>
</html>