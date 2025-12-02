<?php
// Pega a variável $navActive, se ela foi definida na página que incluiu este navbar
$navActive = $navActive ?? '';

// Verifica o nível de acesso da sessão. O padrão é 'aluno' se não estiver definido.
// 'verifica.php' deve ter sido incluído ANTES deste arquivo para que $_SESSION['acesso'] exista.
$user_acesso = $_SESSION['acesso'] ?? 'aluno';

?>
<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg bg-primary navbar-dark shadow-sm sticky-top">
    <div class="container-fluid">

        <?php if ($user_acesso == 'admin'): ?>
            <!-- Título do Sistema (Admin) -->
            <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>admin/index.php">
                <i class="fas fa-user-shield me-1"></i>
                <?php echo SIS_NAME; ?> - Admin
            </a>
        <?php else: ?>
            <!-- Título do Sistema (Aluno) -->
            <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>index.php">
                <i class="fas fa-user-graduate me-1"></i>
                <?php echo SIS_NAME; ?> - Aluno
            </a>
        <?php endif; ?>

        <!-- Botão de Toggle para mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Conteúdo Colapsável -->
        <div class="collapse navbar-collapse" id="navbarNav">

            <!-- Links Principais (esquerda) -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($user_acesso == 'admin'): ?>
                    <!-- Link Admin -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($navActive == 'home' ? 'active' : ''); ?>" href="<?php echo BASE_URL; ?>admin/index.php">
                            <i class="fas fa-fw fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                <?php else: ?>
                    <!-- Link Aluno -->
                     <li class="nav-item">
                        <a class="nav-link <?php echo ($navActive == 'home' ? 'active' : ''); ?>" href="<?php echo BASE_URL; ?>index.php">
                            <i class="fas fa-fw fa-home"></i> Home
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Itens de Ação (direita) -->
            <ul class="navbar-nav ms-auto d-flex flex-row align-items-center">

                <!-- Botão Troca de Tema -->
                <li class="nav-item me-3">
                    <button type="button" class="btn btn-outline-light btn-sm" id="theme-toggler" title="Alternar Tema">
                        <i class="fas fa-moon"></i>
                    </button>
                </li>

                <!-- Botão de Logout -->
                <li class="nav-item">
                    <a class="btn btn-danger btn-sm" href="<?php echo BASE_URL; ?>backend/auth/logout.php" title="Sair">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                </li>

            </ul>
        </div>

    </div>
</nav>