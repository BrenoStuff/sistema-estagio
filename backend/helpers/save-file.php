<?php

function saveFile($file, $directory, $file_name) {
    $targetDir = "../uploads/$directory/";
    $targetFile = $targetDir . basename($file["name"]);
    $fileName = $file_name . "_" . date("YmdHis") . "." . strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $targetFile = $targetDir . $fileName;
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($targetFile)) {
        $aviso = "Desculpe, arquivo já existe.";
        $uploadOk = 0;
    }

    // Check file size
    if ($file["size"] > 5000000) {
        $aviso = "Desculpe, seu arquivo é muito grande.";
        $uploadOk = 0;
    }

    // Allow certain file formats (pdf, png, jpg, jpeg)
    if (!in_array($fileType, ['pdf', 'png', 'jpg', 'jpeg'])) {
        $aviso = "Desculpe, apenas arquivos PDF, PNG, JPG e JPEG são permitidos.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $aviso = "Desculpe, seu arquivo não foi enviado.";
    } else {
        // Try to upload file
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            $aviso = "O arquivo " . htmlspecialchars(basename($file["name"])) . " foi enviado com sucesso.";
        } else {
            $aviso = "Desculpe, houve um erro ao enviar seu arquivo.";
        }
    }

    // Return the path of the uploaded file
    return $targetFile;
}