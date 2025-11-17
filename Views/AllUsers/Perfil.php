<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
  <div class="perfil-wrapper">
    
    <div class="perfil-header">
      <form method="POST" action="" enctype="multipart/form-data">
        <?= Csrf::input(); ?>
        <label for="avatarUpload" title="<?= I18n::t('profile.avatar.change'); ?>">
          <img src="<?= htmlspecialchars($userImg) ?>" class="perfil-avatar" alt="<?= I18n::t('profile.avatar.alt'); ?>">
        </label>
        <input type="file" id="avatarUpload" name="avatar" accept="image/*" style="display: none;">
      </form>
      <h2><?= htmlspecialchars($userName) ?></h2>
      <p><?= htmlspecialchars($rol) ?></p>
    </div>

    
    <div class="perfil-tabs">
      <button class="tab active" data-tab="info"><?= I18n::t('profile.tab.info') ?></button>
      <button class="tab" data-tab="ajustes"><?= I18n::t('profile.tab.settings') ?></button>
    </div>

    
    <div class="perfil-content active" id="info">
      <form method="POST" action="">
        <?= Csrf::input(); ?>
        <div class="perfil-campo">
          <label><?= I18n::t('profile.field.name') ?>:</label>
          <input type="text" name="name" value="<?= htmlspecialchars($userName) ?>">
        </div>

        <div class="perfil-campo">
          <label><?= I18n::t('profile.field.email') ?>:</label>
          <input type="email" name="email" value="<?= htmlspecialchars($userEmail) ?>">
        </div>

        <div class="perfil-campo">
          <label><?= I18n::t('profile.field.role') ?>:</label>
          <input type="text" value="<?= htmlspecialchars($rol) ?>" readonly>
        </div>

        <?php if ($rol === 'Tecnico'): ?>
          <div class="perfil-campo">
            <label><?= I18n::t('profile.field.specialty') ?>:</label>
            <input type="text" name="especialidad" value="<?= htmlspecialchars($tecnicoEspecialidad ?? '') ?>" placeholder="<?= I18n::t('profile.specialty.placeholder'); ?>" />
          </div>
        <?php endif; ?>

        <br>
        <button type="submit" class="btn-perfil-guardar"><?= I18n::t('profile.actions.save') ?></button>
      </form>
    </div>

    
    <div class="perfil-content" id="ajustes">
      <?php if ($rol === 'Tecnico'): ?>
      <form method="POST" action="">
        <?= Csrf::input(); ?>
        <div class="perfil-campo">
          <label><?= I18n::t('profile.field.availability') ?>:</label>
          <?php $dispActual = $tecnicoDisponibilidad ?? 'Disponible'; ?>
          <select name="disponibilidad">
            <option value="Disponible" <?= ($dispActual === 'Disponible') ? 'selected' : '' ?>><?= I18n::t('profile.availability.available') ?></option>
            <option value="Ocupado" <?= ($dispActual === 'Ocupado') ? 'selected' : '' ?>><?= I18n::t('profile.availability.unavailable') ?></option>
          </select>
        </div>
        <button type="submit" class="btn-perfil-guardar"><?= I18n::t('profile.actions.saveSettings') ?></button>
      </form>
      <?php endif; ?>

      
      <?php 
        $current = $_SERVER['REQUEST_URI'] ?? 'index.php?route=Default/Perfil';
        $prev = htmlspecialchars($current, ENT_QUOTES, 'UTF-8');
        $locale = function_exists('I18n\\getLocale') ? I18n::getLocale() : ($_SESSION['lang'] ?? 'es');
      ?>
<form method="get" action="index.php" class="perfil-idioma idioma-card">
    <input type="hidden" name="route" value="Language/Set" />
    <input type="hidden" name="prev" value="<?= $prev ?>" />

    <div class="idioma-title">
        🌐 <span><?= I18n::t('profile.language.label') ?></span>
    </div>

    <div class="idioma-controls">
        <select id="langSelect" name="lang">
          <option value="es" <?= ($locale==='es'?'selected':'') ?>><?= I18n::t('lang.spanish') ?></option>
          <option value="en" <?= ($locale==='en'?'selected':'') ?>><?= I18n::t('lang.english') ?></option>
          <option value="pt" <?= ($locale==='pt'?'selected':'') ?>><?= I18n::t('lang.portuguese') ?></option>
        </select>

        <button type="submit" class="idioma-btn">
            <?= I18n::t('profile.language.change') ?>
        </button>
    </div>
</form>



    <div class="perfil-volver-panel">
      <a href="index.php?route=Default/Index" class="btn-volver-panel">
        <i class="bx bx-arrow-back"></i> <?= I18n::t('profile.back') ?>
      </a>
    </div>
  </div>
</main>
<script src="js/perfil-tabs.js?v=<?= time(); ?>" defer></script>
<script src="js/DarkMode.js?v=<?= time(); ?>" defer></script>
