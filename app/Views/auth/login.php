<?php $title='Acceso'; $view='auth/login'; ?>
<div class="card">
  <h2>Acceso</h2>
  <?php if (!empty($error)): ?><p style="color:#ff7676"><?= htmlspecialchars($error) ?></p><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <label>Usuario</label>
    <input name="user" required>
    <label>Contraseña</label>
    <input name="pass" type="password" required>
    <!-- <div style="margin-top:12px"><button>Entrar</button></div> -->
    <p class="muted">Demo: admin / admin</p>
  </form>
</div>