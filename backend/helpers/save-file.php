<?php
function uploadPDF($file, $destino_dir, $nome_base = 'arquivo') {
    if (!isset($file) || $file['error'] !== 0) {
        return ['success' => false, 'error' => 'Arquivo não enviado ou erro no upload.'];
    }

    $file_type = mime_content_type($file['tmp_name']);
    if ($file_type !== 'application/pdf') {
        return ['success' => false, 'error' => 'Apenas arquivos PDF são permitidos.'];
    }

    if (!is_dir($destino_dir)) {
        if (!mkdir($destino_dir, 0777, true)) {
            return ['success' => false, 'error' => 'Falha ao criar diretório de destino.'];
        }
    }

    $nome_arquivo = $nome_base . '_' . time() . '_' . rand(1000, 9999) . '.pdf';

    $destino = $destino_dir . $nome_arquivo;

    if (move_uploaded_file($file['tmp_name'], $destino)) {
        return [
            'success' => true,
            'file_path' => $destino, // Caminho absoluto (opcional de uso interno)
            'file_name' => $nome_arquivo, // Apenas o nome
            'error' => ''
        ];
    } else {
        return ['success' => false, 'error' => 'Falha ao mover o arquivo.'];
    }
}
?>
