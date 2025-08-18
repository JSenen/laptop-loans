<?php $title='Portátiles'; $view='laptops/index'; ?>
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center">
    <h2>Portátiles</h2>
    <a href="<?= url('laptops/create') ?>"><button>+ Nuevo portátil</button></a>

  </div>
  <table class="table">
    <thead><tr><th>Nº Serie</th><th>Marca</th><th>Modelo</th><th>Estado</th></tr></thead>
    <tbody>
    <?php foreach ($laptops as $l): ?>
      <tr>
        <td><?= htmlspecialchars($l['num_serie']) ?></td>
        <td><?= htmlspecialchars($l['marca']) ?></td>
        <td><?= htmlspecialchars($l['modelo']) ?></td>
        <td><?= htmlspecialchars($l['estado']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>