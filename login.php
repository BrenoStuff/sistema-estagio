<?php require 'config.php'; ?>
<?php 
// Define $title ANTES de incluir o head.php
$title = SIS_NAME . " - Login"; 
?>
<?php require 'components/head.php'; ?>

<!-- 
  Layout de <body> modificado para centralizar o conteúdo:
  - d-flex: Ativa o Flexbox
  - flex-column: Organiza os itens em coluna (main e footer)
  - min-vh-100: Garante que o corpo ocupe pelo menos 100% da altura da tela
  - bg-body-tertiary: Fundo que reage ao modo claro/escuro
-->
<body class="d-flex flex-column min-vh-100 bg-body-tertiary">

    <!-- 
      Container principal:
      - flex-grow-1: Faz este container "crescer" e ocupar o espaço, empurrando o footer para baixo.
      - d-flex align-items-center justify-content-center: Centraliza o card de login vertical e horizontalmente.
    -->
    <main class="container flex-grow-1 d-flex align-items-center justify-content-center">
        <div class="col-lg-4 col-md-6 col-sm-8">
            
            <!-- Card de Login -->
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4 p-sm-5">

                    <!-- Título e Ícone -->
                    <div class="text-center mb-4">
                        <i class="fas fa-book-reader fa-3x text-primary"></i>
                        <h1 class="h3 fw-bold mt-2 mb-0"><?php echo SIS_NAME; ?></h1>
                        <p class="text-muted">Acesse seu painel de estágios</p>
                    </div>

                    <!-- Alerta de Erro (movido para dentro do card) -->
                    <?php if (isset($_GET['aviso'])) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($_GET['aviso']); // Adiciona h() por segurança ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php } ?>

                    <!-- Formulário com Input Groups -->
                    <form action="<?php echo BASE_URL;?>backend/auth/login.php" method="POST">
                        <label for="login" class="form-label small">Login</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="login" name="login" placeholder="Login" required>
                        </div>
                        
                        <label for="senha" class="form-label small">Senha</label>
                        <div class="input-group mb-4">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Entrar
                        </button>
                    </form>
                    
                    <!-- Placeholder para o login da Microsoft -->
                    <div class="text-center mt-4">
                        <p class="text-muted small">Futuramente, o login será integrado com sua conta Microsoft.</p>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- 
      Rodapé:
      - mt-auto: Margem superior automática, empurra o rodapé para o fim do container flex (body).
    -->
    <div class="mt-auto">
        <?php require 'components/footer.php'; ?>
    </div>
</body>
</html>