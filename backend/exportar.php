<?php
session_start();
require 'helpers/db-connect.php';

// 1. Segurança: Apenas Admin
if (!isset($_SESSION['acesso']) || $_SESSION['acesso'] !== 'admin') {
    die("Acesso negado.");
}

// 2. Recebe o tipo de relatório desejado
$tipo = filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$tipo) {
    die("Tipo de relatório não especificado.");
}

// 3. Configurações do Arquivo para Download (Excel/CSV)
$data_hoje = date('d-m-Y');
$filename = "relatorio_{$tipo}_{$data_hoje}.csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Cria o ponteiro de saída
$output = fopen('php://output', 'w');

// IMPORTANTE: Adiciona o BOM (Byte Order Mark) para o Excel reconhecer acentos (UTF-8)
fputs($output, "\xEF\xBB\xBF");

// 4. Lógica de cada relatório
switch ($tipo) {
    
    // --- RELATÓRIO 1: TODOS OS CONTRATOS ---
    case 'contratos_geral':
        // Cabeçalhos das colunas
        fputcsv($output, ['ID', 'Aluno', 'RA', 'Curso', 'Empresa', 'Data Inicio', 'Data Fim', 'Status', 'Remunerado'], ';');
        
        // Busca os dados
        $sql = "SELECT c.cntr_id, u.user_nome, u.user_ra, cur.curs_nome, e.empr_nome, 
                       c.cntr_data_inicio, c.cntr_data_fim, c.cntr_ativo, c.cntr_tipo_estagio
                FROM contratos c
                JOIN usuarios u ON c.cntr_id_usuario = u.user_id
                LEFT JOIN cursos cur ON u.user_id_curs = cur.curs_id
                JOIN empresas e ON c.cntr_id_empresa = e.empr_id
                ORDER BY c.cntr_id DESC";
        
        $stmt = $conexao->query($sql);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $status = $row['cntr_ativo'] == 1 ? 'Ativo' : 'Finalizado';
            $remunerado = $row['cntr_tipo_estagio'] == 1 ? 'Sim' : 'Não';
            $d_ini = date('d/m/Y', strtotime($row['cntr_data_inicio']));
            $d_fim = date('d/m/Y', strtotime($row['cntr_data_fim']));
            
            fputcsv($output, [
                $row['cntr_id'],
                $row['user_nome'],
                $row['user_ra'],
                $row['curs_nome'],
                $row['empr_nome'],
                $d_ini,
                $d_fim,
                $status,
                $remunerado
            ], ';'); // Ponto e vírgula é o padrão do Excel no Brasil
        }
        break;

    // --- RELATÓRIO 2: ALUNOS COM ESTÁGIO ATIVO ---
    case 'alunos_ativos':
        fputcsv($output, ['Aluno', 'RA', 'Email/Login', 'Telefone', 'Empresa Atual', 'Fim do Contrato'], ';');

        $sql = "SELECT u.user_nome, u.user_ra, u.user_login, u.user_contato, e.empr_nome, c.cntr_data_fim
                FROM contratos c
                JOIN usuarios u ON c.cntr_id_usuario = u.user_id
                JOIN empresas e ON c.cntr_id_empresa = e.empr_id
                WHERE c.cntr_ativo = 1
                ORDER BY u.user_nome ASC";
        
        $stmt = $conexao->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $row['user_nome'],
                $row['user_ra'],
                $row['user_login'],
                $row['user_contato'],
                $row['empr_nome'],
                date('d/m/Y', strtotime($row['cntr_data_fim']))
            ], ';');
        }
        break;

    // --- RELATÓRIO 3: EMPRESAS PARCEIRAS ---
    case 'empresas':
        fputcsv($output, ['ID', 'Razao Social', 'CNPJ', 'Tipo', 'Cidade', 'Contato', 'Endereco'], ';');

        $sql = "SELECT * FROM empresas ORDER BY empr_nome ASC";
        $stmt = $conexao->query($sql);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $row['empr_id'],
                $row['empr_nome'],
                $row['empr_cnpj'],
                $row['empr_tipo'],
                $row['empr_cidade'],
                $row['empr_contato_1'],
                $row['empr_endereco']
            ], ';');
        }
        break;

    default:
        echo "Tipo de relatório inválido.";
        break;
}

fclose($output);
exit();
?>