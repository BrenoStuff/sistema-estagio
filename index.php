<?php
// Configurações da Página
require 'config.php';
require 'backend/auth/verifica.php';
$title = SIS_NAME . ' - Area do Aluno';
$navActive = 'home';

//
// CÓDIGO DE CONEXÃO COM O BANCO DE DADOS (Migrado para PDO)
//
require_once 'backend/helpers/db-connect.php';

$user_id = $_SESSION['usuario'];

// Inicializa variáveis para evitar erros
$usuario = null;
$contratoAtivo = null;
$relatorioInicial = null;
$atividadesRelatorioInicial = []; // Usa array vazio para loops foreach
$relatorioFinal = null;
$atividadesRelatorioFinal = []; // Usa array vazio para loops foreach

// Helper de segurança para prevenir XSS
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

try {
    // Informações do usuário (Corrigido SQL Injection)
    $sql_user = "SELECT * FROM usuarios
                 JOIN cursos ON user_id_curs = curs_id
                 WHERE user_id = ?";
    $stmt_user = $conexao->prepare($sql_user);
    $stmt_user->execute([$user_id]);
    $usuario = $stmt_user->fetch();

    if (!$usuario) {
        header("location:error.php?aviso=Usuário não encontrado!");
        exit();
    }

    // Informações do contrato ativo do usuário (Corrigido SQL Injection)
    $sql_contrato = "SELECT * FROM contratos
                     JOIN empresas ON cntr_id_empresa = empr_id
                     WHERE cntr_id_usuario = ? AND cntr_ativo = 1 LIMIT 1";
    $stmt_contrato = $conexao->prepare($sql_contrato);
    $stmt_contrato->execute([$user_id]);
    $contratoAtivo = $stmt_contrato->fetch();

    // Se um contrato ativo existir, busca os relatórios e atividades
    if ($contratoAtivo) {
        
        // Informações de relatório inicial (Corrigido SQL Injection)
        if (!empty($contratoAtivo['cntr_id_relatorio_inicial'])) {
            $sql_rini = "SELECT * FROM relatorio_inicial WHERE rini_id = ?";
            $stmt_rini = $conexao->prepare($sql_rini);
            $stmt_rini->execute([$contratoAtivo['cntr_id_relatorio_inicial']]);
            $relatorioInicial = $stmt_rini->fetch();
        }

        // Atividades do relatório inicial (Corrigido SQL Injection)
        if ($relatorioInicial) {
            $sql_atv_ini = "SELECT * FROM atv_estagio_ini WHERE atvi_id_relatorio_ini = ?";
            $stmt_atv_ini = $conexao->prepare($sql_atv_ini);
            $stmt_atv_ini->execute([$relatorioInicial['rini_id']]);
            $atividadesRelatorioInicial = $stmt_atv_ini->fetchAll();
        }

        // Informações de relatório final (Corrigido SQL Injection)
        if (!empty($contratoAtivo['cntr_id_relatorio_final'])) {
            $sql_rfin = "SELECT * FROM relatorio_final WHERE rfin_id = ?";
            $stmt_rfin = $conexao->prepare($sql_rfin);
            $stmt_rfin->execute([$contratoAtivo['cntr_id_relatorio_final']]);
            $relatorioFinal = $stmt_rfin->fetch();
        }

        // Atividades do relatório final (Corrigido SQL Injection)
        if ($relatorioFinal) {
            $sql_atv_fin = "SELECT * FROM atv_estagio_fin WHERE atvf_id_relatorio_fin = ?";
            $stmt_atv_fin = $conexao->prepare($sql_atv_fin);
            $stmt_atv_fin->execute([$relatorioFinal['rfin_id']]);
            $atividadesRelatorioFinal = $stmt_atv_fin->fetchAll();
        }
    }
} catch (PDOException $e) {
    // Tratamento de erro seguro
    error_log("Erro PDO no Index (Aluno): " . $e->getMessage());
    die("Erro ao carregar dados do painel. Contate o administrador.");
}

?>

<?php
// Head
require 'components/head.php';
?>

<body class="bg-body-tertiary">
    <?php require 'components/navbar.php'; ?>

    <div class="container-lg mt-4">
        
        <!-- Card de Perfil do Aluno -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        <i class="fa-solid fa-circle-user fa-7x text-primary"></i>
                    </div>
                    <div>
                        <h1 class="h3 fw-bold mb-0">Bem vindo, <?php echo h($usuario['user_nome']); ?>!</h1>
                        <p class="text-muted mb-1">Aqui está o resumo do seu perfil e estágio.</p>
                        <hr class="my-2">
                        <dl class="row mb-0 small">
                            <dt class="col-sm-3 col-lg-2">Curso:</dt>
                            <dd class="col-sm-9 col-lg-10"><?php echo h($usuario['curs_nome']); ?></dd>

                            <dt class="col-sm-3 col-lg-2">RA:</dt>
                            <dd class="col-sm-9 col-lg-10"><?php echo h($usuario['user_ra']); ?></dd>

                            <dt class="col-sm-3 col-lg-2">E-mail:</dt>
                            <dd class="col-sm-9 col-lg-10"><?php echo h($usuario['user_login']); ?></dd>

                            <dt class="col-sm-3 col-lg-2">Contato:</dt>
                            <dd class="col-sm-9 col-lg-10"><?php echo h($usuario['user_contato']); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card de Contrato -->
        <?php if ($contratoAtivo == null): ?>
            <!-- Caso NÃO TENHA contrato ativo -->
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-5">
                    <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                    <h2 class="h4">Nenhum Contrato Ativo Encontrado</h2>
                    <p class="text-muted">
                        Você não possui nenhum contrato de estágio ativo no momento.
                        <br>
                        Por favor, entre em contato com a coordenação do curso se isso for um erro.
                    </p>
                </div>
            </div>
        <?php else: ?>
            <!-- Caso TENHA contrato ativo -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white p-3">
                    <h2 class="h4 mb-0"><i class="fas fa-suitcase me-2"></i> Meu Estágio Ativo</h2>
                    <p class="mb-0 small"><?php echo h($contratoAtivo['empr_nome']); ?> | 
                        Início: <?php echo date('d/m/Y', strtotime(h($contratoAtivo['cntr_data_inicio']))); ?> - 
                        Término: <?php echo date('d/m/Y', strtotime(h($contratoAtivo['cntr_data_fim']))); ?>
                    </p>
                </div>

                <div class="card-body p-4">
                    <?php
                        // Cálculo do percentual de dias restantes (Lógica mantida)
                        $dataInicio = new DateTime($contratoAtivo['cntr_data_inicio']);
                        $dataFim = new DateTime($contratoAtivo['cntr_data_fim']);
                        $dataAtual = new DateTime();
                        $intervalo = $dataInicio->diff($dataFim);
                        $diasTotais = $intervalo->days;
                        
                        // Garante que dias restantes não seja negativo se a data já passou
                        $diasRestantesInterval = $dataAtual->diff($dataFim);
                        $diasRestantes = ($diasRestantesInterval->invert == 1) ? 0 : $diasRestantesInterval->days;

                        // Evita divisão por zero
                        $percentual = 0;
                        if ($diasTotais > 0) {
                            $diasCorridos = $diasTotais - $diasRestantes;
                            $percentual = ($diasCorridos / $diasTotais) * 100;
                        }
                        if ($percentual > 100) $percentual = 100;
                    ?>

                    <h5 class="small fw-bold">Progresso do Contrato (<?php echo $diasRestantes; ?> dias restantes)</h5>
                    <div class="progress mb-4" style="height: 20px;">
                        <div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: <?php echo $percentual; ?>%;" aria-valuenow="<?php echo $percentual; ?>" aria-valuemin="0" aria-valuemax="100">
                            <?php echo round($percentual, 1); ?>%
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-dark"><i class="fas fa-building me-1"></i> Empresa</h5>
                            <dl class="row small">
                                <dt class="col-sm-4">Endereço:</dt>
                                <dd class="col-sm-8"><?php echo h($contratoAtivo['empr_endereco']); ?></dd>
                                
                                <dt class="col-sm-4">Cidade:</dt>
                                <dd class="col-sm-8"><?php echo h($contratoAtivo['empr_cidade']); ?></dd>

                                <dt class="col-sm-4">Contato 1:</dt>
                                <dd class="col-sm-8"><?php echo h($contratoAtivo['empr_contato_1']); ?></dd>

                                <?php if (!empty($contratoAtivo['empr_contato_2'])): ?>
                                <dt class="col-sm-4">Contato 2:</dt>
                                <dd class="col-sm-8"><?php echo h($contratoAtivo['empr_contato_2']); ?></dd>
                                <?php endif; ?>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-dark"><i class="fas fa-info-circle me-1"></i> Detalhes do Contrato</h5>
                            <dl class="row small">
                                <dt class="col-sm-4">Horário:</dt>
                                <dd class="col-sm-8"><?php echo h($contratoAtivo['cntr_hora_inicio']); ?> às <?php echo h($contratoAtivo['cntr_hora_final']); ?></dd>
                            </dl>
                            <h5 class="text-dark"><i class="fas fa-file-contract me-1"></i> Documentos</h5>
                            <dl class="row small">
                                <dt class="col-sm-4">Termo:</dt>
                                <dd class="col-sm-8"><a href="<?php echo BASE_URL . h($contratoAtivo['cntr_termo_contrato']); ?>" target="_blank"><i class="fas fa-file-pdf"></i> Ver Contrato</a></dd>
                                
                                <?php if (!empty($contratoAtivo['cntr_anexo_extra'])): ?>
                                <dt class="col-sm-4">Anexo:</dt>
                                <dd class="col-sm-8"><a href="<?php echo BASE_URL . h($contratoAtivo['cntr_anexo_extra']); ?>" target="_blank"><i class="fas fa-file-pdf"></i> Ver Anexo Extra</a></dd>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Seção dos Relatórios (Lado a Lado) -->
                    <h4 class="mb-3"><i class="fas fa-folder-open me-1"></i> Meus Relatórios</h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <!-- Acordeão Relatório INICIAL -->
                            <div class="accordion" id="accordionRelatorioInicial">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingRelatorioInicial">

                                        <?php $controleRelatorioInicial = 0; // Criação de variável para controle de status do relatório inicial ?>

                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRelatorioInicial" aria-expanded="true" aria-controls="collapseRelatorioInicial">
                                            Relatório Inicial &nbsp;
                                            <?php if ($relatorioInicial): // Verifica se $relatorioInicial foi carregado ?>
                                                <?php if (empty($relatorioInicial['rini_assinatura'])): ?>
                                                    <span class="badge bg-warning text-dark">Aguardando Assinatura</span>
                                                    <?php $controleRelatorioInicial = 1; ?>
                                                <?php elseif (!empty($relatorioInicial['rini_assinatura']) && $relatorioInicial['rini_aprovado'] == 0): ?>
                                                    <span class="badge bg-warning text-dark">Aguardando Validação</span>
                                                    <?php $controleRelatorioInicial = 2; ?>
                                                <?php elseif (!empty($relatorioInicial['rini_assinatura']) && $relatorioInicial['rini_aprovado'] == 1): ?>
                                                    <span class="badge bg-success">Aprovado</span>
                                                    <?php $controleRelatorioInicial = 3; ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Não enviado</span>
                                                <?php $controleRelatorioInicial = 0; ?>
                                            <?php endif; ?>
                                        </button>
                                    </h2>
                                    <div id="collapseRelatorioInicial" class="accordion-collapse collapse" aria-labelledby="headingRelatorioInicial" data-bs-parent="#accordionRelatorioInicial">
                                        <div class="accordion-body">

                                            <?php if ($controleRelatorioInicial == 0): ?>
                                                <p>Relatório inicial não enviado.</p>
                                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRelatorioInicial">Preencher Relatório Inicial</button>
                                            
                                            <?php elseif ($controleRelatorioInicial == 1): ?>
                                                <p>Relatório inicial salvo. Faça o download, assine e envie o PDF.</p>
                                                <a href="<?php echo BASE_URL; ?>backend/relatorio-inicial/imprimir-pdf.php?cntr_id=<?php echo h($contratoAtivo['cntr_id']); ?>" class="btn btn-secondary mb-2"><i class="fas fa-print"></i> Baixar PDF</a>
                                                <button class="btn btn-outline-secondary mb-2" data-bs-toggle="modal" data-bs-target="#modalEditarRelatorioInicial"><i class="fas fa-edit"></i> Editar</button>
                                                <button class="btn btn-outline-danger mb-2" data-bs-toggle="modal" data-bs-target="#modalRefazerRelatorioInicial"><i class="fas fa-trash-alt"></i> Cancelar</button>

                                                <form action="<?php echo BASE_URL;?>backend/relatorio-inicial/enviar-pdf.php" method="POST" enctype="multipart/form-data" class="mt-3 border-top pt-3">
                                                    <div class="mb-3">
                                                        <label for="relatorio_inicial" class="form-label fw-bold">Anexe o relatório assinado:</label>
                                                        <input type="file" class="form-control" id="relatorio_inicial" name="relatorio_inicial" accept=".pdf" required>
                                                    </div>
                                                    <input type="hidden" name="user_id" value="<?php echo h($user_id); ?>">
                                                    <input type="hidden" name="cntr_id" value="<?php echo h($contratoAtivo['cntr_id']); ?>">
                                                    <input type="hidden" name="rini_id" value="<?php echo h($relatorioInicial['rini_id']); ?>">
                                                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-upload"></i> Enviar PDF Assinado</button>
                                                </form>
                                            
                                            <?php elseif ($controleRelatorioInicial == 2): ?>
                                                <p>Relatório assinado enviado. Aguardando validação da coordenação.</p>
                                                <a href="<?php echo BASE_URL . h($relatorioInicial['rini_assinatura']); ?>" target="_blank" class="btn btn-secondary mb-2"><i class="fas fa-eye"></i> Ver PDF Enviado</a>
                                                <button class="btn btn-danger mb-2" data-bs-toggle="modal" data-bs-target="#modalCancelarEnvioRelatorioInicial">Cancelar Envio</button>

                                            <?php elseif ($controleRelatorioInicial == 3): ?>
                                                <p>Relatório inicial aprovado. Parabéns!</p>
                                                <a href="<?php echo BASE_URL . h($relatorioInicial['rini_assinatura']); ?>" target="_blank" class="btn btn-success mb-2 w-100"><i class="fas fa-check-circle"></i> Ver Documento Aprovado</a>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <!-- Acordeão Relatório FINAL -->
                            <div class="accordion" id="accordionRelatorioFinal">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingRelatorioFinal">

                                        <?php $controleRelatorioFinal = 0; // Criação de variável para controle de status do relatório final ?>

                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRelatorioFinal" aria-expanded="false" aria-controls="collapseRelatorioFinal">
                                            Relatório Final &nbsp;

                                            <?php if ($relatorioFinal): // Verifica se $relatorioFinal foi carregado ?>
                                                <?php if (empty($relatorioFinal['rfin_assinatura'])): ?>
                                                    <span class="badge bg-warning text-dark">Aguardando Assinatura</span>
                                                    <?php $controleRelatorioFinal = 1; ?>
                                                <?php elseif (!empty($relatorioFinal['rfin_assinatura']) && $relatorioFinal['rfin_aprovado'] == 0): ?>
                                                    <span class="badge bg-warning text-dark">Aguardando Validação</span>
                                                    <?php $controleRelatorioFinal = 2; ?>
                                                <?php elseif (!empty($relatorioFinal['rfin_assinatura']) && $relatorioFinal['rfin_aprovado'] == 1): ?>
                                                    <span class="badge bg-success">Aprovado</span>
                                                    <?php $controleRelatorioFinal = 3; ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Não enviado</span>
                                                <?php $controleRelatorioFinal = 0; ?>
                                            <?php endif; ?>
                                        </button>
                                    </h2>
                                    <div id="collapseRelatorioFinal" class="accordion-collapse collapse" aria-labelledby="headingRelatorioFinal" data-bs-parent="#accordionRelatorioFinal">
                                        <div class="accordion-body">

                                            <?php if ($controleRelatorioInicial != 3): // Checar se o relatório inicial foi APROVADO ?>
                                                <div class="alert alert-warning small" role="alert">
                                                    <i class="fas fa-lock"></i> Relatório final só pode ser enviado após o <b>Relatório Inicial</b> ser aprovado.
                                                </div>
                                            <?php else : ?>

                                                <?php if ($controleRelatorioFinal == 0): ?>
                                                    <p>Relatório final não enviado.</p>
                                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRelatorioFinal">Preencher Relatório Final</button>
                                                
                                                <?php elseif ($controleRelatorioFinal == 1): ?>
                                                    <p>Relatório final salvo. Faça o download, assine e envie o PDF.</p>
                                                    <a href="backend/relatorio-final/imprimir-pdf.php?cntr_id=<?php echo h($contratoAtivo['cntr_id']); ?>" class="btn btn-secondary mb-2"><i class="fas fa-print"></i> Baixar PDF</a>
                                                    <button class="btn btn-outline-secondary mb-2" data-bs-toggle="modal" data-bs-target="#modalEditarRelatorioFinal"><i class="fas fa-edit"></i> Editar</button>
                                                    <button class="btn btn-outline-danger mb-2" data-bs-toggle="modal" data-bs-target="#modalRefazerRelatorioFinal"><i class="fas fa-trash-alt"></i> Cancelar</button>

                                                    <form action="<?php echo BASE_URL;?>backend/relatorio-final/enviar-pdf.php" method="POST" enctype="multipart/form-data" class="mt-3 border-top pt-3">
                                                        <div class="mb-3">
                                                            <label for="relatorio_final" class="form-label fw-bold">Anexe o relatório assinado:</label>
                                                            <input type="file" class="form-control" id="relatorio_final" name="relatorio_final" accept=".pdf" required>
                                                        </div>
                                                        <input type="hidden" name="user_id" value="<?php echo h($user_id); ?>">
                                                        <input type="hidden" name="cntr_id" value="<?php echo h($contratoAtivo['cntr_id']); ?>">
                                                        <input type="hidden" name="rfin_id" value="<?php echo h($relatorioFinal['rfin_id']); ?>">
                                                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-upload"></i> Enviar PDF Assinado</button>
                                                    </form>

                                                <?php elseif ($controleRelatorioFinal == 2): ?>
                                                    <p>Relatório assinado enviado. Aguardando validação da coordenação.</p>
                                                    <a href="<?php echo BASE_URL . h($relatorioFinal['rfin_assinatura']); ?>" target="_blank" class="btn btn-secondary mb-2"><i class="fas fa-eye"></i> Ver PDF Enviado</a>
                                                    <button class="btn btn-danger mb-2" data-bs-toggle="modal" data-bs-target="#modalCancelarEnvioRelatorioFinal">Cancelar Envio</button>

                                                <?php elseif ($controleRelatorioFinal == 3): ?>
                                                    <p>Relatório final aprovado. Parabéns!</p>
                                                    <a href="<?php echo BASE_URL . h($relatorioFinal['rfin_assinatura']); ?>" target="_blank" class="btn btn-success mb-2 w-100"><i class="fas fa-check-circle"></i> Ver Documento Aprovado</a>
                                                <?php endif; ?>

                                            <?php endif; // Endif da verificação do relatório inicial para exibir o relatório final ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        <?php endif; ?>
    </div>


    <!-- **** MODALS **** -->
    <!-- Todos os Modals são movidos para o final do body para garantir o funcionamento -->

    <!-- Modal: Preencher Relatório INICIAL -->
    <div class="modal fade" id="modalRelatorioInicial" tabindex="-1" aria-labelledby="modalRelatorioInicialLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRelatorioInicialLabel">Preencher Relatório Inicial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Preencha o formulário abaixo para enviar o relatório inicial.</p>
                    <form action="<?php echo BASE_URL;?>backend/relatorio-inicial/create.php" method="POST" enctype="multipart/form-data">
                        <!-- Campo texto 1023 caracteres: Discorra sobre a forma como ocorreu a sua contratação: -->
                        <div class="mb-3">
                            <label for="rini_como_ocorreu" class="form-label">Discorra sobre a forma como ocorreu a sua contratação:</label>
                            <textarea class="form-control" id="rini_como_ocorreu" name="rini_como_ocorreu" rows="3" maxlength="1023" required></textarea>
                        </div>

                        <!-- Campo texto 1023 caracteres: Comente sobre o desenvolvimento de seu cronograma de estágio -->
                        <div class="mb-3">
                            <label for="rini_dev_cronograma" class="form-label">Comente sobre o desenvolvimento de seu cronograma de estágio:</label>
                            <textarea class="form-control" id="rini_dev_cronograma" name="rini_dev_cronograma" rows="3" maxlength="1023" required></textarea>
                        </div>

                        <!-- Campo texto 1023 caracteres: Discorra sobre como foi sua preparação para o início do estágio -->
                        <div class="mb-3">
                            <label for="rini_preparacao_inicio" class="form-label">Discorra sobre como foi sua preparação para o início do estágio:</label>
                            <textarea class="form-control" id="rini_preparacao_inicio" name="rini_preparacao_inicio" rows="3" maxlength="1023" required></textarea>
                        </div>

                        <!-- Campo de atividades, onde a atividade é numerada e tem um texto de comentário do lado, elá terá um botão que irá adicionar atividades a partir que aperta ele -->
                        <div class="mb-3">
                            <label for="atividades" class="form-label">Atividades desenvolvidas:</label>
                            <div id="atividades-container">
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <textarea class="form-control" name="atividade1" placeholder="Atividade 1" rows="3" maxlength="1023" required></textarea>
                                    </div>
                                    <div class="col-6">
                                        <textarea class="form-control" name="comentario1" placeholder="Comentário" rows="3" maxlength="1023" required></textarea>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary" id="add-atividade">Adicionar Atividade</button>
                        </div>

                        <!-- Campo texto 1023 caracteres: Discorra sobre as dificuldades encontradas no desenvolvimento e como foram solucionadas -->
                        <div class="mb-3">
                            <label for="rini_dificul_encontradas" class="form-label">Discorra sobre as dificuldades encontradas no desenvolvimento e como foram solucionadas:</label>
                            <textarea class="form-control" id="rini_dificul_encontradas" name="rini_dificul_encontradas" rows="3" maxlength="1023" required></textarea>
                        </div>

                        <!-- Campo texto 1023 caracteres: Discorra sobre as aplicações de conhecimentos desenvolvidos pelas disciplinas do curso, relacionando a atividade na qual ocorreu, as disciplinas envolvidas com elas e as contribuições que cada disciplina propiciou: -->
                        <div class="mb-3">
                            <label for="rini_aplic_conhecimento" class="form-label">Discorra sobre as aplicações de conhecimentos desenvolvidos pelas disciplinas do curso, relacionando a atividade na qual ocorreu, as disciplinas envolvidas com elas e as contribuições que cada disciplina propiciou:</label>
                            <textarea class="form-control" id="rini_aplic_conhecimento" name="rini_aplic_conhecimento" rows="3" maxlength="1023" required></textarea>
                        </div>

                        <!-- Campo texto 1023 caracteres: Houve contato com novas ferramentas, técnicas e/ou métodos, diferentes dos aprendidos durante o curso? Em caso positivo, cite-os e comente-os: -->
                        <div class="mb-3">
                            <label for="rini_novas_ferramentas" class="form-label">Houve contato com novas ferramentas, técnicas e/ou métodos, diferentes dos aprendidos durante o curso? Em caso positivo, cite-os e comente-os:</label>
                            <textarea class="form-control" id="rini_novas_ferramentas" name="rini_novas_ferramentas" rows="3" maxlength="1023" required></textarea>
                        </div>

                        <!-- Campo texto 1023 caracteres: Outros comentários desejáveis: -->
                        <div class="mb-3">
                            <label for="rini_comentarios" class="form-label">Outros comentários desejáveis:</label>
                            <textarea class="form-control" id="rini_comentarios" name="rini_comentarios" rows="3" maxlength="1023"></textarea>
                        </div>

                        <!-- campo anexo de arquivo 1 e arquivo 2: Se desejável, anexe outros documentos relativos às atividades de estágio ou críticas e sugestões sobre este formulário. -->
                        <p class="form-text">Se desejável, anexe outros documentos relativos às atividades de estágio ou críticas e sugestões sobre este formulário.</p>
                        <div class="mb-3">
                            <label for="rini_anexo_1" class="form-label">Anexo 1</label>
                            <input type="file" class="form-control" id="rini_anexo_1" name="rini_anexo_1">
                        </div>

                        <div class="mb-3">
                            <label for="rini_anexo_2" class="form-label">Anexo 2</label>
                            <input type="file" class="form-control" id="rini_anexo_2" name="rini_anexo_2">
                        </div>

                        <input type="hidden" name="cntr_id" value="<?php echo $contratoAtivo['cntr_id']; ?>">

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Enviar Relatório</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Preencher Relatório FINAL -->
    <div class="modal fade" id="modalRelatorioFinal" tabindex="-1" aria-labelledby="modalRelatorioFinalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRelatorioFinalLabel">Preencher Relatório Final</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Preencha o formulário abaixo para enviar o relatório final.</p>
                    <form action="<?php echo BASE_URL;?>backend/relatorio-final/create.php" method="POST" enctype="multipart/form-data">
                        <!-- Campo texto 1023 caracteres: Apresentar, em forma de texto (não em tópicos), uma síntese sobre a empresa onde foi realizado o estágio; nesta síntese devem estar contidos: - Histórico da empresa; - Perfil da empresa; - Descrição do setor onde o estágio foi realizado (apresentar as principais atividades do setor). -->
                        <div class="mb-3">
                            <label for="rfin_sintese_empresa" class="form-label">Apresentar, em forma de texto (não em tópicos), uma síntese sobre a empresa onde foi realizado o estágio; nesta síntese devem estar contidos: <br>
                                - Histórico da empresa; <br>
                                - Perfil da empresa; <br>
                                - Descrição do setor onde o estágio foi realizado (apresentar as principais atividades do setor).</label>
                            <textarea class="form-control" id="rfin_sintese_empresa" name="rfin_sintese_empresa" rows="9" maxlength="1023" required></textarea>
                        </div>
                        <!-- Campo de atividades, onde a atividade é numerada, tem um texto de resumo e disciplina relacionada a essa atividade, elá terá um botão que irá adicionar atividades a partir que aperta ele, ficando uma linha com atividade / resumo / disciplina -->
                        <div class="mb-3">
                            <label for="atividades-final" class="form-label">Relacione e comente as atividades desenvolvidas no período total de estágio:</label>
                            <div id="atividades-container-final">
                                <div class="row mb-2">
                                    <div class="col-4">
                                        <textarea class="form-control" name="atividade1_final" placeholder="Atividade 1" rows="3" maxlength="1023" required></textarea>
                                    </div>
                                    <div class="col-4">
                                        <textarea class="form-control" name="resumo1_final" placeholder="Resumo da Atividade 1" rows="3" maxlength="1023" required></textarea>
                                    </div>
                                    <div class="col-4">
                                        <textarea class="form-control" name="disciplina1_final" placeholder="Disciplina Relacionada 1" rows="3" maxlength="1023" required></textarea>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary" id="add-atividade-final">Adicionar Atividade</button>
                        </div>

                        <!-- campo anexo de arquivo 1 e arquivo 2: Se desejável, anexe outros documentos relativos às atividades de estágio ou críticas e sugestões sobre este formulário. -->
                        <p class="form-text">Se desejável, anexe outros documentos relativos às atividades de estágio ou críticas e sugestões sobre este formulário.</p>
                        <div class="mb-3">
                            <label for="rfin_anexo_1" class="form-label">Anexo 1</label>
                            <input type="file" class="form-control" id="rfin_anexo_1" name="rfin_anexo_1">
                        </div>
                        <div class="mb-3">
                            <label for="rfin_anexo_2" class="form-label">Anexo 2</label>
                            <input type="file" class="form-control" id="rfin_anexo_2" name="rfin_anexo_2">
                        </div>

                        <input type="hidden" name="cntr_id" value="<?php echo $contratoAtivo['cntr_id']; ?>">
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Enviar Relatório</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals de Edição e Confirmação (só são criados se o relatório correspondente existir) -->
    <?php if ($relatorioInicial): ?> 
        <!-- Modal: Editar Relatório INICIAL -->
        <div class="modal fade" id="modalEditarRelatorioInicial" tabindex="-1" aria-labelledby="modalEditarRelatorioInicialLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarRelatorioInicialLabel">Editar Relatório Inicial</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Edite o formulário abaixo para atualizar o relatório inicial.</p>
                        <form id="formEditarRelatorioInicial" action="<?php echo BASE_URL;?>backend/relatorio-inicial/update.php" method="POST" enctype="multipart/form-data">
                            
                            <div class="mb-3">
                                <label for="rini_como_ocorreu_edit" class="form-label">Discorra sobre a forma como ocorreu a sua contratação:</label>
                                <textarea class="form-control" name="rini_como_ocorreu_edit" rows="3" required><?php echo h($relatorioInicial['rini_como_ocorreu']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="rini_dev_cronograma_edit" class="form-label">Comente sobre o desenvolvimento de seu cronograma de estágio:</label>
                                <textarea class="form-control" name="rini_dev_cronograma_edit" rows="3" required><?php echo h($relatorioInicial['rini_dev_cronograma']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="rini_preparacao_inicio_edit" class="form-label">Discorra sobre como foi sua preparação para o início do estágio:</label>
                                <textarea class="form-control" name="rini_preparacao_inicio_edit" rows="3" required><?php echo h($relatorioInicial['rini_preparacao_inicio']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Atividades desenvolvidas (Atividade / Comentário):</label>
                                <div id="atividades-container-edit">
                                    <?php foreach ($atividadesRelatorioInicial as $key => $atividade): ?>
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <textarea class="form-control" name="atividade<?php echo $key + 1; ?>_edit" placeholder="Atividade <?php echo $key + 1; ?>"><?php echo h($atividade['atvi_atividade']); ?></textarea> 
                                            </div>
                                            <div class="col-6">
                                                <textarea class="form-control" name="comentario<?php echo $key + 1; ?>_edit" placeholder="Comentário <?php echo $key + 1; ?>"><?php echo h($atividade['atvi_comentario']); ?></textarea>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <!-- Script JS em 'myScripts.js' deve cuidar de adicionar/remover campos dinamicamente -->
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="add-atividade-edit">Adicionar Atividade</button>
                            </div>
                            
                            <div class="mb-3">
                                <label for="rini_dificul_encontradas_edit" class="form-label">Discorra sobre as dificuldades encontradas...</label>
                                <textarea class="form-control" name="rini_dificul_encontradas_edit" rows="3" required><?php echo h($relatorioInicial['rini_dificul_encontradas']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="rini_aplic_conhecimento_edit" class="form-label">Discorra sobre as aplicações de conhecimentos...</label>
                                <textarea class="form-control" name="rini_aplic_conhecimento_edit" rows="3" required><?php echo h($relatorioInicial['rini_aplic_conhecimento']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="rini_novas_ferramentas_edit" class="form-label">Houve contato com novas ferramentas...</label>
                                <textarea class="form-control" name="rini_novas_ferramentas_edit" rows="3" required><?php echo h($relatorioInicial['rini_novas_ferramentas']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="rini_comentarios_edit" class="form-label">Outros comentários desejáveis:</label>
                                <textarea class="form-control" name="rini_comentarios_edit" rows="3"><?php echo h($relatorioInicial['rini_comentarios']); ?></textarea>
                            </div>
                            
                            <input type="hidden" name="rini_id_edit" id="rini_id_edit" value="<?php echo h($relatorioInicial['rini_id']); ?>">
                            <input type="hidden" name="cntr_id_edit" value="<?php echo h($contratoAtivo['cntr_id']); ?>">
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                <button type="submit" class="btn btn-primary">Atualizar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Cancelar Relatório INICIAL (antes do envio do PDF) -->
        <div class="modal fade" id="modalRefazerRelatorioInicial" tabindex="-1" aria-labelledby="modalRefazerRelatorioInicialLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalRefazerRelatorioInicialLabel">Cancelar Relatório</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Tem certeza que deseja cancelar este relatório? Todo o preenchimento será perdido e você terá que refazê-lo.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                        <form action="<?php echo BASE_URL;?>backend/relatorio-inicial/delete.php" method="POST">
                            <input type="hidden" name="cntr_id" value="<?php echo h($contratoAtivo['cntr_id']); ?>">
                            <input type="hidden" name="rini_id" value="<?php echo h($relatorioInicial['rini_id']); ?>">
                            <button type="submit" class="btn btn-danger">Sim, cancelar relatório</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Cancelar Envio (PDF) Relatório INICIAL -->
        <div class="modal fade" id="modalCancelarEnvioRelatorioInicial" tabindex="-1" aria-labelledby="modalCancelarEnvioRelatorioInicialLabel" aria-hidden="true">
             <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCancelarEnvioRelatorioInicialLabel">Cancelar Envio de PDF</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Tem certeza que deseja cancelar o envio deste PDF? O relatório voltará ao status "Aguardando Assinatura".
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                        <form action="<?php echo BASE_URL; ?>backend/relatorio-inicial/excluir-pdf.php" method="POST">
                            <input type="hidden" name="rini_id" value="<?php echo h($relatorioInicial['rini_id']); ?>">
                            <button type="submit" class="btn btn-danger">Sim, cancelar envio</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($relatorioFinal): ?>
        <!-- Modal: Editar Relatório FINAL -->
        <div class="modal fade" id="modalEditarRelatorioFinal" tabindex="-1" aria-labelledby="modalEditarRelatorioFinalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarRelatorioFinalLabel">Editar Relatório Final</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Edite o formulário abaixo para atualizar o relatório final.</p>
                        <form id="formEditarRelatorioFinal" action="<?php echo BASE_URL;?>backend/relatorio-final/update.php" method="POST" enctype="multipart/form-data">
                            
                            <div class="mb-3">
                                <label for="rfin_sintese_empresa_edit" class="form-label">Apresentar, em forma de texto...</label>
                                <textarea class="form-control" name="rfin_sintese_empresa_edit" rows="5" required><?php echo h($relatorioFinal['rfin_sintese_empresa']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Relacione as atividades (Atividade / Resumo / Disciplina Relacionada):</label>
                                <div id="atividades-container-final-edit">
                                    <?php foreach ($atividadesRelatorioFinal as $key => $atividade): ?>
                                        <div class="row mb-2">
                                            <div class="col-4">
                                                <textarea class="form-control" name="atividade<?php echo $key + 1; ?>_final_edit" placeholder="Atividade <?php echo $key + 1; ?>"><?php echo h($atividade['atvf_atividade']); ?></textarea>
                                            </div>
                                            <div class="col-4">
                                                <textarea class="form-control" name="resumo<?php echo $key + 1; ?>_final_edit" placeholder="Resumo <?php echo $key + 1; ?>"><?php echo h($atividade['atvf_resumo']); ?></textarea>
                                            </div>
                                            <div class="col-4">
                                                <textarea class="form-control" name="disciplina<?php echo $key + 1; ?>_final_edit" placeholder="Disciplina <?php echo $key + 1; ?>"><?php echo h($atividade['atvf_disciplina_relacionada']); ?></textarea>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <!-- Script JS em 'myScripts.js' deve cuidar de adicionar/remover campos dinamicamente -->
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="add-atividade-final-edit">Adicionar Atividade</button>
                            </div>
                            
                            <input type="hidden" name="rfin_id_edit" id="rfin_id_edit" value="<?php echo h($relatorioFinal['rfin_id']); ?>">
                            <input type="hidden" name="cntr_id_edit" value="<?php echo h($contratoAtivo['cntr_id']); ?>">
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                <button type="submit" class="btn btn-primary">Atualizar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Cancelar Relatório FINAL (antes do envio do PDF) -->
        <div class="modal fade" id="modalRefazerRelatorioFinal" tabindex="-1" aria-labelledby="modalRefazerRelatorioFinalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalRefazerRelatorioFinalLabel">Cancelar Relatório</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                         Tem certeza que deseja cancelar este relatório? Todo o preenchimento será perdido e você terá que refazê-lo.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                        <form action="<?php echo BASE_URL;?>backend/relatorio-final/delete.php" method="POST">
                            <input type="hidden" name="cntr_id" value="<?php echo h($contratoAtivo['cntr_id']); ?>">
                            <input type="hidden" name="rfin_id" value="<?php echo h($relatorioFinal['rfin_id']); ?>">
                            <button type="submit" class="btn btn-danger">Sim, cancelar relatório</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal: Cancelar Envio (PDF) Relatório FINAL -->
        <div class="modal fade" id="modalCancelarEnvioRelatorioFinal" tabindex="-1" aria-labelledby="modalCancelarEnvioRelatorioFinalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title" id="modalCancelarEnvioRelatorioFinalLabel">Cancelar Envio de PDF</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                         Tem certeza que deseja cancelar o envio deste PDF? O relatório voltará ao status "Aguardando Assinatura".
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                        <form action="<?php echo BASE_URL; ?>backend/relatorio-final/excluir-pdf.php" method="POST">
                            <input type="hidden" name="rfin_id" value="<?php echo h($relatorioFinal['rfin_id']); ?>">
                            <button type="submit" class="btn btn-danger">Sim, cancelar envio</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <?php require 'components/footer.php'; ?>
</body>
</html>