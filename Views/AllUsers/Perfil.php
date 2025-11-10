<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
  <div class="perfil-wrapper">
    
    <div class="perfil-header">
      <form method="POST" action="" enctype="multipart/form-data">
        <?= Csrf::input(); ?>
        <label for="avatarUpload" title="<?= __('profile.avatar.change'); ?>">
          <img src="<?= htmlspecialchars($userImg) ?>" class="perfil-avatar" alt="<?= __('profile.avatar.alt'); ?>">
        </label>
        <input type="file" id="avatarUpload" name="avatar" accept="image/*" style="display: none;">
      </form>
      <h2><?= htmlspecialchars($userName) ?></h2>
      <p><?= htmlspecialchars($rol) ?></p>
    </div>

    
    <div class="perfil-tabs">
      <button class="tab active" data-tab="info"><?= __('profile.tab.info') ?></button>
      <button class="tab" data-tab="ajustes"><?= __('profile.tab.settings') ?></button>
    </div>

    
    <div class="perfil-content active" id="info">
      <form method="POST" action="">
        <?= Csrf::input(); ?>
        <div class="perfil-campo">
          <label><?= __('profile.field.name') ?>:</label>
          <input type="text" name="name" value="<?= htmlspecialchars($userName) ?>">
        </div>

        <div class="perfil-campo">
          <label><?= __('profile.field.email') ?>:</label>
          <input type="email" name="email" value="<?= htmlspecialchars($userEmail) ?>">
        </div>

        <div class="perfil-campo">
          <label><?= __('profile.field.role') ?>:</label>
          <input type="text" value="<?= htmlspecialchars($rol) ?>" readonly>
        </div>

        <?php if ($rol === 'Tecnico'): ?>
          <div class="perfil-campo">
            <label><?= __('profile.field.specialty') ?>:</label>
            <input type="text" name="especialidad" value="<?= htmlspecialchars($tecnicoEspecialidad ?? '') ?>" placeholder="<?= __('profile.specialty.placeholder'); ?>" />
          </div>
        <?php endif; ?>

        <button type="submit" class="btn-perfil-guardar"><?= __('profile.actions.save') ?></button>
      </form>
    </div>

    
    <div class="perfil-content" id="ajustes">
      <?php if ($rol === 'Tecnico'): ?>
      <form method="POST" action="">
        <?= Csrf::input(); ?>
        <div class="perfil-campo">
          <label><?= __('profile.field.availability') ?>:</label>
          <?php $dispActual = $tecnicoDisponibilidad ?? 'Disponible'; ?>
          <select name="disponibilidad">
            <option value="Disponible" <?= ($dispActual === 'Disponible') ? 'selected' : '' ?>><?= __('profile.availability.available') ?></option>
            <option value="Ocupado" <?= ($dispActual === 'Ocupado') ? 'selected' : '' ?>><?= __('profile.availability.unavailable') ?></option>
          </select>
        </div>
        <button type="submit" class="btn-perfil-guardar"><?= __('profile.actions.saveSettings') ?></button>
      </form>
      <?php endif; ?>

      
      <?php 
        $current = $_SERVER['REQUEST_URI'] ?? '/ProyectoPandora/Public/index.php?route=Default/Perfil';
        $prev = htmlspecialchars($current, ENT_QUOTES, 'UTF-8');
        $locale = function_exists('I18n\\getLocale') ? I18n::getLocale() : ($_SESSION['lang'] ?? 'es');
      ?>
      <form method="get" action="/ProyectoPandora/Public/index.php" class="perfil-idioma">
        <input type="hidden" name="route" value="Language/Set" />
        <input type="hidden" name="prev" value="<?= $prev ?>" />
        <label for="langSelect"><?= __('profile.language.label') ?>:</label>
        <select id="langSelect" name="lang">
          <option value="es" <?= ($locale==='es'?'selected':'') ?>><?= __('lang.spanish') ?></option>
          <option value="en" <?= ($locale==='en'?'selected':'') ?>><?= __('lang.english') ?></option>
          <option value="pt" <?= ($locale==='pt'?'selected':'') ?>><?= __('lang.portuguese') ?></option>
        </select>
        <button type="submit"><?= __('profile.language.change') ?></button>
      </form>

      
      <div class="perfil-campo modo-oscuro-toggle">
        <label for="toggle-darkmode">🌙 <?= __('profile.darkmode.toggle') ?>:</label>
        <label class="switch">
          <input type="checkbox" id="toggle-darkmode">
          <span class="slider"></span>
        </label>
      </div>
    </div>

    <div class="perfil-volver-panel">
      <a href="/ProyectoPandora/Public/index.php?route=Default/Index" class="btn-volver-panel">
        <i class="bx bx-arrow-back"></i> <?= __('profile.back') ?>
      </a>
    </div>
  </div>
</main>
<script src="/ProyectoPandora/Public/js/perfil-tabs.js?v=<?= time(); ?>" defer></script>
<script src="/ProyectoPandora/Public/js/DarkMode.js?v=<?= time(); ?>" defer></script>
