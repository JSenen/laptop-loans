<?php $title='Cursos'; $__view='courses/index'; ?>
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center">
    <h2>Cursos</h2>
    <a href="<?= url('courses/create') ?>"><button>+ Nuevo curso</button></a>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>Nombre</th><th>Inicio</th><th>Fin</th><th>Exportar</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($courses as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['nombre']) ?></td>
        <td><?= htmlspecialchars($c['fecha_inicio']) ?></td>
        <td><?= htmlspecialchars($c['fecha_fin']) ?></td>
        <td>
          <a class="newtab" target="_blank" rel="noopener"
             href="<?= url('exports/course') ?>&id=<?= (int)$c['id'] ?>">
             📄 Excel
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
