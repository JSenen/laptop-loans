<?php $title='Entrega'; $__view='handovers/entrega'; ?>
<div class="card">
  <h2>Registrar entrega</h2>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

    <div class="card" style="background:#0b152e">
      <h3>Persona</h3>
      <label>Selecciona una persona</label>
      <select name="person_id" required>
        <option value="">--</option>
        <?php foreach(($people ?? []) as $p): ?>
          <option value="<?= (int)$p['id'] ?>">
            <?= htmlspecialchars($p['nombre'].' '.$p['apellidos']) ?>
            (<?= htmlspecialchars($p['dni'] ?: $p['tip']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <div style="margin-top:6px">
        <a href="<?= url('people/create') ?>"><button type="button" class="secondary">+ Nueva persona</button></a>
      </div>
    </div>

    <div class="card" style="background:#0b152e">
      <h3>Equipo</h3>
      <label>Portátil disponible</label>
      <select name="laptop_id" required>
        <option value="">--</option>
        <?php foreach(($laptops ?? []) as $l): ?>
          <option value="<?= (int)$l['id'] ?>"><?= htmlspecialchars($l['num_serie']) ?></option>
        <?php endforeach; ?>
      </select>
      <div style="margin-top:6px">
        <a href="<?= url('laptops/create') ?>"><button type="button" class="secondary">+ Nuevo portátil</button></a>
      </div>

      <label style="margin-top:12px">Curso</label>
      <select name="course_id">
        <option value="">--</option>
        <?php foreach(($courses ?? []) as $c): ?>
          <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
        <?php endforeach; ?>
      </select>

      <label style="margin-top:12px">Observaciones</label>
      <textarea name="observaciones" rows="3"></textarea>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px">
        <div><label>Fecha</label><input type="datetime-local" name="fecha"></div>
        <div></div>
      </div>
    </div>

    <div style="margin-top:12px"><button>Registrar entrega</button></div>
  </form>
</div>
