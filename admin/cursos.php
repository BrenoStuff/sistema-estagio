<?php
require '../config.php';
require '../backend/auth/verifica.php';

// Segurança
if ($_SESSION['acesso'] !== 'admin') {
    header("Location:" . BASE_URL . "error.php?aviso=" . urlencode("Acesso negado."));
    exit();
}

require_once '../backend/helpers/db-connect.php';

try {
    // Busca os cursos e conta quantos alunos tem em cada (útil para gestão)
    $sql = "SELECT c.*, (SELECT COUNT(*) FROM usuarios u WHERE u.user_id_curs = c.curs_id) as total_alunos 
            FROM cursos c 
            ORDER BY c.curs_nome ASC";
    $cursos = $conexao->query($sql)->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar cursos: " . $e->getMessage());
}

function h($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
?>

<?php require '../components/head.php'; ?>
<body>
    <?php require '../components/navbar.php'; ?>

    <div class="container-fluid p-4 p-md-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 text-gray-800"><i class="fas fa-graduation-cap"></i> Gestão de Cursos</h1>
                <p class="text-muted mb-0">Cadastre as áreas de formação da instituição.</p>
            </div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addCursoModal">
                <i class="fas fa-plus fa-sm text-white-50 me-2"></i>Novo Curso
            </button>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-body-tertiary">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Cursos Ativos</h6>
                    <div class="input-group" style="width: 300px;">
                        <input type="text" id="campoBusca" class="form-control form-control-sm" placeholder="Buscar curso...">
                        <span class="input-group-text bg-primary text-white"><i class="fas fa-search"></i></span>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <?php if (isset($_GET['msg'])) { ?>
                    <?php if ($_GET['msg'] == 'atualizado') { ?>
                        <div class="alert alert-success alert-dismissible fade show">Curso atualizado com sucesso!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php } elseif ($_GET['msg'] == 'deletado') { ?>
                        <div class="alert alert-success alert-dismissible fade show">Curso removido com sucesso!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php } ?>
                <?php } ?>

                <?php if (isset($_GET['error']) && $_GET['error'] == 'tem_alunos') { ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle"></i> Não é possível excluir este curso pois existem <strong>alunos matriculados</strong> nele.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php } ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tabelaCursos">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 10%;">ID</th>
                                <th>Nome do Curso</th>
                                <th class="text-center">Alunos Matriculados</th>
                                <th class="text-center" style="width: 15%;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cursos as $row) { ?>
                            <tr>
                                <td><?php echo h($row['curs_id']); ?></td>
                                <td class="fw-bold text-primary"><?php echo h($row['curs_nome']); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-secondary rounded-pill"><?php echo $row['total_alunos']; ?> alunos</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-warning" 
                                            title="Editar"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editCursoModal"
                                            data-bs-id="<?php echo h($row['curs_id']); ?>"
                                            data-bs-nome="<?php echo h($row['curs_nome']); ?>"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <form action="../backend/cursos/delete.php" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este curso?');">
                                            <input type="hidden" name="curs_id" value="<?php echo h($row['curs_id']); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Excluir" <?php echo ($row['total_alunos'] > 0) ? 'disabled' : ''; ?>>
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
                <?php if (count($cursos) == 0) echo '<div class="alert alert-info mt-3">Nenhum curso cadastrado.</div>'; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCursoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Adicionar Novo Curso</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../backend/cursos/adicionar.php" method="POST">
                        <input type="hidden" name="redirect" value="cursos">
                        
                        <div class="mb-3">
                            <label class="form-label">Nome do Curso</label>
                            <input type="text" class="form-control" name="curs_nome" placeholder="Ex: Engenharia Civil" required>
                        </div>
                        <div class="modal-footer px-0 pb-0">
                            <button type="submit" class="btn btn-primary w-100">Salvar Curso</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editCursoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark">Editar Curso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../backend/cursos/update.php" method="POST">
                        <input type="hidden" name="curs_id" id="edit-id">
                        
                        <div class="mb-3">
                            <label class="form-label">Nome do Curso</label>
                            <input type="text" class="form-control" name="curs_nome" id="edit-nome" required>
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
        // Busca Simples
        const campoBusca = document.getElementById('campoBusca');
        const linhas = document.querySelectorAll('#tabelaCursos tbody tr');

        campoBusca.addEventListener('keyup', function() {
            const termo = this.value.toLowerCase();
            linhas.forEach(linha => {
                const texto = linha.textContent.toLowerCase();
                linha.style.display = texto.includes(termo) ? '' : 'none';
            });
        });

        // Preencher Modal de Edição
        var editModal = document.getElementById('editCursoModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            editModal.querySelector('#edit-id').value = button.getAttribute('data-bs-id');
            editModal.querySelector('#edit-nome').value = button.getAttribute('data-bs-nome');
        });
    });
    </script>
</body>
</html>