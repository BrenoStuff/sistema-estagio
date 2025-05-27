<?php
// Configurações da Página
require 'config.php';
require 'backend/auth/verifica.php';
$title = SIS_NAME . ' - Area do Aluno';
$navActive = 'home';


//
// CÓDIGO DE CONEXÃO COM O BANCO DE DADOS
//

require_once 'backend/helpers/db-connect.php';

$user_id = $_SESSION['usuario'];

// Informações do usuário
$sql = "SELECT * FROM usuarios
        JOIN cursos ON user_id_curs = curs_id
        WHERE user_id = '$user_id'";

$usuarioInfo = $conexao->query($sql);
if ($usuarioInfo->num_rows > 0) {
    $usuario = $usuarioInfo->fetch_assoc();
} else {
    header("location:error.php?aviso=Usuário não encontrado!");
    exit();
}

//
// Informações dos contrato ativo do usuário
// 

$sql = "SELECT * FROM contratos
        JOIN empresas ON cntr_id_empresa = empr_id
        WHERE cntr_id_usuario = '$user_id' AND cntr_ativo = 1 LIMIT 1";
$contratoAtivoInfo = $conexao->query($sql);
if ($contratoAtivoInfo->num_rows > 0) {
    $contratoAtivo = $contratoAtivoInfo->fetch_assoc();
} else {
    $contratoAtivo = null; // Nenhum contrato ativo encontrado
}

// Informações de relatório incial do contrato ativo do usuário
$sql = "SELECT * FROM relatorio_inicial
        WHERE rini_id = '" . $contratoAtivo['cntr_id_relatorio_inicial'] . "'";
$relatorioInicialInfo = $conexao->query($sql);
if ($relatorioInicialInfo->num_rows > 0) {
    $relatorioInicial = $relatorioInicialInfo->fetch_assoc();
} else {
    $relatorioInicial = null; // Nenhum relatório inicial encontrado
}

// Atividades do relatório inicial
if ($relatorioInicial != null) {
    $sql = "SELECT * FROM atv_estagio_ini
            WHERE atvi_id_relatorio_ini = '" . $relatorioInicial['rini_id'] . "'";
    $atividadesRelatorioInicialInfo = $conexao->query($sql);
    if ($atividadesRelatorioInicialInfo->num_rows > 0) {
        $atividadesRelatorioInicial = $atividadesRelatorioInicialInfo->fetch_all(MYSQLI_ASSOC);
    } else {
        $atividadesRelatorioInicial = null; // Nenhuma atividade encontrada
    }
} else {
    $atividadesRelatorioInicial = null; // Nenhum relatório inicial encontrado
}

// Informações de relatório final do contrato ativo do usuário
$sql = "SELECT * FROM relatorio_final
        WHERE rfin_id = '" . $contratoAtivo['cntr_id_relatorio_final'] . "'";
$relatorioFinalInfo = $conexao->query($sql);
if ($relatorioFinalInfo->num_rows > 0) {
    $relatorioFinal = $relatorioFinalInfo->fetch_assoc();
} else {
    $relatorioFinal = null; // Nenhum relatório final encontrado
}

// Atividades do relatório final
if ($relatorioFinal != null) {
    $sql = "SELECT * FROM atv_estagio_fin
            WHERE atvf_id_relatorio_fin = '" . $relatorioFinal['rfin_id'] . "'";
    $atividadesRelatorioFinalInfo = $conexao->query($sql);
    if ($atividadesRelatorioFinalInfo->num_rows > 0) {
        $atividadesRelatorioFinal = $atividadesRelatorioFinalInfo->fetch_all(MYSQLI_ASSOC);
    } else {
        $atividadesRelatorioFinal = null; // Nenhuma atividade encontrada
    }
} else {
    $atividadesRelatorioFinal = null; // Nenhum relatório final encontrado
}

?>

<?php
// Head
require 'components/head.php';
?>

<body class="bg-light">
    <!-- Navbar -->
    <?php require 'components/navbar.php'; ?>

    <!-- Seção de informações do aluno -->
    <section class="container-fluid mt-4">
        <div class="p-5 mx-2 bg-white rounded-4">
            <div class="row">
                <div class="col-md-4 d-flex justify-content-center align-items-center">
                    <!-- Icone fontawesome grande -->
                    <i class="fa-solid fa-circle-user fa-10x"></i>
                </div>
                <div class="col-md-8">
                    <p class="fs-1">Bem vindo, <?php echo $usuario['user_nome']; ?>!</p>
                    <p class="fs-5"><strong>Curso:</strong> <?php echo $usuario['curs_nome']; ?></p>
                    <p class="fs-5"><strong>RA:</strong> <?php echo $usuario['user_ra']; ?></p>
                    <p class="fs-5"><strong>E-mail:</strong> <?php echo $usuario['user_login']; ?></p>
                    <p class="fs-5"><strong>Contato:</strong> <?php echo $usuario['user_contato']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Secão de contratos -->
    <section class="container-fluid mt-4">
        <div class="p-5 mx-2 bg-white rounded-4">
            <h1 class="text-center mb-4">Contrato Ativo</h1>
            <?php if ($contratoAtivo == null): ?>
                <div class="alert alert-warning" role="alert">
                    Você não possui nenhum contrato ativo. Por favor, entre em contato com a coordenação do curso se isso é um erro.
                </div>
            <?php else: ?>
                
                <?php
                // Cálculo do percentual de dias restantes
                    $dataInicio = new DateTime($contratoAtivo['cntr_data_inicio']);
                    $dataFim = new DateTime($contratoAtivo['cntr_data_fim']);
                    $dataAtual = new DateTime();
                    $intervalo = $dataInicio->diff($dataFim);
                    $diasTotais = $intervalo->days;
                    $diasRestantes = $dataAtual->diff($dataFim)->days;
                    $percentual = ($diasTotais - $diasRestantes) / $diasTotais * 100;
                ?>

                <!-- Barra de progresso do contrato -->
                <div class="progress mb-4">
                    <div class="progress-bar" role="progressbar" style="width: <?php echo $percentual; ?>%;" aria-valuenow="<?php echo $percentual; ?>" aria-valuemin="0" aria-valuemax="100">
                        <?php echo round($percentual, 2); ?>%
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 d-flex justify-content-center align-items-center">
                        <!-- Icone de uma suitcase -->
                        <i class="fa-solid fa-suitcase fa-10x"></i>
                    </div>
                    <div class="col-md-8">
                        <div class="row">
                            <!-- Informações da emrpesa -->
                            <div class="col-md-6">
                                <p class="fs-5"><strong>Empresa:</strong> <?php echo $contratoAtivo['empr_nome']; ?></p>
                                <p class="fs-5"><strong>Endereço:</strong> <?php echo $contratoAtivo['empr_endereco']; ?></p>
                                <p class="fs-5"><strong>Contato:</strong> <?php echo $contratoAtivo['empr_contato_1']; ?></p>
                                <?php if (!empty($contratoAtivo['empr_contato_2'])): ?>
                                    <p class="fs-5"><strong>Contato:</strong> <?php echo $contratoAtivo['empr_contato_2']; ?></p>
                                <?php endif; ?>
                            </div>
                            <!-- Informações do contrato -->
                            <div class="col-md-6">
                                <p class="fs-5"><strong>Data de Início:</strong> <?php echo date('d/m/Y', strtotime($contratoAtivo['cntr_data_inicio'])); ?></p>
                                <p class="fs-5"><strong>Data de Término:</strong> <?php echo date('d/m/Y', strtotime($contratoAtivo['cntr_data_fim'])); ?></p>
                                <p class="fs-5"><strong>Contrato: </strong><a href="<?php echo $contratoAtivo['cntr_termo_contrato']; ?>" target="_blank"> Ver Contrato</a></p>
                            </div>
                        </div>
                        
                        <!-- Informações dos relatórios -->
                        <div class="row">
                            <div class="col-md-6">

                                <!-- accordition do relatório inicial -->
                                <div class="accordion" id="accordionRelatorioInicial">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingRelatorioInicial">

                                            <?php $controleRelatorioInicial = 0; // Criação de variável para controle de status do relatório inicial ?>

                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRelatorioInicial" aria-expanded="true" aria-controls="collapseRelatorioInicial">

                                                Relatório Inicial &nbsp;
                                                <?php if (isset($contratoAtivo['cntr_id_relatorio_inicial'])): ?>
                                                    <?php if ($relatorioInicial['rini_assinatura'] == ''): ?>
                                                        <span class="badge bg-warning text-dark">Aguardando Assinatura</span>
                                                        <?php $controleRelatorioInicial = 1; // Setar controle para 1 - Aguardando Assinatura ?>
                                                    <?php elseif ($relatorioInicial['rini_assinatura'] != '' && $relatorioInicial['rini_aprovado'] == 0): ?>
                                                        <span class="badge bg-warning text-dark">Aguardando Validação</span>
                                                        <?php $controleRelatorioInicial = 2; // Setar controle para 2 - Aguardando Validação ?>
                                                    <?php elseif ($relatorioInicial['rini_assinatura'] != '' && $relatorioInicial['rini_aprovado'] == 1): ?>
                                                        <span class="badge bg-success">Aprovado</span>
                                                        <?php $controleRelatorioInicial = 3; // Setar controle para 3 - Aprovado ?>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Não enviado</span>
                                                    <?php $controleRelatorioInicial = 0; // Setar controle para 0 - Não enviado ?>
                                                <?php endif; ?>

                                            </button>
                                        </h2>
                                        <div id="collapseRelatorioInicial" class="accordion-collapse collapse" aria-labelledby="headingRelatorioInicial" data-bs-parent="#accordionRelatorioInicial">
                                            <div class="accordion-body">

                                                <!-- Status do relatório inicial -->
                                                <?php if ($controleRelatorioInicial == 0): ?>
                                                    <p>Relatório inicial não enviado.</p>

                                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRelatorioInicial">Preencher Relatório Inicial</button>
                                                <?php elseif ($controleRelatorioInicial == 1): ?>
                                                    <p>Relatório inicial enviado, mas ainda não assinado. Por favor, faça o download do relatório inicial, assine e envie em PDF.</p>

                                                    <a href="<?php echo BASE_URL; ?>backend/relatorio-inicial/imprimir-pdf.php?cntr_id=<?php echo $contratoAtivo['cntr_id']; ?>" class="btn btn-secondary mb-2">Baixar PDF</a>

                                                    <button class="btn btn-secondary mb-2" data-bs-toggle="modal" data-bs-target="#modalEditarRelatorioInicial">Editar relatório</button>

                                                    <button class="btn btn-danger mb-2" data-bs-toggle="modal" data-bs-target="#modalRefazerRelatorioInicial">Cancelar relatório</button>

                                                    <!-- Modal certeza que deseja refazer o relatório inicial -->
                                                    <div class="modal fade" id="modalRefazerRelatorioInicial" tabindex="-1" aria-labelledby="modalRefazerRelatorioInicialLabel" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="modalRefazerRelatorioInicialLabel">Cancelar Relatório Inicial</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Você tem certeza que deseja cancelar o relatório inicial? Isso irá apagar o relatório atual e você precisará preencher um novo relatório inicial.
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                                                                    <form action="<?php echo BASE_URL;?>backend/relatorio-inicial/delete.php" method="POST">
                                                                        <input type="hidden" name="cntr_id" value="<?php echo $contratoAtivo['cntr_id']; ?>">
                                                                        <input type="hidden" name="rini_id" value="<?php echo $relatorioInicial['rini_id']; ?>">
                                                                        <button type="submit" class="btn btn-danger">Cancelar relatório</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <form action="<?php echo BASE_URL;?>backend/relatorio-inicial/enviar-pdf.php" method="POST" enctype="multipart/form-data">
                                                        <div class="mb-3">
                                                            <label for="relatorio_inicial" class="form-label">Anexe e envie o relatório assinado.</label>
                                                            <input type="file" class="form-control" id="relatorio_inicial" name="relatorio_inicial" required>
                                                        </div>
                                                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                                        <input type="hidden" name="cntr_id" value="<?php echo $contratoAtivo['cntr_id']; ?>">
                                                        <input type="hidden" name="rini_id" value="<?php echo $relatorioInicial['rini_id']; ?>">
                                                        <button type="submit" class="btn btn-primary">Enviar</button>
                                                    </form>
                                                <?php elseif ($controleRelatorioInicial == 2): ?>
                                                    <p>Relatório inicial enviado e assinado, mas ainda não validado. Por favor, aguarde a validação do relatório inicial.</p>
                                                    <a href="<?php echo BASE_URL . $relatorioInicial['rini_assinatura']; ?>" target="_blank" class="btn btn-secondary mb-2">Baixar PDF</a>
                                                    <button class="btn btn-danger mb-2" data-bs-toggle="modal" data-bs-target="#modalCancelarEnvioRelatorioInicial">Cancelar Envio</button>

                                                    <!-- Modal certeza que deseja cancelar o envio do relatório inicial -->
                                                    <div class="modal fade" id="modalCancelarEnvioRelatorioInicial" tabindex="-1" aria-labelledby="modalCancelarEnvioRelatorioInicialLabel" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="modalCancelarEnvioRelatorioInicialLabel">Cancelar Envio do Relatório Inicial</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Você tem certeza que deseja cancelar o envio do relatório inicial? Isso irá apagar o PDF atual e você precisará enviar um novo relatório inicial assinado.
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                                                                    <form action="<?php echo BASE_URL; ?>backend/relatorio-inicial/excluir-pdf.php" method="POST">
                                                                        <input type="hidden" name="rini_id" value="<?php echo $relatorioInicial['rini_id']; ?>">
                                                                        <button type="submit" class="btn btn-danger">Cancelar Envio</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                <?php elseif ($controleRelatorioInicial == 3): ?>
                                                    <p>Relatório inicial enviado, assinado e validado. Parabéns!</p>
                                                    <a href="<?php echo BASE_URL . $relatorioInicial['rini_assinatura']; ?>" target="_blank" class="btn btn-secondary mb-2">Baixar PDF</a>
                                                <?php endif; ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- accordition do relatório final -->
                                <div class="accordion" id="accordionRelatorioFinal">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingRelatorioFinal">

                                            <?php $controleRelatorioFinal = 0; // Criação de variável para controle de status do relatório final ?>

                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRelatorioFinal" aria-expanded="false" aria-controls="collapseRelatorioFinal">
                                                Relatório Final &nbsp;

                                                <?php if (isset($contratoAtivo['cntr_id_relatorio_final'])): ?>
                                                    <?php if ($relatorioFinal['rfin_assinatura'] == ''): ?>
                                                        <span class="badge bg-warning text-dark">Aguardando Assinatura</span>
                                                        <?php $controleRelatorioFinal = 1; // Setar controle para 1 - Aguardando Assinatura ?>
                                                    <?php elseif ($relatorioFinal['rfin_assinatura'] != '' && $relatorioFinal['rfin_status'] == 0): ?>
                                                        <span class="badge bg-warning text-dark">Aguardando Validação</span>
                                                        <?php $controleRelatorioFinal = 2; // Setar controle para 2 - Aguardando Validação ?>
                                                    <?php elseif ($relatorioFinal['rfin_assinatura'] != '' && $relatorioFinal['rfin_status'] == 1): ?>
                                                        <span class="badge bg-success">Aprovado</span>
                                                        <?php $controleRelatorioFinal = 3; // Setar controle para 3 - Aprovado ?>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Não enviado</span>
                                                    <?php $controleRelatorioFinal = 0; // Setar controle para 0 - Não enviado ?>
                                                <?php endif; ?>
                                            </button>
                                        </h2>
                                        <div id="collapseRelatorioFinal" class="accordion-collapse collapse" aria-labelledby="headingRelatorioFinal" data-bs-parent="#accordionRelatorioFinal">
                                            <div class="accordion-body">

                                                <?php if ($controleRelatorioInicial != 3): // Checar se o relatório inicial foi enviado para exibir o código do relatório final ?>
                                                    <p>Relatório final não pode ser enviado antes do relatório inicial ser aprovado.</p>
                                                <?php else : ?>

                                                <!-- Status do relatório final -->
                                                <?php if ($controleRelatorioFinal == 0): ?>
                                                    <p>Relatório final não enviado.</p>
                                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRelatorioFinal">Preencher Relatório Final</button>
                                                <?php elseif ($controleRelatorioFinal == 1): ?>
                                                    <p>Relatório final enviado, mas ainda não assinado. Por favor, faça o download do relatório final, assine e envie em PDF.</p>

                                                    <a href="backend/relatorio-final/imprimir-pdf.php?cntr_id=<?php echo $contratoAtivo['cntr_id']; ?>" class="btn btn-secondary mb-2">Baixar PDF</a>
                                                    <a href="backend/relatorio-final/delete.php?cntr_id=<?php echo $contratoAtivo['cntr_id']; ?>" class="btn btn-danger mb-2">Refazer Relatório Final</a>

                                                    <form action="<?php echo BASE_URL;?>backend/relatorio-final/enviar-pdf.php" method="POST" enctype="multipart/form-data">
                                                        <div class="mb-3">
                                                            <label for="relatorio_final" class="form-label">Anexe e envie o relatório assinado.</label>
                                                            <input type="file" class="form-control" id="relatorio_final" name="relatorio_final" required>
                                                            <button type="submit" class="btn btn-primary mt-2">Enviar</button>
                                                        </div>
                                                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                                        <input type="hidden" name="cntr_id" value="<?php echo $contratoAtivo['cntr_id']; ?>">
                                                    </form>
                                                <?php elseif ($controleRelatorioFinal == 2): ?>
                                                    <p>Relatório final enviado e assinado, mas ainda não validado. Por favor, aguarde a validação do relatório final.</p>
                                                    <a href="backend/relatorio-final/imprimir-pdf.php?cntr_id=<?php echo $contratoAtivo['cntr_id']; ?>" class="btn btn-secondary mb-2">Baixar PDF</a>
                                                    <a href="backend/relatorio-final/delete.php?cntr_id=<?php echo $contratoAtivo['cntr_id']; ?>" class="btn btn-danger mb-2">Cancelar envio</a>
                                                <?php elseif ($controleRelatorioFinal == 3): ?>
                                                    <p>Relatório final enviado, assinado e validado. Parabéns!</p>
                                                    <a href="backend/relatorio-final/imprimir-pdf.php?cntr_id=<?php echo $contratoAtivo['cntr_id']; ?>" class="btn btn-secondary mb-2">Baixar PDF</a>
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

    <!-- Modal de preenchimento do relatório inicial -->
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
                            <button type="submit" class="btn btn-primary">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de edição do relatório inicial -->
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
                        <!-- Campo texto 1023 caracteres: Discorra sobre a forma como ocorreu a sua contratação: -->
                        <div class="mb-3">
                            <label for="rini_como_ocorreu_edit" class="form-label">Discorra sobre a forma como ocorreu a sua contratação:</label>
                            <textarea class="form-control" id="rini_como_ocorreu_edit" name="rini_como_ocorreu_edit" rows="3" maxlength="1023" required><?php echo $relatorioInicial['rini_como_ocorreu']; ?></textarea>
                        </div>

                        <!-- Campo texto 1023 caracteres: Comente sobre o desenvolvimento de seu cronograma de estágio -->
                        <div class="mb-3">
                            <label for="rini_dev_cronograma_edit" class="form-label">Comente sobre o desenvolvimento de seu cronograma de estágio:</label>
                            <textarea class="form-control" id="rini_dev_cronograma_edit" name="rini_dev_cronograma_edit" rows="3" maxlength="1023" required><?php echo $relatorioInicial['rini_dev_cronograma']; ?></textarea>
                        </div>

                        <!-- Campo texto 1023 caracteres: Discorra sobre como foi sua preparação para o início do estágio -->
                        <div class="mb-3">
                            <label for="rini_preparacao_inicio_edit" class="form-label">Discorra sobre como foi sua preparação para o início do estágio:</label>
                            <textarea class="form-control" id="rini_preparacao_inicio_edit" name="rini_preparacao_inicio_edit" rows="3" maxlength="1023" required><?php echo $relatorioInicial['rini_preparacao_inicio']; ?></textarea>
                        </div>

                        <!-- Campo de atividades, onde a atividade é numerada e tem um texto de comentário do lado, elá terá um botão que irá adicionar atividades a partir que aperta ele -->
                        <div class="mb-3">
                            <label for="atividades-edit" class="form-label">Atividades desenvolvidas:</label>
                            <div id="atividades-container-edit">
                                <?php foreach ($atividadesRelatorioInicial as $key => $atividade): ?>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <textarea class="form-control" name="atividade<?php echo $key + 1; ?>_edit" placeholder="Atividade <?php echo $key + 1; ?>" rows="3" maxlength="1023" <?php if ($key == 0) echo 'required'; ?>><?php echo $atividade['atvi_atividade']; ?></textarea> 
                                        </div>
                                        <div class="col-6">
                                            <textarea class="form-control" name="comentario<?php echo $key + 1; ?>_edit" placeholder="Comentário" rows="3" maxlength="1023" <?php if ($key == 0) echo 'required'; ?>><?php echo $atividade['atvi_comentario']; ?></textarea>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-secondary" id="add-atividade-edit">Adicionar Atividade</button>
                        </div>
                        <!-- Campo texto 1023 caracteres: Discorra sobre as dificuldades encontradas no desenvolvimento e como foram solucionadas -->
                        <div class="mb-3">
                            <label for="rini_dificul_encontradas_edit" class="form-label">Discorra sobre as dificuldades encontradas no desenvolvimento e como foram solucionadas:</label>
                            <textarea class="form-control" id="rini_dificul_encontradas_edit" name="rini_dificul_encontradas_edit" rows="3" maxlength="1023" required><?php echo $relatorioInicial['rini_dificul_encontradas']; ?></textarea>
                        </div>
                        <!-- Campo texto 1023 caracteres: Discorra sobre as aplicações de conhecimentos desenvolvidos pelas disciplinas do curso, relacionando a atividade na qual ocorreu, as disciplinas envolvidas com elas e as contribuições que cada disciplina propiciou: -->
                        <div class="mb-3">
                            <label for="rini_aplic_conhecimento_edit" class="form-label">Discorra sobre as aplicações de conhecimentos desenvolvidos pelas disciplinas do curso, relacionando a atividade na qual ocorreu, as disciplinas envolvidas com elas e as contribuições que cada disciplina propiciou:</label>
                            <textarea class="form-control" id="rini_aplic_conhecimento_edit" name="rini_aplic_conhecimento_edit" rows="3" maxlength="1023" required><?php echo $relatorioInicial['rini_aplic_conhecimento']; ?></textarea>
                        </div>
                        <!-- Campo texto 1023 caracteres: Houve contato com novas ferramentas, técnicas e/ou métodos, diferentes dos aprendidos durante o curso? Em caso positivo, cite-os e comente-os: -->
                        <div class="mb-3">
                            <label for="rini_novas_ferramentas_edit" class="form-label">Houve contato com novas ferramentas, técnicas e/ou métodos, diferentes dos aprendidos durante o curso? Em caso positivo, cite-os e comente-os:</label>
                            <textarea class="form-control" id="rini_novas_ferramentas_edit" name="rini_novas_ferramentas_edit" rows="3" maxlength="1023" required><?php echo $relatorioInicial['rini_novas_ferramentas']; ?></textarea>
                        </div>
                        <!-- Campo texto 1023 caracteres: Outros comentários desejáveis: -->
                        <div class="mb-3">
                            <label for="rini_comentarios_edit" class="form-label">Outros comentários desejáveis:</label>
                            <textarea class="form-control" id="rini_comentarios_edit" name="rini_comentarios_edit" rows="3" maxlength="1023"><?php echo $relatorioInicial['rini_comentarios']; ?></textarea>
                        </div>
                        <!-- campo anexo de arquivo 1 e arquivo 2: Se desejável, anexe outros documentos relativos às atividades de estágio ou críticas e sugestões sobre este formulário. -->
                        <p class="form-text">Se desejável, anexe outros documentos relativos às atividades de estágio ou críticas e sugestões sobre este formulário.</p>
                        <div class="mb-3">
                            <label for="rini_anexo_1_edit" class="form-label">Anexo 1</label>
                            <input type="file" class="form-control" id="rini_anexo_1_edit" name="rini_anexo_1_edit">
                        </div>
                        <div class="mb-3">
                            <label for="rini_anexo_2_edit" class="form-label">Anexo 2</label>
                            <input type="file" class="form-control" id="rini_anexo_2_edit" name="rini_anexo_2_edit">
                        </div>
                        <input type="hidden" name="rini_id_edit" id="rini_id_edit" value="<?php echo $relatorioInicial['rini_id']; ?>">
                        <input type="hidden" name="cntr_id_edit" value="<?php echo $contratoAtivo['cntr_id']; ?>">
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Atualizar</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal de preenchimento do relatório final -->
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

                        <input type="hidden" name="cntr_id" value="<?php echo $contratoAtivo['cntr_id']; ?>">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de edição do relatório final -->
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
                        <!-- Campo texto 1023 caracteres: Apresentar, em forma de texto (não em tópicos), uma síntese sobre a empresa onde foi realizado o estágio; nesta síntese devem estar contidos: - Histórico da empresa; - Perfil da empresa; - Descrição do setor onde o estágio foi realizado (apresentar as principais atividades do setor). -->
                        <div class="mb-3">
                            <label for="rfin_sintese_empresa_edit" class="form-label">Apresentar, em forma de texto (não em tópicos), uma síntese sobre a empresa onde foi realizado o estágio; nesta síntese devem estar contidos: <br>
                                - Histórico da empresa; <br>
                                - Perfil da empresa; <br>
                                - Descrição do setor onde o estágio foi realizado (apresentar as principais atividades do setor).</label>
                            <textarea class="form-control" id="rfin_sintese_empresa_edit" name="rfin_sintese_empresa_edit" rows="9" maxlength="1023" required><?php echo $relatorioFinal['rfin_sintese_empresa']; ?></textarea>
                        </div>

                        <!-- Campo de atividades, onde a atividade é numerada, tem um texto de resumo e disciplina relacionada a essa atividade, elá terá um botão que irá adicionar atividades a partir que aperta ele, ficando uma linha com atividade / resumo / disciplina -->
                        <div class="mb-3">
                            <label for="atividades-final-edit" class="form-label">Relacione e comente as atividades desenvolvidas no período total de estágio:</label>
                            <div id="atividades-container-final-edit">
                                <?php foreach ($atividadesRelatorioFinal as $key => $atividade): ?>
                                    <div class="row mb-2">
                                        <div class="col-4">
                                            <textarea class="form-control" name="atividade<?php echo $key + 1; ?>_final_edit" placeholder="Atividade <?php echo $key + 1; ?>" rows="3" maxlength="1023" <?php if ($key == 0) echo 'required'; ?>><?php echo $atividade['atvf_atividade']; ?></textarea>
                                        </div>
                                        <div class="col-4">
                                            <textarea class="form-control" name="resumo<?php echo $key + 1; ?>_final_edit" placeholder="Resumo da Atividade <?php echo $key + 1; ?>" rows="3" maxlength="1023" <?php if ($key == 0) echo 'required'; ?>><?php echo $atividade['atvf_resumo']; ?></textarea>
                                        </div>
                                        <div class="col-4">
                                            <textarea class="form-control" name="disciplina<?php echo $key + 1; ?>_final_edit" placeholder="Disciplina Relacionada <?php echo $key + 1; ?>" rows="3" maxlength="1023" <?php if ($key == 0) echo 'required'; ?>><?php echo $atividade['atvf_disciplina_relacionada']; ?></textarea>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-secondary" id="add-atividade-final-edit">Adicionar Atividade</button>
                        </div>
                        <input type="hidden" name="rfin_id_edit" id="rfin_id_edit" value="<?php echo $relatorioFinal['rfin_id']; ?>">
                        <input type="hidden" name="cntr_id_edit" value="<?php echo $contratoAtivo['cntr_id']; ?>">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Atualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require 'components/footer.php'; ?>
</body>
</html>