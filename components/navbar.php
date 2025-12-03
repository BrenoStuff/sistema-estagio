<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lógica de Notificações
$notificacoes = [];
$qtd_nao_lidas = 0;

if (isset($_SESSION['usuario']) && isset($conexao)) {
    try {
        $sql_not = "SELECT * FROM notificacoes 
                    WHERE not_user_id = ? AND not_lida = 0 
                    ORDER BY not_data DESC LIMIT 5";
        $stmt_not = $conexao->prepare($sql_not);
        $stmt_not->execute([$_SESSION['usuario']]);
        $notificacoes = $stmt_not->fetchAll();
        $qtd_nao_lidas = count($notificacoes);
    } catch (PDOException $e) {
        // Silêncio
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>">
            <i class="fas fa-graduation-cap me-2"></i><?php echo SIS_NAME; ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if (isset($_SESSION['usuario'])) { ?>
                    <?php if ($_SESSION['acesso'] == 'admin') { ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/index.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/contratos.php">Contratos</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/empresas.php">Empresas</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/alunos.php">Alunos</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/cursos.php">Cursos</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/relatorios.php">Relatórios</a></li>
                    <?php } else { ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>index.php">Meus Estágios</a></li>
                    <?php } ?>
                <?php } ?>
            </ul>

            <ul class="navbar-nav ms-auto align-items-center">
                <?php if (isset($_SESSION['usuario'])) { ?>
                    
                    <li class="nav-item me-2">
                        <button class="btn btn-link nav-link" id="theme-toggler" title="Alternar Tema">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle position-relative" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell fa-lg"></i>
                            <?php if ($qtd_nao_lidas > 0) { ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $qtd_nao_lidas; ?>
                                </span>
                            <?php } ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="notifDropdown" style="width: 350px;">
                            <li><h6 class="dropdown-header bg-body-tertiary text-dark border-bottom fw-bold">Central de Notificações</h6></li>
                            
                            <?php if ($qtd_nao_lidas > 0) { ?>
                                <?php foreach ($notificacoes as $not) { ?>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-start py-3 border-bottom" 
                                           href="<?php echo BASE_URL; ?>backend/notificacoes/marcar.php?id=<?php echo $not['not_id']; ?>&link=<?php echo urlencode($not['not_link']); ?>">
                                            <div style="width: 100%;">
                                                <div class="small text-muted mb-1"><?php echo date('d/m H:i', strtotime($not['not_data'])); ?></div>
                                                <span class="fw-bold d-block text-dark" style="white-space: normal; line-height: 1.2; margin-bottom: 2px;">
                                                    <?php echo htmlspecialchars($not['not_titulo']); ?>
                                                </span>
                                                <small class="text-secondary d-block" style="white-space: normal; line-height: 1.3;">
                                                    <?php echo htmlspecialchars($not['not_mensagem']); ?>
                                                </small>
                                            </div>
                                        </a>
                                    </li>
                                <?php } ?>
                            <?php } else { ?>
                                <li class="text-center py-4 text-muted small">
                                    <i class="fas fa-check-circle fa-2x mb-2 text-gray-300"></i><br>
                                    Nenhuma notificação nova.
                                </li>
                            <?php } ?>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle fa-lg"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>backend/auth/logout.php"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Sair</a></li>
                        </ul>
                    </li>
                <?php } else { ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-light text-primary px-3" href="<?php echo BASE_URL; ?>login.php">Entrar</a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>