<?php $title='Asistente IA'; $view='ai/ask'; ?>
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
    <h2>Asistente IA</h2>
    <a class="btn btn-link" href="<?= url('handovers/index') ?>">← Volver</a>
  </div>

  <form id="aiForm" method="get" action="<?= url('ai/ask') ?>" class="mb-3">
    <input type="hidden" name="r" value="ai/ask">
    <div class="input-group">
      <input name="q" class="form-control" placeholder="Pregunta en español (ej: ¿Quién tiene el portátil PW03PA30?)"
             value="<?= htmlspecialchars($q ?? '') ?>" autofocus required>
      <button id="aiBtn" class="btn btn-primary" type="submit">
        Preguntar
      </button>
    </div>
  </form>
    <p class="text-muted small mt-2">
        Ejemplos: “¿Quién tiene el portátil <code>PW03PA30</code>?” ·
        “Resumen del curso <code>Competencias Digitales 2025</code>” ·
        “Atrasados más de <code>14</code> días” ·
        “Historial del portátil <code>Curso 129</code>”.
    </p>
  <!-- Spinner (se muestra durante el envío) -->
  <div id="aiSpinner" class="d-none text-center my-3" aria-live="polite">
    <div class="spinner-border" role="status" aria-hidden="true"></div>
    <div class="small text-muted mt-2">Pensando…</div>
  </div>

  <?php if (!empty($out)): ?>
    <div class="card p-3" style="white-space:pre-wrap;"><?= htmlspecialchars($out) ?></div>
  <?php endif; ?>
</div>

<script>
  (function(){
    const f = document.getElementById('aiForm');
    const btn = document.getElementById('aiBtn');
    const sp = document.getElementById('aiSpinner');
    if (!f) return;
    f.addEventListener('submit', function(){
      sp.classList.remove('d-none');
      btn.disabled = true;
      btn.innerText = 'Pensando…';
    });
  })();
</script>
