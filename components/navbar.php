<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg bg-primary navbar-dark">
    <div class="container-fluid">

        <a class="navbar-brand" href="<?php echo BASE_URL; ?>"><?php echo SIS_NAME; ?></a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">

                <!-- Item (home) e botão de logout na direita da pagina -->
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>backend/auth/logout.php">Logout</a>
                </li>
                

                
                

            </ul>
        </div>

    </div>
</nav>