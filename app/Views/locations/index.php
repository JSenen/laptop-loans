<?php $title='Ubicaciones'; $view='locations/index'; ?>
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center">
    <h2>Ubicaciones</h2>
    <a href="<?= url('locations/create') ?>"><button>+ Nueva</button></a>
  </div>
  <table class="table">
    <thead><tr><th>Nombre</th><th>Tipo</th><th>Descripción</th></tr></thead>
    <tbody>
      <?php foreach ($locations as $x): ?>
      <tr>
        <td><?= htmlspecialchars($x['nombre']) ?></td>
        <td><?= htmlspecialchars($x['tipo']) ?></td>
        <td><?= htmlspecialchars($x['descripcion']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
