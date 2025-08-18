<?php $title='Personas'; $view='people/index'; ?>
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center">
    <h2>Personas</h2>
    <a href="<?= url('people/create') ?>"><button>+ Nueva persona</button></a>

  </div>
  <table class="table">
    <thead><tr><th>Nombre</th><th>DNI</th><th>TIP</th><th>Teléfono</th><th>Email</th></tr></thead>
    <tbody>
    <?php foreach ($people as $p): ?>
      <tr>
        <td><?= htmlspecialchars($p['nombre'].' '.$p['apellidos']) ?></td>
        <td><?= htmlspecialchars($p['dni']) ?></td>
        <td><?= htmlspecialchars($p['tip']) ?></td>
        <td><?= htmlspecialchars($p['telefono']) ?></td>
        <td><?= htmlspecialchars($p['email']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>