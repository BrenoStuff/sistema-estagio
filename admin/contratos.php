<?php
require '../config.php';
require '../backend/auth/verifica.php';

// Segurança: Apenas admin
if ($_SESSION['acesso'] !== 'admin') {
    header("Location:" . BASE_URL . "error.php?aviso=" . urlencode("Acesso negado."));
    exit();
}

require_once '../backend/helpers/db-connect.php';

// Buscar todos os contratos com dados relacionados
try {
    $sql = "SELECT * FROM contratos
            JOIN empresas ON cntr_id_empresa = empr_id
            JOIN usuarios ON cntr_id_usuario = user_id
            JOIN cursos ON user_id_curs = curs_id
            ORDER BY cntr_id DESC"; // Mais recentes primeiro
    $contratos = $conexao->query($sql)->fetchAll();

    // Dados para o modal de criar novo (Selects)
    $alunos = $conexao->query("SELECT user_id, user_nome FROM usuarios WHERE user_acesso = 'aluno'")->fetchAll();
    $empresas = $conexao->query("SELECT empr_id, empr_nome FROM empresas")->fetchAll();

} catch (PDOException $e) {
    die("Erro ao carregar contratos: " . $e->getMessage());
}

// Função helper
function h($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
?>

<?php require '../components/head.php'; ?>
<body>
    <?php require '../components/navbar.php'; ?>

    <div class="container-fluid p-4 p-md-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 text-gray-800"><i class="fas fa-file-contract"></i> Gestão de Contratos</h1>
                <p class="text-muted mb-0">Gerencie, edite e acompanhe todos os estágios.</p>
            </div>
            <div>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addContratoModal"><i class="fas fa-plus fa-sm text-white-50 me-2"></i>Novo Contrato</button>
                <a href="../backend/exportar.php?tipo=contratos_geral" class="btn btn-success shadow-sm me-2"><i class="fas fa-file-excel fa-sm text-white-50 me-2"></i>Exportar Excel</a>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-body-tertiary">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-secondary">Filtrar por Status:</label>
                        <select id="filtroStatus" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="Ativo">Ativos</option>
                            <option value="Finalizado">Finalizados</option>
                        </select>
                    </div>
                    <div class="col-md-5 offset-md-4 mt-3 mt-md-0">
                         <label class="small font-weight-bold text-secondary">Buscar:</label>
                        <div class="input-group">
                            <input type="text" id="campoBusca" class="form-control form-control-sm" placeholder="Pesquise por aluno, empresa, curso...">
                            <span class="input-group-text bg-primary text-white"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'atualizado') { ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Contrato atualizado com sucesso!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php } ?>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle" id="tabelaContratos">
                        <thead class="table-secondary">
                            <tr>
                                <th>#ID</th>
                                <th>Aluno</th>
                                <th>Empresa</th>
                                <th>Curso</th>
                                <th>Período</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contratos as $row) { 
                                $status = $row['cntr_ativo'] ? 'Ativo' : 'Finalizado';
                                $badge = $row['cntr_ativo'] ? 'bg-success' : 'bg-secondary';
                            ?>
                            <tr class="linha-tabela">
                                <td><?php echo h($row['cntr_id']); ?></td>
                                <td class="fw-bold text-primary"><?php echo h($row['user_nome']); ?></td>
                                <td><?php echo h($row['empr_nome']); ?></td>
                                <td><small><?php echo h($row['curs_nome']); ?></small></td>
                                <td class="small">
                                    <?php echo date('d/m/y', strtotime($row['cntr_data_inicio'])); ?> a 
                                    <?php echo date('d/m/y', strtotime($row['cntr_data_fim'])); ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?php echo $badge; ?> status-badge"><?php echo $status; ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info text-white" 
                                            title="Ver Detalhes"
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
                                            data-bs-horario="<?php echo h($row['cntr_hora_inicio']); ?> às <?php echo h($row['cntr_hora_final']); ?>"
                                            data-bs-remunerado="<?php echo $row['cntr_tipo_estagio'] ? 'Sim' : 'Não'; ?>"
                                            data-bs-status="<?php echo $status; ?>"
                                            data-bs-link-termo="<?php echo BASE_URL . h($row['cntr_termo_contrato']); ?>"
                                            data-bs-link-anexo="<?php echo $row['cntr_anexo_extra'] ? BASE_URL . h($row['cntr_anexo_extra']) : ''; ?>"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-warning" 
                                            title="Editar Contrato"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editContratoModal"
                                            data-bs-id="<?php echo h($row['cntr_id']); ?>"
                                            data-bs-inicio="<?php echo h($row['cntr_data_inicio']); ?>"
                                            data-bs-fim="<?php echo h($row['cntr_data_fim']); ?>"
                                            data-bs-hinicio="<?php echo h($row['cntr_hora_inicio']); ?>"
                                            data-bs-hfim="<?php echo h($row['cntr_hora_final']); ?>"
                                            data-bs-ativo="<?php echo h($row['cntr_ativo']); ?>"
                                            data-bs-tipo="<?php echo h($row['cntr_tipo_estagio']); ?>"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
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

    <div class="modal fade" id="addContratoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Novo Contrato</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../backend/contratos/create.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Aluno</label>
                            <select class="form-select" name="cntr_id_usuario" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($alunos as $a) { ?>
                                    <option value="<?php echo h($a['user_id']); ?>"><?php echo h($a['user_nome']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Empresa</label>
                            <select class="form-select" name="cntr_id_empresa" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($empresas as $e) { ?>
                                    <option value="<?php echo h($e['empr_id']); ?>"><?php echo h($e['empr_nome']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label>Início</label>
                                <input type="date" class="form-control" name="cntr_data_inicio" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label>Fim</label>
                                <input type="date" class="form-control" name="cntr_data_fim" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label>Hora Início</label>
                                <input type="time" class="form-control" name="cntr_hora_inicio" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label>Hora Fim</label>
                                <input type="time" class="form-control" name="cntr_hora_final" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Termo de Compromisso (PDF)</label>
                            <input type="file" class="form-control" name="cntr_termo_contrato" accept=".pdf" required>
                        </div>
                        <div class="mb-3">
                            <label>Plano de Atividade (Opcional PDF)</label>
                            <input type="file" class="form-control" name="cntr_anexo_extra" accept=".pdf">
                        </div>
                         <div class="row">
                            <div class="col-6 mb-3">
                                <label>Remunerado</label>
                                <select class="form-select" name="cntr_tipo_estagio">
                                    <option value="1">Sim</option>
                                    <option value="0">Não</option>
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label>Status Inicial</label>
                                <select class="form-select" name="cntr_ativo">
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer px-0 pb-0">
                            <button type="submit" class="btn btn-primary w-100">Salvar Contrato</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editContratoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark">Editar Contrato #<span id="edit-id-display"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../backend/contratos/update.php" method="POST">
                        <input type="hidden" name="cntr_id" id="edit-cntr-id">
                        
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Data Início</label>
                                <input type="date" name="cntr_data_inicio" id="edit-inicio" class="form-control" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Data Fim</label>
                                <input type="date" name="cntr_data_fim" id="edit-fim" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Hora Início</label>
                                <input type="time" name="cntr_hora_inicio" id="edit-hinicio" class="form-control" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Hora Fim</label>
                                <input type="time" name="cntr_hora_final" id="edit-hfim" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Remunerado?</label>
                            <select name="cntr_tipo_estagio" id="edit-tipo" class="form-select">
                                <option value="1">Sim</option>
                                <option value="0">Não</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status do Contrato</label>
                            <select name="cntr_ativo" id="edit-ativo" class="form-select">
                                <option value="1">Ativo (Em andamento)</option>
                                <option value="0">Finalizado / Inativo</option>
                            </select>
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

    <div class="modal fade" id="contratoDetalhesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes do Contrato #<span id="modal-contrato-id"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary fw-bold">Aluno</h6>
                            <p class="mb-1">Nome: <span id="modal-aluno-nome"></span></p>
                            <p class="mb-1">RA: <span id="modal-aluno-ra"></span></p>
                            <p class="mb-1">Curso: <span id="modal-curso-nome"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary fw-bold">Empresa</h6>
                            <p class="mb-1">Nome: <span id="modal-empresa-nome"></span></p>
                            <p class="mb-1">Contato: <span id="modal-empresa-contato"></span></p>
                            <p class="mb-1">Cidade: <span id="modal-empresa-cidade"></span></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                         <div class="col-md-12">
                            <h6 class="text-primary fw-bold">Dados do Estágio</h6>
                            <p>
                                <strong>Período:</strong> <span id="modal-data-inicio"></span> até <span id="modal-data-fim"></span><br>
                                <strong>Horário:</strong> <span id="modal-horario"></span><br>
                                <strong>Status:</strong> <span id="modal-status"></span>
                            </p>
                         </div>
                    </div>
                    <div class="d-grid gap-2 d-md-block mt-3">
                         <a href="#" id="modal-link-termo" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fas fa-file-pdf"></i> Termo de Contrato</a>
                         <a href="#" id="modal-link-anexo" target="_blank" class="btn btn-outline-secondary btn-sm" style="display:none;"><i class="fas fa-paperclip"></i> Anexo Extra</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require '../components/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // --- 1. Lógica de Pesquisa e Filtro ---
        const campoBusca = document.getElementById('campoBusca');
        const filtroStatus = document.getElementById('filtroStatus');
        const linhas = document.querySelectorAll('#tabelaContratos tbody tr');

        function filtrarTabela() {
            const termo = campoBusca.value.toLowerCase();
            const statusDesejado = filtroStatus.value;

            linhas.forEach(linha => {
                const textoLinha = linha.textContent.toLowerCase();
                const badgeStatus = linha.querySelector('.status-badge').textContent;
                
                const matchTexto = textoLinha.includes(termo);
                const matchStatus = statusDesejado === '' || badgeStatus === statusDesejado;

                if (matchTexto && matchStatus) {
                    linha.style.display = '';
                } else {
                    linha.style.display = 'none';
                }
            });
        }

        campoBusca.addEventListener('keyup', filtrarTabela);
        filtroStatus.addEventListener('change', filtrarTabela);


        // --- 2. Lógica Modal Editar ---
        var editModal = document.getElementById('editContratoModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            editModal.querySelector('#edit-cntr-id').value = button.getAttribute('data-bs-id');
            editModal.querySelector('#edit-id-display').textContent = button.getAttribute('data-bs-id');
            editModal.querySelector('#edit-inicio').value = button.getAttribute('data-bs-inicio');
            editModal.querySelector('#edit-fim').value = button.getAttribute('data-bs-fim');
            editModal.querySelector('#edit-hinicio').value = button.getAttribute('data-bs-hinicio');
            editModal.querySelector('#edit-hfim').value = button.getAttribute('data-bs-hfim');
            editModal.querySelector('#edit-ativo').value = button.getAttribute('data-bs-ativo');
            editModal.querySelector('#edit-tipo').value = button.getAttribute('data-bs-tipo');
        });

        // --- 3. Lógica Modal Detalhes ---
        var detailsModal = document.getElementById('contratoDetalhesModal');
        detailsModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            
            // Preencher textos simples
            detailsModal.querySelector('#modal-contrato-id').textContent = button.getAttribute('data-bs-id');
            detailsModal.querySelector('#modal-aluno-nome').textContent = button.getAttribute('data-bs-aluno-nome');
            detailsModal.querySelector('#modal-aluno-ra').textContent = button.getAttribute('data-bs-aluno-ra');
            detailsModal.querySelector('#modal-curso-nome').textContent = button.getAttribute('data-bs-curso-nome');
            detailsModal.querySelector('#modal-empresa-nome').textContent = button.getAttribute('data-bs-empresa-nome');
            detailsModal.querySelector('#modal-empresa-contato').textContent = button.getAttribute('data-bs-empresa-contato');
            detailsModal.querySelector('#modal-empresa-cidade').textContent = button.getAttribute('data-bs-empresa-cidade');
            detailsModal.querySelector('#modal-data-inicio').textContent = button.getAttribute('data-bs-data-inicio');
            detailsModal.querySelector('#modal-data-fim').textContent = button.getAttribute('data-bs-data-fim');
            detailsModal.querySelector('#modal-horario').textContent = button.getAttribute('data-bs-horario');
            detailsModal.querySelector('#modal-status').textContent = button.getAttribute('data-bs-status');

            // Configurar botões de link
            var linkTermo = button.getAttribute('data-bs-link-termo');
            var linkAnexo = button.getAttribute('data-bs-link-anexo');
            
            detailsModal.querySelector('#modal-link-termo').href = linkTermo;
            
            var btnAnexo = detailsModal.querySelector('#modal-link-anexo');
            if(linkAnexo) {
                btnAnexo.href = linkAnexo;
                btnAnexo.style.display = 'inline-block';
            } else {
                btnAnexo.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html>