<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<?php $canBroadcastAll = $canBroadcastAll ?? false; $allowedAudienceRoles = $allowedAudienceRoles ?? ['Cliente','Tecnico']; ?>

<main>
  <div class="notif-panel">
    <div class="notif-panel-header">
      <h2>Crear notificación</h2>
    </div>
    <div class="notif-panel-body">
      <?php if (!empty($_GET['error'])): ?>
        <div class="notif-alert notif-alert-warning">
          <?php
            $map = [
              'csrf'=>'Sesión expirada. Intenta nuevamente.',
              'aud'=>'Como supervisor no puedes enviar a "Todos".',
              'role'=>'Rol de audiencia no permitido.',
              'target'=>'Debes indicar un usuario válido.',
              'target_role'=>'Solo puedes enviar a clientes o técnicos.',
              'required'=>'Título y mensaje son obligatorios.'
            ];
            $code = $_GET['error'];
            echo htmlspecialchars($map[$code] ?? 'Error en el formulario.');
          ?>
        </div>
      <?php endif; ?>

      <form class="notif-form" method="POST" action="">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '') ?>" />

        <div class="notif-field">
          <label>Título</label>
          <input type="text" name="title" required>
        </div>

        <div class="notif-field">
          <label>Mensaje</label>
          <textarea name="body" rows="4" required></textarea>
        </div>

        <div class="notif-field">
          <label>Audiencia</label>
          <select name="audience" id="audienceSelect">
            <?php if ($canBroadcastAll): ?>
              <option value="ALL">Todos</option>
            <?php endif; ?>
            <option value="ROLE">Por rol</option>
            <option value="USER">Usuario específico</option>
          </select>
        </div>

        <div class="notif-field" id="roleField" style="display:none;">
          <label>Rol</label>
          <select name="audience_role">
            <?php foreach ($allowedAudienceRoles as $r): ?>
              <option value="<?= htmlspecialchars($r) ?>"><?= htmlspecialchars($r === 'Tecnico' ? 'Técnico' : $r) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="notif-field" id="userField" style="display:none;">
          <label>Usuario</label>
          <input type="text" id="userSearch" placeholder="Buscar por nombre o email" class="notif-input-small" oninput="filterUsers()" />
          <select name="target_user_id" id="userSelect" size="6" class="notif-select-large">
            <?php foreach (($selectableUsers ?? []) as $u): ?>
              <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars(($u['name'] ?? '')) ?> — <?= htmlspecialchars(($u['email'] ?? '')) ?> (<?= htmlspecialchars($u['role'] ?? '') ?>)</option>
            <?php endforeach; ?>
          </select>
          <small>Selecciona un usuario de la lista. Puedes filtrar por nombre o email.</small>
        </div>

        <button class="notif-btn" type="submit">Publicar</button>
      </form>
    </div>
  </div>
</main>

<script>
  const sel = document.getElementById('audienceSelect');
  const roleF = document.getElementById('roleField');
  const userF = document.getElementById('userField');

  function upd(){
    const v = sel.value;
    roleF.style.display = (v==='ROLE') ? 'block' : 'none';
    userF.style.display = (v==='USER') ? 'block' : 'none';
  }

  sel.addEventListener('change', upd);
  upd();

  function filterUsers(){
    const q = (document.getElementById('userSearch').value || '').toLowerCase();
    const select = document.getElementById('userSelect');
    for (const opt of select.options) {
      const txt = opt.textContent.toLowerCase();
      opt.hidden = q && !txt.includes(q);
    }
  }
</script>
