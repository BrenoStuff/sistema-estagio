<?php require 'config.php'; ?>
<?php require 'components/head.php'; ?>

<body class="container py-5">
    <h1> Bem vindo </h1>

    <!-- form com boostrap usando floating label -->
    <form action="<?php echo BASE_URL;?>backend/auth/login.php" method="POST">
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="login" name="login" placeholder="Login">
            <label for="login">Login</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha">
            <label for="senha">Senha</label>
        </div>
        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>

    <br><br>

    <?php if (isset($_GET['aviso'])) { ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_GET['aviso']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>
    
    <p class="d-none d-md-block" style="margin-top: 40vh;"></p>
    <p class="d-md-none" style="margin-top: 35vh;"></p>
    <?php require 'components/footer.php'; ?>
</body>
</html>