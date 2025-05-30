<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPOP - Erro</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="container py-5">
    <h1> Ocorreu um erro! </h1>

    <div class="alert alert-danger" role="alert">
        <?php echo isset($_GET['aviso']) ? $_GET['aviso'] : 'Erro desconhecido!'; ?>
    </div>
    
    <?php if (isset($_GET['aviso']) && $_GET['aviso'] == 'Usuário não encontrado!') { ?>
        <a href="backend/auth/logout.php" class="btn btn-primary">Deslogar</a>
    <?php } else { ?>
        <a href="index.php" class="btn btn-primary">Voltar</a>
    <?php } ?>
    <br><br>
    

    
    <script src="js/bootstrap.js"></script>
    <script src="js/script.js"></script>
</body>
</html>