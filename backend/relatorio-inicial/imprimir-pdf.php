<?php
include_once '../../config.php';
// **** CORREÇÃO: Includes que estavam faltando ****
include_once '../auth/verifica.php';
include_once '../helpers/db-connect.php';
// **** FIM DA CORREÇÃO ****

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
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}

try {
    // 2. Query Principal (Relatório) com Prepared Statement
    $sql = "SELECT * FROM contratos
            JOIN empresas ON cntr_id_empresa = empr_id
            JOIN usuarios ON cntr_id_usuario = user_id
            JOIN cursos ON user_id_curs = curs_id
            JOIN relatorio_inicial ON rini_id = cntr_id_relatorio_inicial
            WHERE cntr_id = ?"; // Placeholder

    $stmt = $conexao->prepare($sql);
    $stmt->execute([$contrato_id]);
    
    $relatorio = $stmt->fetch(); 

    // 3. Verifica se o relatório foi encontrado
    if (!$relatorio) {
        $aviso = "Relatório não encontrado!";
        header("Location: ../../error.php?aviso=$aviso");
        exit();
    }

    // 4. Query Secundária (Atividades) com Prepared Statement
    $sql_atv = "SELECT * FROM atv_estagio_ini WHERE atvi_id_relatorio_ini = ?";
    
    $stmt_atv = $conexao->prepare($sql_atv);
    $stmt_atv->execute([$relatorio['rini_id']]); // Usa o ID seguro da query anterior

    $atividades = $stmt_atv->fetchAll(); // Substitui fetch_all(MYSQLI_ASSOC)

} catch (PDOException $e) {
    // Tratamento de erro seguro
    error_log("Erro PDO ao imprimir relatório inicial: " . $e->getMessage());
    $aviso = "Erro interno ao buscar dados do relatório. Tente novamente mais tarde.";
    header("Location: ../../error.php?aviso=" . urlencode($aviso));
    exit();
}

// 5. Prepara dados para o HTML (agora usando h())
$aluno = h($relatorio['user_nome']);
$aluno_ra = h($relatorio['user_ra']);
$empresa = h($relatorio['empr_nome']);
$empresa_cidade = h($relatorio['empr_cidade']);
$curso = h($relatorio['curs_nome']);
$data_inicio = date('d/m/Y', strtotime(h($relatorio['cntr_data_inicio'])));
// $data_fim = date('d/m/Y', strtotime(h($relatorio['cntr_data_fim']))); // Removido
$horario = h($relatorio['cntr_hora_inicio']) . " às " . h($relatorio['cntr_hora_final']);

// **** NOVO: Data atual para o período ****
$data_impressao = date('d/m/Y'); // Data atual

// Define o título da página (para a aba do navegador)
$title = "Relatório Inicial - " . $aluno;
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
    .table-atividades th {
        width: 40%;
    }
    .table-atividades td {
        min-height: 40px;
        vertical-align: top;
        white-space: pre-wrap; 
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
        margin-top: 1.5cm;
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
        }

        .report-header {
            /* Repete no topo de cada página */
            display: table-header-group; 
            
            /* Recria o padding original da página A4 */
            /* É necessário um div interno para padding funcionar com table-header-group */
            padding: 1.5cm 1.5cm 0 1.5cm; 
        }

        main {
            /* O conteúdo principal */
            display: table-row-group; 
            
            /* Adiciona padding lateral ao conteúdo principal */
            padding: 0 1.5cm;
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
            <h1 class="report-title text-center">RELATÓRIO INICIAL</h1>
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
                            <p class="small text-muted mb-0 fst-italic">não declare periodo coincidente aos dos próximos relatórios, tampouco horas acumuladas</p>
                            <!-- **** ALTERADO **** -->
                            <p class="fw-bold mb-0">de <?php echo $data_inicio; ?> a <?php echo $data_impressao; ?></p>
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

            <!-- 1. Contratação -->
            <div class="section">
                <h3 class="section-title">1. Discorra sobre a forma como ocorreu a sua contratação:</h3>
                <div class="section-box">
                    <p><?php echo nl2br(h($relatorio['rini_como_ocorreu'])); ?></p>
                </div>
            </div>
            
            <!-- 2. Cronograma -->
            <div class="section">
                <h3 class="section-title">2. Comente sobre o desenvolvimento de seu cronograma de estágio:</h3>
                <div class="section-box">
                    <p><?php echo nl2br(h($relatorio['rini_dev_cronograma'])); ?></p>
                </div>
            </div>
            
            <!-- 3. Preparação -->
            <div class="section">
                <h3 class="section-title">3. Discorra sobre como foi sua preparação para o início do estágio:</h3>
                <div class="section-box">
                    <p><?php echo nl2br(h($relatorio['rini_preparacao_inicio'])); ?></p>
                </div>
            </div>

            <!-- 4. Atividades Desenvolvidas -->
            <div class="section">
                <h3 class="section-title">4. Relacione e comente as atividades desenvolvidas neste primeiro período de estágio:</h3>
                <table class="table table-bordered table-atividades">
                    <thead class="table-light">
                        <tr> 
                            <th>Atividades:</th> 
                            <th>Comentários:</th> 
                        </tr>
                    </thead>
                    <!-- (Loop dinâmico - sem alterações) -->
                    <tbody>
                        <?php 
                        if (!empty($atividades)) {
                            foreach ($atividades as $key => $atividade) { 
                        ?>
                        <tr>
                            <td><strong><?php echo $key + 1; ?>.</strong> <?php echo nl2br(h($atividade['atvi_atividade'])); ?></td>
                            <td><?php echo nl2br(h($atividade['atvi_comentario'])); ?></td>
                        </tr>
                        <?php 
                            } // Fim do foreach
                        } else {
                        ?>
                        <!-- Caso não haja atividades -->
                        <tr>
                            <td colspan="2" class="text-muted fst-italic">Nenhuma atividade registrada para este relatório.</td>
                        </tr>
                        <?php
                        } // Fim do if
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- 5. Dificuldades -->
            <div class="section">
                <h3 class="section-title">5. Discorra sobre as dificuldades encontradas no desenvolvimento e como foram solucionadas:</h3>
                <div class="section-box">
                    <p><?php echo nl2br(h($relatorio['rini_dificul_encontradas'])); ?></p>
                </div>
            </div>
            
            <!-- 6. Aplicação Conhecimento -->
            <div class="section">
                <h3 class="section-title">6. Discorra sobre as aplicações de conhecimentos desenvolvidos pelas disciplinas do curso, relacionando a atividade na qual ocorreu, as disciplinas envolvidas com elas e as contribuições que cada disciplina propiciou:</h3>
                <div class="section-box">
                    <p><?php echo nl2br(h($relatorio['rini_aplic_conhecimento'])); ?></p>
                </div>
            </div>
            
            <!-- 7. Novas Ferramentas -->
            <div class="section">
                <h3 class="section-title">7. Houve contato com novas ferramentas, técnicas e/ou métodos, diferentes dos aprendidos durante o curso? Em caso positivo, cite-os e comente-os:</h3>
                <div class="section-box">
                    <p><?php echo nl2br(h($relatorio['rini_novas_ferramentas'])); ?></p>
                </div>
            </div>
            
            <!-- 8. Outros Comentários -->
            <div class="section">
                <h3 class="section-title">8. Outros comentários desejáveis:</h3>
                <div class="section-box">
                    <p><?php echo nl2br(h($relatorio['rini_comentarios'])); ?></p>
                </div>
            </div>
            
            <!-- 9. Anexos -->
            <div class="section">
                <h3 class="section-title">9. Se desejável, anexe outros documentos relativos às atividades de estágio ou críticas e sugestões sobre este formulário.</h3>
                <div class="section-box">
                    <?php if (empty($relatorio['rini_anexo_1']) && empty($relatorio['rini_anexo_2'])) { ?>
                        <p class="text-muted fst-italic">Nenhum anexo foi enviado.</p>
                    <?php } else { ?>
                        <?php if (!empty($relatorio['rini_anexo_1'])) { ?>
                            <p><strong>Anexo 1:</strong> <a href="<?php echo BASE_URL . h($relatorio['rini_anexo_1']); ?>" target="_blank">Anexo 1</a></p>
                        <?php } ?>
                        <?php if (!empty($relatorio['rini_anexo_2'])) { ?>
                            <p><strong>Anexo 2:</strong> <a href="<?php echo BASE_URL . h($relatorio['rini_anexo_2']); ?>" target="_blank">Anexo 2</a></p>
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

    <!-- Scripts do Bootstrap (necessários para os botões) -->
    <script src="../../js/bootstrap.js"></script>
</body>
</html>