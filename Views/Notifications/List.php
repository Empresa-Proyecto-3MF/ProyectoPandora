<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
  <div class="panel">
    <div class="panel-header">
      <h2>Notificaciones</h2>
      <?php
        
        $role = strtolower($authUser['role'] ?? '');
        if ($role === 'administrador' || $role === 'supervisor'):
      ?>
        <a class="btn" href="index.php?route=Notification/Create">Nueva</a>
      <?php endif; ?>
    </div>
    <div class="panel-body">
      <?php if (empty($list)): ?>
        <p>No hay notificaciones.</p>
      <?php else: ?>
        <ul class="notif-list">
          <?php foreach ($list as $n): ?>
            <li class="notif-item <?= $n['is_read'] ? 'read' : 'unread' ?>">
              <div class="notif-title"><?= htmlspecialchars($n['title']) ?></div>
              <div class="notif-body"><?= nl2br(htmlspecialchars($n['body'])) ?></div>
              <div class="notif-meta">
                <span class="badge <?= $n['is_read'] ? 'badge--muted' : 'badge--primary' ?>">
                  <?= $n['is_read'] ? 'Leída' : 'No leída' ?>
                </span>
                <span style="margin-left:8px; opacity:0.8;">
                  <?= htmlspecialchars($n['created_at']) ?>
                </span>
              </div>
              <?php if (!$n['is_read']): ?>
                <form method="POST" action="index.php?route=Notification/MarkRead" class="js-mark-read" style="display:inline;">
                  <?= Csrf::input(); ?>
                  <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                  <button class="btn small" type="submit">Marcar como leída</button>
                </form>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</main>
