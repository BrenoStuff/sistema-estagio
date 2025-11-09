<?php
include_once '../../config.php';
include_once '../auth/verifica.php';
include_once '../helpers/db-connect.php';

// 1. Filtragem da entrada (Segurança)
$contrato_id = filter_input(INPUT_GET, 'cntr_id', FILTER_VALIDATE_INT);

if (!$contrato_id) {
    $aviso = "ID de contrato inválido.";
    header("Location: ../../error.php?aviso=$aviso");
    exit();
}

// Helper de segurança (definido aqui caso não esteja no format.php)
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars(trim($str ?? ''), ENT_QUOTES, 'UTF-8'); // Adicionado trim()
    }
}

try {
    // 2. Query Principal (Relatório FINAL)
    $sql = "SELECT * FROM contratos
            JOIN empresas ON cntr_id_empresa = empr_id
            JOIN usuarios ON cntr_id_usuario = user_id
            JOIN cursos ON user_id_curs = curs_id
            JOIN relatorio_final ON rfin_id = cntr_id_relatorio_final
            WHERE cntr_id = ?"; // Placeholder

    $stmt = $conexao->prepare($sql);
    $stmt->execute([$contrato_id]);
    
    $relatorio = $stmt->fetch(); 

    // 3. Verifica se o relatório foi encontrado
    if (!$relatorio) {
        $aviso = "Relatório Final não encontrado!";
        header("Location: ../../error.php?aviso=$aviso");
        exit();
    }

    // 4. Query Secundária (Atividades FINAIS)
    $sql_atv = "SELECT * FROM atv_estagio_fin WHERE atvf_id_relatorio_fin = ?";
    
    $stmt_atv = $conexao->prepare($sql_atv);
    $stmt_atv->execute([$relatorio['rfin_id']]); // Usa o ID seguro da query anterior

    $atividades = $stmt_atv->fetchAll(); // Substitui fetch_all(MYSQLI_ASSOC)

} catch (PDOException $e) {
    // Tratamento de erro seguro
    error_log("Erro PDO ao imprimir relatório final: " . $e->getMessage());
    $aviso = "Erro interno ao buscar dados do relatório. Tente novamente mais tarde.";
    header("Location: ../../error.php?aviso=" . urlencode($aviso));
    exit();
}

// 5. Prepara dados para o HTML (agora usando h() com trim())
$aluno = h($relatorio['user_nome']);
$aluno_ra = h($relatorio['user_ra']);
$empresa = h($relatorio['empr_nome']);
$empresa_cidade = h($relatorio['empr_cidade']);
$curso = h($relatorio['curs_nome']);
$data_inicio = date('d/m/Y', strtotime(h($relatorio['cntr_data_inicio'])));
$data_fim = date('d/m/Y', strtotime(h($relatorio['cntr_data_fim']))); // Relatório Final usa a data fim real
$horario = h($relatorio['cntr_escala_horario']);

// Define o título da página (para a aba do navegador)
$title = "Relatório Final - " . $aluno;
require_once '../../components/head.php';
?>

<!-- Estilos para a página de impressão (Layout PDF) -->
<style>
    body {
        background-color: #E0E0E0; /* Fundo cinza para contrastar com a "folha" */
        font-family: Arial, sans-serif;
        line-height: 1.5;
        color: #000;
    }
    
    h1, h2, h3, h4, h5, h6, .btn {
        font-family: 'Inter', Arial, sans-serif;
    }

    /* Regras para TELA (visualização no navegador) */
    .page {
        width: 21cm; /* Largura A4 */
        min-height: 29.7cm; /* Altura A4 (PARA TELA) */
        padding: 1.5cm;
        margin: 1cm auto;
        background: white;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border: 1px solid #DADADA;

        /* **** Ativa o Flexbox para "grudar" o rodapé **** */
        display: flex;
        flex-direction: column;
    }

    /* **** Faz o <main> crescer e ocupar o espaço vago **** */
    main {
        flex-grow: 1;
    }

    .report-header {
        text-align: center;
        margin-bottom: 25px;
    }
    .logo-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    .logo-container img {
        height: 50px;
        filter: grayscale(100%);
    }
    .report-title {
        margin-top: 15px;
        font-weight: bold;
        font-size: 1.2rem;
    }

    .section {
        margin-bottom: 20px;
        page-break-inside: avoid; /* Tenta evitar quebras DENTRO de uma seção */
    }
    .section-title {
        font-weight: bold;
        margin-bottom: 8px;
        font-size: 1rem;
    }

    /* Caixa de resposta */
    .section-box {
        border: 1px solid #000;
        padding: 10px;
        min-height: 80px; /* Altura mínima para as caixas de texto */
        word-break: break-word;
    }
    .section-box p {
        text-align: justify;
        margin: 0;
        text-indent: 1.5em; /* Parágrafo */
    }
    .section-box p:first-child {
        text-indent: 0;
    }
    
    /* Tabela de identificação */
    .table-identificacao th {
        width: 80px; /* Largura para "Nome:" e "RA:" */
    }
    
    /* Tabela de Atividades */
    .table-atividades {
        font-size: 0.9rem;
    }
    /* **** ALTERAÇÃO: 3 colunas **** */
    .table-atividades th {
        width: 33.33%;
    }
    .table-atividades td {
        min-height: 40px;
        vertical-align: top;
        word-break: break-word;
    }
    .table-atividades tbody tr {
        page-break-inside: avoid; /* Evita quebrar linhas da tabela no meio */
    }
    
    /* Seção de Assinaturas */
    .signature-section {
        margin-top: 2cm;
        page-break-inside: avoid; /* Tenta evitar quebrar a seção de assinaturas */
    }
    .signature-box {
        border: 1px solid #000;
        padding: 10px;
        height: 120px;
        text-align: left;
    }
    .signature-box .label {
        font-size: 0.8rem;
    }
    
    /* Caixa de Parecer */
    .parecer-box {
        font-size: 0.9rem;
        border: 1px solid #000;
    }
    .parecer-box .check {
        display: inline-block;
        width: 15px;
        height: 15px;
        border: 1px solid #000;
        margin-right: 5px;
        vertical-align: middle;
    }

    .page-footer {
        text-align: center;
        margin-top: 1.5cm; /* Margem superior para separar do conteúdo */
        padding-top: 10px;
        border-top: 1px solid #AAA;
        font-size: 0.8rem;
        page-break-inside: avoid; /* Tenta evitar quebrar o rodapé */
    }

    .section-box .table {
        margin-bottom: 0;
    }


    /* **** REGRAS DE IMPRESSÃO **** */
    @media print {
        body {
            background-color: #FFF;
            margin: 0;
            padding: 0;
        }
        .no-print {
            display: none; /* Oculta botões */
        }

        /* Remove o layout de "folha" da tela */
        .page {
            width: 100%;
            min-height: auto; 
            margin: 0;
            box-shadow: none;
            border: none;
            padding: 0; /* Reset padding, os grupos de tabela abaixo irão cuidar disso */
            
            /* **** MÁGICA DA IMPRESSÃO **** */
            /* Faz o .page se comportar como uma tabela para repetir header/footer */
            display: table;
            border-collapse: collapse;
            flex-direction: unset; /* Desativa o flex na impressão */
        }

        main {
            /* O conteúdo principal */
            display: table-row-group; 
            
            /* Adiciona padding lateral ao conteúdo principal */
            padding: 0 1.5cm;
            flex-grow: unset; /* Desativa o flex na impressão */
        }
        
        .report-header {
            /* Repete no topo de cada página */
            display: table-header-group; 
            
            /* Recria o padding original da página A4 */
            padding: 1.5cm 1.5cm 0 1.5cm; 
        }

        .page-footer {
            /* Repete na base de cada página */
            display: table-footer-group; 
            
            /* Recria o padding original da página A4 */
            padding: 0 1.5cm 1.5cm 1.5cm; 
        }
        /* **** FIM DA MÁGICA **** */

        /* Garante quebras de página mais limpas */
        .section, .signature-section, .table-atividades tbody tr {
            page-break-inside: avoid;
        }
    }
</style>

<body>
    
    <!-- Botões de Ação (Não imprimíveis) -->
    <div class="container no-print text-center my-3">
        <a href="../../index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Imprimir / Salvar PDF
        </button>
    </div>

    <!-- Página / Documento -->
    <div class="page">
        <!-- Cabeçalho FATEC -->
        <header class="report-header">
            <div class="logo-container">
                <!-- Usando placeholders para os logos como no PDF -->
                <img src="https://placehold.co/200x60/FFF/000?text=Logo+Centro+Paula+Souza" alt="Logo Paula Souza">
                <img src="https://placehold.co/200x60/FFF/000?text=Logo+Governo+SP" alt="Logo Gov SP">
            </div>
            <h2 class="text-center mt-2 fw-bold">Fatec São Sebastião</h2>
            <!-- **** ALTERAÇÃO: Título **** -->
            <h1 class="report-title text-center">RELATÓRIO FINAL</h1>
        </header>

        <main>
            <!-- 1. Identificação -->
            <div class="section">
                <h3 class="section-title">Identificação do(a) aluno(a):</h3>
                <table class="table table-bordered table-identificacao">
                    <tbody>
                        <tr>
                            <th style="width: 80px;">Nome:</th>
                            <td><?php echo $aluno; ?></td>
                        </tr>
                        <tr>
                            <th>RA:</th>
                            <td><?php echo $aluno_ra; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 2. Período e Horas -->
            <div class="row">
                <div class="col-8">
                    <div class="section">
                        <h3 class="section-title">Período</h3>
                        <div class="border p-2">
                            <p class="small text-muted mb-0 fst-italic">Período total de realização do estágio</p>
                            <!-- **** ALTERAÇÃO: Data Fim **** -->
                            <p class="fw-bold mb-0">de <?php echo $data_inicio; ?> a <?php echo $data_fim; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                     <div class="section">
                        <h3 class="section-title">Horas estagiadas</h3>
                        <div class="border p-2" style="height: 85px;">
                            <!-- Espaço em branco para preenchimento manual de horas -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- **** INÍCIO DAS SEÇÕES DO RELATÓRIO FINAL **** -->

            <!-- 1. Síntese da Empresa -->
            <div class="section">
                <h3 class="section-title">1. Apresentar, em forma de texto, uma síntese sobre a empresa (histórico, perfil, setor):</h3>
                <div class="section-box">
                    <p><?php echo nl2br(h($relatorio['rfin_sintese_empresa'])); ?></p>
                </div>
            </div>

            <!-- 2. Atividades Desenvolvidas (3 colunas) -->
            <div class="section">
                <h3 class="section-title">2. Relacione e comente as atividades desenvolvidas no período total de estágio:</h3>
                <div class="section-box p-0 m-0">
                    <table class="table table-bordered table-atividades mb-0">
                        <thead class="table-light">
                            <tr> 
                                <th>Atividades:</th> 
                                <th>Resumo de cada atividade:</th> 
                                <th>Disciplina relacionada:</th> 
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (!empty($atividades)) {
                                foreach ($atividades as $key => $atividade) { 
                            ?>
                            <tr>
                                <td><strong><?php echo $key + 1; ?>.</strong> <?php echo nl2br(h($atividade['atvf_atividade'])); ?></td>
                                <td><?php echo nl2br(h($atividade['atvf_resumo'])); ?></td>
                                <td><?php echo nl2br(h($atividade['atvf_disciplina_relacionada'])); ?></td>
                            </tr>
                            <?php 
                                } // Fim do foreach
                            } else {
                            ?>
                            <!-- Caso não haja atividades -->
                            <tr>
                                <td colspan="3" class="text-muted fst-italic p-3">Nenhuma atividade registrada para este relatório.</td>
                            </tr>
                            <?php
                            } // Fim do if
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- 3. Anexos (Versão simplificada) -->
            <div class="section">
                <h3 class="section-title">3. Se desejável, anexe outros documentos relativos às atividades de estágio.</h3>
                <div class="section-box">
                    <?php if (empty($relatorio['rfin_anexo_1']) && empty($relatorio['rfin_anexo_2'])) { ?>
                        <p class="text-muted fst-italic">Nenhum anexo foi enviado.</p>
                    <?php } else { ?>
                        <?php if (!empty($relatorio['rfin_anexo_1'])) { ?>
                            <p class="mb-1"><strong>Anexo 1:</strong> <a href="<?php echo BASE_URL . h($relatorio['rfin_anexo_1']); ?>" target="_blank">Visualizar Anexo 1</a></p>
                        <?php } ?>
                        <?php if (!empty($relatorio['rfin_anexo_2'])) { ?>
                            <p class="mb-0"><strong>Anexo 2:</strong> <a href="<?php echo BASE_URL . h($relatorio['rfin_anexo_2']); ?>" target="_blank">Visualizar Anexo 2</a></p>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>

            <!-- Assinaturas -->
            <div class="signature-section row">
                <div class="col-4">
                    <div class="signature-box">
                        <span class="label">Estagiário:</span>
                    </div>
                    <small class="text-center d-block">Identificação e assinatura</small>
                </div>
                <div class="col-4">
                     <div class="signature-box">
                        <span class="label">Empresa:</span>
                    </div>
                    <small class="text-center d-block">Carimbos da empresa e do supervisor, com sua assinatura</small>
                </div>
                <div class="col-4">
                     <div class="parecer-box p-2">
                        <strong>PARECER:</strong>
                        <p class="mt-2 mb-1"><span class="check"></span> aprovado</p>
                        <p class="mb-1"><span class="check"></span> reprovado, motivo:</p>
                        <div style="height: 40px; border-bottom: 1px solid #CCC;"></div> <!-- Espaço -->
                        <p class="mb-0 small text-center mt-2">Coordenador de estágios:</p>
                        <p class="mb-0 small text-center">em ___/___/______</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Rodapé do Documento -->
        <footer class="page-footer text-muted">
            www.centropaulasouza.sp.gov.br
            <br>
            Rua Ítalo Nascimento, 366 Porto Grande 11600-000 São Sebastião SP Tel.: (12) 3892.3015 3892-5767
        </footer>
    </div>
    <!-- Fim da Página 1 / Documento Principal -->

    <!-- Scripts do Bootstrap (necessários para os botões) -->
    <script src="../../js/bootstrap.js"></script>
</body>
</html>