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

<body class="bg-light">
    <?php require 'components/navbar.php'; ?>

    <section class="container-fluid mt-4">
        <div class="p-5 mx-2 bg-white rounded-4">
            <div class="row">
                <div class="col-md-4 d-flex justify-content-center align-items-center">
                    <i class="fa-solid fa-circle-user fa-10x"></i>
                </div>
                <div class="col-md-8">
                    <p class="fs-1">Bem vindo, <?php echo h($usuario['user_nome']); ?>!</p>
                    <p class="fs-5"><strong>Curso:</strong> <?php echo h($usuario['curs_nome']); ?></p>
                    <p class="fs-5"><strong>RA:</strong> <?php echo h($usuario['user_ra']); ?></p>
                    <p class="fs-5"><strong>E-mail:</strong> <?php echo h($usuario['user_login']); ?></p>
                    <p class="fs-5"><strong>Contato:</strong> <?php echo h($usuario['user_contato']); ?></p>
                </div>
            </div>
        </div>
    </section>

    <section class="container-fluid mt-4">
        <div class="p-5 mx-2 bg-white rounded-4">
            <div class="row">
                <div class="col-md-4"></div>
                <div class="col-md-8">
                    <p class="fs-1">Contratos Ativos</p>
                </div>
            </div>
            <?php if ($contratoAtivo == null): ?>
                <div class="alert alert-warning" role="alert">
                    Você não possui nenhum contrato ativo. Por favor, entre em contato com a coordenação do curso se isso é um erro.
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-md-4 d-flex justify-content-center align-items-center">
                        <i class="fa-solid fa-suitcase fa-10x"></i>
                    </div>
                    <div class="col-md-8">

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

                        <div class="progress mb-4">
                            <div class="progress-bar" role="progressbar" style="width: <?php echo $percentual; ?>%;" aria-valuenow="<?php echo $percentual; ?>" aria-valuemin="0" aria-valuemax="100">
                                <?php echo round($percentual, 2); ?>%
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <p class="fs-5"><strong>Empresa:</strong> <?php echo h($contratoAtivo['empr_nome']); ?></p>
                                <p class="fs-5"><strong>Endereço:</strong> <?php echo h($contratoAtivo['empr_endereco']); ?></p>
                                <p class="fs-5"><strong>Contato:</strong> <?php echo h($contratoAtivo['empr_contato_1']); ?></p>
                                <?php if (!empty($contratoAtivo['empr_contato_2'])): ?>
                                    <p class="fs-5"><strong>Contato:</strong> <?php echo h($contratoAtivo['empr_contato_2']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <p class="fs-5"><strong>Data de Início:</strong> <?php echo date('d/m/Y', strtotime(h($contratoAtivo['cntr_data_inicio']))); ?></p>
                                <p class="fs-5"><strong>Data de Término:</strong> <?php echo date('d/m/Y', strtotime(h($contratoAtivo['cntr_data_fim']))); ?></p>
                                <p class="fs-5"><strong>Contrato: </strong><a href="<?php echo h($contratoAtivo['cntr_termo_contrato']); ?>" target="_blank"> Ver Contrato</a></p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">

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
                                                    <p>Relatório inicial enviado, mas ainda não assinado. Por favor, faça o download do relatório inicial, assine e envie em PDF.</p>
                                                    <a href="<?php echo BASE_URL; ?>backend/relatorio-inicial/imprimir-pdf.php?cntr_id=<?php echo h($contratoAtivo['cntr_id']); ?>" class="btn btn-secondary mb-2">Baixar PDF</a>
                                                    <button class="btn btn-secondary mb-2" data-bs-toggle="modal" data-bs-target="#modalEditarRelatorioInicial">Editar relatório</button>
                                                    <button class="btn btn-danger mb-2" data-bs-toggle="modal" data-bs-target="#modalRefazerRelatorioInicial">Cancelar relatório</button>

                                                    <div class="modal fade" id="modalRefazerRelatorioInicial" ...>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                                                            <form action="<?php echo BASE_URL;?>backend/relatorio-inicial/delete.php" method="POST">
                                                                <input type="hidden" name="cntr_id" value="<?php echo h($contratoAtivo['cntr_id']); ?>">
                                                                <input type="hidden" name="rini_id" value="<?php echo h($relatorioInicial['rini_id']); ?>">
                                                                <button type="submit" class="btn btn-danger">Cancelar relatório</button>
                                                            </form>
                                                        </div>
                                                        </div>

                                                    <form action="<?php echo BASE_URL;?>backend/relatorio-inicial/enviar-pdf.php" method="POST" enctype="multipart/form-data">
                                                        <div class="mb-3">
                                                            <label for="relatorio_inicial" class="form-label">Anexe e envie o relatório assinado.</label>
                                                            <input type="file" class="form-control" id="relatorio_inicial" name="relatorio_inicial" required>
                                                        </div>
                                                        <input type="hidden" name="user_id" value="<?php echo h($user_id); ?>">
                                                        <input type="hidden" name="cntr_id" value="<?php echo h($contratoAtivo['cntr_id']); ?>">
                                                        <input type="hidden" name="rini_id" value="<?php echo h($relatorioInicial['rini_id']); ?>">
                                                        <button type="submit" class="btn btn-primary">Enviar</button>
                                                    </form>
                                                
                                                <?php elseif ($controleRelatorioInicial == 2): ?>
                                                    <p>Relatório inicial enviado e assinado, mas ainda não validado. Por favor, aguarde a validação do relatório inicial.</p>
                                                    <a href="<?php echo BASE_URL . h($relatorioInicial['rini_assinatura']); ?>" target="_blank" class="btn btn-secondary mb-2">Baixar PDF</a>
                                                    <button class="btn btn-danger mb-2" data-bs-toggle="modal" data-bs-target="#modalCancelarEnvioRelatorioInicial">Cancelar Envio</button>

                                                    <div class="modal fade" id="modalCancelarEnvioRelatorioInicial" ...>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                                                            <form action="<?php echo BASE_URL; ?>backend/relatorio-inicial/excluir-pdf.php" method="POST">
                                                                <input type="hidden" name="rini_id" value="<?php echo h($relatorioInicial['rini_id']); ?>">
                                                                <button type="submit" class="btn btn-danger">Cancelar Envio</button>
                                                            </form>
                                                        </div>
                                                        </div>

                                                <?php elseif ($controleRelatorioInicial == 3): ?>
                                                    <p>Relatório inicial enviado, assinado e validado. Parabéns!</p>
                                                    <a href="<?php echo BASE_URL . h($relatorioInicial['rini_assinatura']); ?>" target="_blank" class="btn btn-secondary mb-2">Baixar PDF</a>
                                                <?php endif; ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
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
                                                    <p>Relatório final não pode ser enviado antes do relatório inicial ser aprovado.</p>
                                                <?php else : ?>

                                                    <?php if ($controleRelatorioFinal == 0): ?>
                                                        <p>Relatório final não enviado.</p>
                                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRelatorioFinal">Preencher Relatório Final</button>
                                                    
                                                    <?php elseif ($controleRelatorioFinal == 1): ?>
                                                        <p>Relatório final enviado, mas ainda não assinado. Por favor, faça o download do relatório final, assine e envie em PDF.</p>
                                                        <a href="backend/relatorio-final/imprimir-pdf.php?cntr_id=<?php echo h($contratoAtivo['cntr_id']); ?>" class="btn btn-secondary mb-2">Baixar PDF</a>
                                                        <button class="btn btn-secondary mb-2" data-bs-toggle="modal" data-bs-target="#modalEditarRelatorioFinal">Editar relatório</button>
                                                        <button class="btn btn-danger mb-2" data-bs-toggle="modal" data-bs-target="#modalRefazerRelatorioFinal">Cancelar relatório</button>

                                                        <div class="modal fade" id="modalRefazerRelatorioFinal" ...>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                                                                <form action="<?php echo BASE_URL;?>backend/relatorio-final/delete.php" method="POST">
                                                                    <input type="hidden" name="cntr_id" value="<?php echo h($contratoAtivo['cntr_id']); ?>">
                                                                    <input type="hidden" name="rfin_id" value="<?php echo h($relatorioFinal['rfin_id']); ?>">
                                                                    <button type="submit" class="btn btn-danger">Cancelar relatório</button>
                                                                </form>
                                                            </div>
                                                            </div>

                                                        <form action="<?php echo BASE_URL;?>backend/relatorio-final/enviar-pdf.php" method="POST" enctype="multipart/form-data">
                                                            <div class="mb-3">
                                                                <label for="relatorio_final" class="form-label">Anexe e envie o relatório assinado.</label>
                                                                <input type="file" class="form-control" id="relatorio_final" name="relatorio_final" required>
                                                            </div>
                                                            <input type="hidden" name="user_id" value="<?php echo h($user_id); ?>">
                                                            <input type="hidden" name="cntr_id" value="<?php echo h($contratoAtivo['cntr_id']); ?>">
                                                            <input type="hidden" name="rfin_id" value="<?php echo h($relatorioFinal['rfin_id']); ?>">
                                                            <button type="submit" class="btn btn-primary">Enviar</button>
                                                        </form>

                                                    <?php elseif ($controleRelatorioFinal == 2): ?>
                                                        <p>Relatório final enviado e assinado, mas ainda não validado. Por favor, aguarde a validação do relatório final.</p>
                                                        <a href="<?php echo BASE_URL . h($relatorioFinal['rfin_assinatura']); ?>" target="_blank" class="btn btn-secondary mb-2">Baixar PDF</a>
                                                        <button class="btn btn-danger mb-2" data-bs-toggle="modal" data-bs-target="#modalCancelarEnvioRelatorioFinal">Cancelar Envio</button>

                                                        <div class="modal fade" id="modalCancelarEnvioRelatorioFinal" ...>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                                                                <form action="<?php echo BASE_URL; ?>backend/relatorio-final/excluir-pdf.php" method="POST">
                                                                    <input type="hidden" name="rfin_id" value="<?php echo h($relatorioFinal['rfin_id']); ?>">
                                                                    <button type="submit" class="btn btn-danger">Cancelar Envio</button>
                                                                </form>
                                                            </div>
                                                            </div>

                                                    <?php elseif ($controleRelatorioFinal == 3): ?>
                                                        <p>Relatório final enviado, assinado e validado. Parabéns!</p>
                                                        <a href="<?php echo BASE_URL . h($relatorioFinal['rfin_assinatura']); ?>" target="_blank" class="btn btn-secondary mb-2">Baixar PDF</a>
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
    </section>

    <div class="modal fade" id="modalRelatorioInicial" ...>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRelatorioInicialLabel">Preencher Relatório Inicial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Preencha o formulário abaixo para enviar o relatório inicial.</p>
                    <form action="<?php echo BASE_URL;?>backend/relatorio-inicial/create.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="cntr_id" value="<?php echo h($contratoAtivo['cntr_id'] ?? ''); ?>">

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if ($relatorioInicial): ?> 
    <div class="modal fade" id="modalEditarRelatorioInicial" ...>
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
                            <textarea ... name="rini_como_ocorreu_edit" ...><?php echo h($relatorioInicial['rini_como_ocorreu']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="rini_dev_cronograma_edit" class="form-label">Comente sobre o desenvolvimento de seu cronograma de estágio:</label>
                            <textarea ... name="rini_dev_cronograma_edit" ...><?php echo h($relatorioInicial['rini_dev_cronograma']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="rini_preparacao_inicio_edit" class="form-label">Discorra sobre como foi sua preparação para o início do estágio:</label>
                            <textarea ... name="rini_preparacao_inicio_edit" ...><?php echo h($relatorioInicial['rini_preparacao_inicio']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="atividades-edit" class="form-label">Atividades desenvolvidas:</label>
                            <div id="atividades-container-edit">
                                <?php foreach ($atividadesRelatorioInicial as $key => $atividade): ?>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <textarea ... name="atividade<?php echo $key + 1; ?>_edit" ...><?php echo h($atividade['atvi_atividade']); ?></textarea> 
                                        </div>
                                        <div class="col-6">
                                            <textarea ... name="comentario<?php echo $key + 1; ?>_edit" ...><?php echo h($atividade['atvi_comentario']); ?></textarea>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-secondary" id="add-atividade-edit">Adicionar Atividade</button>
                        </div>
                        
                        <div class="mb-3">
                            <label for="rini_dificul_encontradas_edit" class="form-label">Discorra sobre as dificuldades encontradas...</label>
                            <textarea ... name="rini_dificul_encontradas_edit" ...><?php echo h($relatorioInicial['rini_dificul_encontradas']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="rini_aplic_conhecimento_edit" class="form-label">Discorra sobre as aplicações de conhecimentos...</label>
                            <textarea ... name="rini_aplic_conhecimento_edit" ...><?php echo h($relatorioInicial['rini_aplic_conhecimento']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="rini_novas_ferramentas_edit" class="form-label">Houve contato com novas ferramentas...</label>
                            <textarea ... name="rini_novas_ferramentas_edit" ...><?php echo h($relatorioInicial['rini_novas_ferramentas']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="rini_comentarios_edit" class="form-label">Outros comentários desejáveis:</label>
                            <textarea ... name="rini_comentarios_edit" ...><?php echo h($relatorioInicial['rini_comentarios']); ?></textarea>
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
    <?php endif; ?>

    <div class="modal fade" id="modalRelatorioFinal" ...>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRelatorioFinalLabel">Preencher Relatório Final</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Preencha o formulário abaixo para enviar o relatório final.</p>
                    <form action="<?php echo BASE_URL;?>backend/relatorio-final/create.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="cntr_id" value="<?php echo h($contratoAtivo['cntr_id'] ?? ''); ?>">
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if ($relatorioFinal): ?>
    <div class="modal fade" id="modalEditarRelatorioFinal" ...>
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
                            <textarea ... name="rfin_sintese_empresa_edit" ...><?php echo h($relatorioFinal['rfin_sintese_empresa']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="atividades-final-edit" class="form-label">Relacione e comente as atividades...</label>
                            <div id="atividades-container-final-edit">
                                <?php foreach ($atividadesRelatorioFinal as $key => $atividade): ?>
                                    <div class="row mb-2">
                                        <div class="col-4">
                                            <textarea ... name="atividade<?php echo $key + 1; ?>_final_edit" ...><?php echo h($atividade['atvf_atividade']); ?></textarea>
                                        </div>
                                        <div class="col-4">
                                            <textarea ... name="resumo<?php echo $key + 1; ?>_final_edit" ...><?php echo h($atividade['atvf_resumo']); ?></textarea>
                                        </div>
                                        <div class="col-4">
                                            <textarea ... name="disciplina<?php echo $key + 1; ?>_final_edit" ...><?php echo h($atividade['atvf_disciplina_relacionada']); ?></textarea>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-secondary" id="add-atividade-final-edit">Adicionar Atividade</button>
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
    <?php endif; ?>

    <?php require 'components/footer.php'; ?>
</body>
</html>