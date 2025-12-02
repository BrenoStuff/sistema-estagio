<?php
require '../config.php';
require '../backend/auth/verifica.php';

if ($_SESSION['acesso'] !== 'admin') {
    header("Location:" . BASE_URL . "error.php?aviso=" . urlencode("Acesso negado."));
    exit();
}

require_once '../backend/helpers/db-connect.php';

try {
    $sql = "SELECT * FROM empresas ORDER BY empr_id DESC";
    $empresas = $conexao->query($sql)->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar empresas: " . $e->getMessage());
}

function h($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
?>

<?php require '../components/head.php'; ?>
<body>
    <?php require '../components/navbar.php'; ?>

    <div class="container-fluid p-4 p-md-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 text-gray-800"><i class="fas fa-building"></i> Gestão de Empresas</h1>
                <p class="text-muted mb-0">Cadastre e gerencie as empresas parceiras.</p>
            </div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addEmpresaModal">
                <i class="fas fa-plus fa-sm text-white-50 me-2"></i>Nova Empresa
            </button>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Empresas Cadastradas</h6>
                <div class="input-group" style="width: 300px;">
                    <input type="text" id="campoBusca" class="form-control form-control-sm" placeholder="Buscar empresa, CNPJ ou cidade...">
                    <span class="input-group-text bg-primary text-white"><i class="fas fa-search"></i></span>
                </div>
            </div>
            
            <div class="card-body">
                <?php if (isset($_GET['msg'])) { ?>
                    <?php if ($_GET['msg'] == 'atualizado') { ?>
                        <div class="alert alert-success alert-dismissible fade show">Empresa atualizada com sucesso!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php } elseif ($_GET['msg'] == 'deletado') { ?>
                        <div class="alert alert-success alert-dismissible fade show">Empresa removida com sucesso!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php } ?>
                <?php } ?>

                <?php if (isset($_GET['error']) && $_GET['error'] == 'tem_contratos') { ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle"></i> Não é possível excluir esta empresa pois ela possui <strong>contratos vinculados</strong>.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php } ?>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle" id="tabelaEmpresas">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th>Razão Social / Nome</th>
                                <th>CNPJ</th>
                                <th>Tipo</th>
                                <th>Cidade</th>
                                <th>Contato Principal</th>
                                <th class="text-center" style="width: 15%;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($empresas as $row) { ?>
                            <tr class="linha-tabela">
                                <td><?php echo h($row['empr_id']); ?></td>
                                <td class="fw-bold text-primary"><?php echo h($row['empr_nome']); ?></td>
                                <td><?php echo h($row['empr_cnpj']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo h($row['empr_tipo']); ?></span></td>
                                <td><?php echo h($row['empr_cidade']); ?></td>
                                <td class="small"><?php echo h($row['empr_contato_1']); ?></td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-warning" 
                                            title="Editar"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editEmpresaModal"
                                            data-bs-id="<?php echo h($row['empr_id']); ?>"
                                            data-bs-nome="<?php echo h($row['empr_nome']); ?>"
                                            data-bs-cnpj="<?php echo h($row['empr_cnpj']); ?>"
                                            data-bs-tipo="<?php echo h($row['empr_tipo']); ?>"
                                            data-bs-cidade="<?php echo h($row['empr_cidade']); ?>"
                                            data-bs-endereco="<?php echo h($row['empr_endereco']); ?>"
                                            data-bs-contato1="<?php echo h($row['empr_contato_1']); ?>"
                                            data-bs-contato2="<?php echo h($row['empr_contato_2']); ?>"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <form action="../backend/empresas/delete.php" method="POST" class="d-inline" onsubmit="return confirm('ATENÇÃO: Tem certeza?');">
                                            <input type="hidden" name="empr_id" value="<?php echo h($row['empr_id']); ?>">
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

    <div class="modal fade" id="addEmpresaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Nova Empresa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../backend/empresas/adicionar.php" method="POST">
                        <input type="hidden" name="redirect" value="empresas"> 
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Nome / Razão Social</label>
                                <input type="text" class="form-control" name="empr_nome" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">CNPJ</label>
                                <input type="text" class="form-control" name="empr_cnpj">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" name="empr_tipo" required>
                                    <option value="Privada">Privada</option>
                                    <option value="Pública">Pública</option>
                                    <option value="ONG">ONG</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cidade</label>
                                <input type="text" class="form-control" name="empr_cidade" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Endereço Completo</label>
                            <input type="text" class="form-control" name="empr_endereco" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contato 1 (Telefone/Email)</label>
                                <input type="text" class="form-control" name="empr_contato_1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contato 2 (Opcional)</label>
                                <input type="text" class="form-control" name="empr_contato_2">
                            </div>
                        </div>
                        <div class="modal-footer px-0 pb-0">
                            <button type="submit" class="btn btn-primary w-100">Salvar Empresa</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editEmpresaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark">Editar Empresa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../backend/empresas/update.php" method="POST">
                        <input type="hidden" name="empr_id" id="edit-id">
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Nome / Razão Social</label>
                                <input type="text" class="form-control" name="empr_nome" id="edit-nome" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">CNPJ</label>
                                <input type="text" class="form-control" name="empr_cnpj" id="edit-cnpj">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" name="empr_tipo" id="edit-tipo" required>
                                    <option value="Privada">Privada</option>
                                    <option value="Pública">Pública</option>
                                    <option value="ONG">ONG</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cidade</label>
                                <input type="text" class="form-control" name="empr_cidade" id="edit-cidade" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Endereço Completo</label>
                            <input type="text" class="form-control" name="empr_endereco" id="edit-endereco" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contato 1</label>
                                <input type="text" class="form-control" name="empr_contato_1" id="edit-contato1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contato 2</label>
                                <input type="text" class="form-control" name="empr_contato_2" id="edit-contato2">
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
        // Busca em tempo real
        const campoBusca = document.getElementById('campoBusca');
        const linhas = document.querySelectorAll('#tabelaEmpresas tbody tr');

        campoBusca.addEventListener('keyup', function() {
            const termo = this.value.toLowerCase();
            linhas.forEach(linha => {
                const texto = linha.textContent.toLowerCase();
                linha.style.display = texto.includes(termo) ? '' : 'none';
            });
        });

        // Preencher Modal de Edição
        var editModal = document.getElementById('editEmpresaModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            
            editModal.querySelector('#edit-id').value = button.getAttribute('data-bs-id');
            editModal.querySelector('#edit-nome').value = button.getAttribute('data-bs-nome');
            editModal.querySelector('#edit-cnpj').value = button.getAttribute('data-bs-cnpj');
            editModal.querySelector('#edit-tipo').value = button.getAttribute('data-bs-tipo');
            editModal.querySelector('#edit-cidade').value = button.getAttribute('data-bs-cidade');
            editModal.querySelector('#edit-endereco').value = button.getAttribute('data-bs-endereco');
            editModal.querySelector('#edit-contato1').value = button.getAttribute('data-bs-contato1');
            editModal.querySelector('#edit-contato2').value = button.getAttribute('data-bs-contato2');
        });
    });
    </script>
</body>
</html>