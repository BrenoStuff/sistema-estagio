<?php
require '../config.php';
require '../backend/auth/verifica.php';

// Apenas Admin
if ($_SESSION['acesso'] !== 'admin') {
    header("Location:" . BASE_URL . "error.php?aviso=" . urlencode("Acesso negado."));
    exit();
}

require_once '../backend/helpers/db-connect.php';

try {
    // 1. Buscar Relatórios Iniciais (TUDO: Aprovados e Pendentes)
    $sql_rini = "SELECT rini.*, u.user_nome, u.user_ra, e.empr_nome 
                 FROM relatorio_inicial rini
                 JOIN contratos c ON c.cntr_id_relatorio_inicial = rini.rini_id
                 JOIN usuarios u ON c.cntr_id_usuario = u.user_id
                 JOIN empresas e ON c.cntr_id_empresa = e.empr_id
                 WHERE rini.rini_assinatura IS NOT NULL
                 ORDER BY rini.rini_id DESC";
    $relatorios_ini = $conexao->query($sql_rini)->fetchAll();

    // 2. Buscar Relatórios Finais (TUDO)
    $sql_rfin = "SELECT rfin.*, u.user_nome, u.user_ra, e.empr_nome 
                 FROM relatorio_final rfin
                 JOIN contratos c ON c.cntr_id_relatorio_final = rfin.rfin_id
                 JOIN usuarios u ON c.cntr_id_usuario = u.user_id
                 JOIN empresas e ON c.cntr_id_empresa = e.empr_id
                 WHERE rfin.rfin_assinatura IS NOT NULL
                 ORDER BY rfin.rfin_id DESC";
    $relatorios_fin = $conexao->query($sql_rfin)->fetchAll();

} catch (PDOException $e) {
    die("Erro ao carregar relatórios: " . $e->getMessage());
}

function h($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
?>

<?php require '../components/head.php'; ?>
<body>
    <?php require '../components/navbar.php'; ?>

    <div class="container-fluid p-4 p-md-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 text-gray-800"><i class="fas fa-file-signature"></i> Gestão de Relatórios</h1>
                <p class="text-muted mb-0">Histórico completo de documentos enviados pelos alunos.</p>
            </div>
            <a href="relatorios.php" class="btn btn-outline-primary btn-sm"><i class="fas fa-sync-alt"></i> Atualizar Lista</a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                    <input type="text" id="campoBusca" class="form-control" placeholder="Pesquisar por nome do aluno, RA ou empresa...">
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs mb-3" id="relatorioTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="inicial-tab" data-bs-toggle="tab" data-bs-target="#inicial" type="button" role="tab"><i class="fas fa-file-alt me-2"></i>Relatórios Iniciais</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="final-tab" data-bs-toggle="tab" data-bs-target="#final" type="button" role="tab"><i class="fas fa-file-medical me-2"></i>Relatórios Finais</button>
            </li>
        </ul>

        <div class="tab-content" id="relatorioTabsContent">
            
            <div class="tab-pane fade show active" id="inicial" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle tabela-relatorios">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Aluno</th>
                                        <th>Empresa</th>
                                        <th class="text-center">PDF</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($relatorios_ini as $row) { 
                                        $aprovado = $row['rini_aprovado'];
                                        $classeStatus = $aprovado ? 'bg-success' : 'bg-warning text-dark';
                                        $textoStatus = $aprovado ? 'Aprovado' : 'Pendente';
                                    ?>
                                    <tr>
                                        <td>#<?php echo h($row['rini_id']); ?></td>
                                        <td>
                                            <div class="fw-bold text-primary"><?php echo h($row['user_nome']); ?></div>
                                            <small class="text-muted">RA: <?php echo h($row['user_ra']); ?></small>
                                        </td>
                                        <td><?php echo h($row['empr_nome']); ?></td>
                                        <td class="text-center">
                                            <a href="<?php echo BASE_URL . h($row['rini_assinatura']); ?>" target="_blank" class="btn btn-sm btn-outline-danger" title="Ver PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?php echo $classeStatus; ?>"><?php echo $textoStatus; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <?php if (!$aprovado) { ?>
                                                    <form action="../backend/relatorio-inicial/aprovar.php" method="POST">
                                                        <input type="hidden" name="rini_id" value="<?php echo h($row['rini_id']); ?>">
                                                        <button type="submit" class="btn btn-sm btn-success" title="Aprovar">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                <?php } ?>

                                                <form action="../backend/relatorio-inicial/reprovar.php" method="POST" onsubmit="return confirm('Tem certeza? Se o aluno já foi aprovado, ele terá que enviar novamente.');" class="ms-1">
                                                    <input type="hidden" name="rini_id" value="<?php echo h($row['rini_id']); ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Reprovar / Solicitar Correção">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($relatorios_ini) == 0) echo '<p class="text-center text-muted mt-3">Nenhum relatório inicial encontrado.</p>'; ?>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="final" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle tabela-relatorios">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Aluno</th>
                                        <th>Empresa</th>
                                        <th class="text-center">PDF</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($relatorios_fin as $row) { 
                                        $aprovado = $row['rfin_aprovado'];
                                        $classeStatus = $aprovado ? 'bg-success' : 'bg-warning text-dark';
                                        $textoStatus = $aprovado ? 'Aprovado' : 'Pendente';
                                    ?>
                                    <tr>
                                        <td>#<?php echo h($row['rfin_id']); ?></td>
                                        <td>
                                            <div class="fw-bold text-primary"><?php echo h($row['user_nome']); ?></div>
                                            <small class="text-muted">RA: <?php echo h($row['user_ra']); ?></small>
                                        </td>
                                        <td><?php echo h($row['empr_nome']); ?></td>
                                        <td class="text-center">
                                            <a href="<?php echo BASE_URL . h($row['rfin_assinatura']); ?>" target="_blank" class="btn btn-sm btn-outline-danger" title="Ver PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?php echo $classeStatus; ?>"><?php echo $textoStatus; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <?php if (!$aprovado) { ?>
                                                    <form action="../backend/relatorio-final/aprovar.php" method="POST">
                                                        <input type="hidden" name="rfin_id" value="<?php echo h($row['rfin_id']); ?>">
                                                        <button type="submit" class="btn btn-sm btn-success" title="Aprovar">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                <?php } ?>

                                                <form action="../backend/relatorio-final/reprovar.php" method="POST" onsubmit="return confirm('Tem certeza? O status voltará para reprovado.');" class="ms-1">
                                                    <input type="hidden" name="rfin_id" value="<?php echo h($row['rfin_id']); ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Reprovar / Solicitar Correção">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($relatorios_fin) == 0) echo '<p class="text-center text-muted mt-3">Nenhum relatório final encontrado.</p>'; ?>
                    </div>
                </div>
            </div>

        </div> </div>

    <?php require '../components/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Lógica de Busca Unificada
        const campoBusca = document.getElementById('campoBusca');
        
        campoBusca.addEventListener('keyup', function() {
            const termo = this.value.toLowerCase();
            // Busca em todas as tabelas com a classe .tabela-relatorios
            const tabelas = document.querySelectorAll('.tabela-relatorios tbody');

            tabelas.forEach(tbody => {
                const linhas = tbody.querySelectorAll('tr');
                linhas.forEach(linha => {
                    const texto = linha.textContent.toLowerCase();
                    if(texto.includes(termo)) {
                        linha.style.display = '';
                    } else {
                        linha.style.display = 'none';
                    }
                });
            });
        });
    });
    </script>
</body>
</html>