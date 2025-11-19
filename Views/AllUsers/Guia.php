<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<?php $appName = I18n::t('app.name'); ?>

<main class="guia-container" aria-label="<?= I18n::t('guide.hero.aria', ['app' => $appName]); ?>">
    <section class="guia-hero" aria-label="<?= I18n::t('guide.hero.aria', ['app' => $appName]); ?>">
        <div class="guia-header">
            <span class="badge"><?= I18n::t('guide.hero.badge'); ?></span>
        </div>
        <h1><?= I18n::t('guide.hero.title', ['app' => $appName]); ?></h1>
        <p><?= I18n::t('guide.hero.description'); ?></p>
        <div class="guia-cta">
            <a class="btn-prim" href="index.php?route=Register/Register"><?= I18n::t('guide.cta.register'); ?></a>
            <a class="btn-sec" href="index.php?route=Auth/Login"><?= I18n::t('guide.cta.login'); ?></a>
        </div>
    </section>

    <div class="guia-wrap">
        <div class="guia-grid" role="list" aria-label="<?= I18n::t('guide.hero.steps.aria'); ?>">
            <article class="guia-card" role="listitem">
                <div class="guia-num">1</div>
                <div class="guia-body">
                    <h3><?= I18n::t('guide.step.register.title'); ?></h3>
                    <p><?= I18n::t('guide.step.register.description'); ?></p>
                </div>
            </article>
            <article class="guia-card" role="listitem">
                <div class="guia-num">2</div>
                <div class="guia-body">
                    <h3><?= I18n::t('guide.step.access.title'); ?></h3>
                    <p><?= I18n::t('guide.step.access.description'); ?></p>
                </div>
            </article>
            <article class="guia-card" role="listitem">
                <div class="guia-num">3</div>
                <div class="guia-body">
                    <h3><?= I18n::t('guide.step.panel.title'); ?></h3>
                    <p><?= I18n::t('guide.step.panel.description'); ?></p>
                </div>
            </article>
            <article class="guia-card" role="listitem">
                <div class="guia-num">4</div>
                <div class="guia-body">
                    <h3><?= I18n::t('guide.step.repair.title'); ?></h3>
                    <p><?= I18n::t('guide.step.repair.description'); ?></p>
                </div>
            </article>
            <article class="guia-card" role="listitem">
                <div class="guia-num">5</div>
                <div class="guia-body">
                    <h3><?= I18n::t('guide.step.support.title'); ?></h3>
                    <p><?= I18n::t('guide.step.support.description'); ?></p>
                </div>
            </article>
        </div>

        <p class="guia-thanks"><?= I18n::t('guide.thanks'); ?></p>
    </div>
</main>

