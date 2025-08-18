<?php $title='Devolución'; $__view='handovers/devolucion'; ?>
<div class="card">
  <h2>Registrar devolución</h2>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

    <div class="card" style="background:#0b152e">
      <label>Portátil prestado</label>
      <select name="laptop_id" required>
        <option value="">--</option>
        <?php foreach(($prestados ?? []) as $x): ?>
          <option value="<?= (int)$x['id'] ?>">
            <?= htmlspecialchars($x['num_serie']) ?> — <?= htmlspecialchars($x['nombre'].' '.$x['apellidos']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label style="margin-top:12px">Fecha</label>
      <input type="datetime-local" name="fecha">

      <label style="margin-top:12px">Observaciones</label>
      <textarea name="observaciones" rows="3"></textarea>
    </div>

    <div style="margin-top:12px"><button>Registrar devolución</button></div>
  </form>
</div>
