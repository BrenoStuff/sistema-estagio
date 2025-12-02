<?php require 'config.php'; ?>
<?php 
// Define $title ANTES de incluir o head.php
$title = SIS_NAME . " - Entrar"; 
?>
<?php require 'components/head.php'; ?>

<!-- 
  CSS Adicional para o estilo Microsoft:
  - Define a largura máxima do card e o fundo branco
-->
<style>
  .login-card-wrapper {
    max-width: 440px;
    width: 100%;
  }
</style>


<body class="d-flex flex-column min-vh-100 bg-body-tertiary">
  <main class="container flex-grow-1 d-flex align-items-center justify-content-center">
    <div class="login-card-wrapper">
      <div class="card shadow-lg border-0 rounded-3">
        <div class="card-body bg-white p-4 p-sm-5">
          
          <!-- Logo -->
          <div class="mb-4">
            <i class="fab fa-microsoft fa-2x" style="color: #5E5E5E;"></i>
          </div>

          <h1 class="h4 fw-bold mb-3">Entrar</h1>
          <p class="text-muted small">Use seu login e senha do sistema.</p>


          <!-- Aviso de Erro -->
          <?php if (isset($_GET['aviso'])) { ?>
            <div class="alert alert-danger p-2 small mb-3" role="alert">
              <?php echo htmlspecialchars($_GET['aviso']); // Adiciona h() por segurança ?>
            </div>
          <?php } ?>
          
          <form action="<?php echo BASE_URL;?>backend/auth/login.php" method="POST">
            
            <!-- Mantendo os Floating Labels do seu código, pois são visualmente similares -->
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="login" name="login" placeholder="Email, RA ou login" required>
              <label for="login">Login</label>
            </div>
            
            <div class="form-floating mb-3">
              <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
              <label for="senha">Senha</label>
            </div>
            
            <!-- Botão de Entrar alinhado à direita, como na MS -->
            <div class="d-flex justify-content-end mt-4">
              <button type="submit" class="btn btn-primary px-4 py-2">Entrar</button>
            </div>

          </form>

        </div>
      </div>
      
      <!-- Aviso de login futuro da MS -->
      <div class="text-center text-muted small mt-4">
        Futuramente, o login será integrado com a conta Microsoft.
      </div>

    </div>
  
  </main>

  <div class="mt-auto w-100">
    <?php require 'components/footer.php'; ?>
  </div>

</body>
</html>