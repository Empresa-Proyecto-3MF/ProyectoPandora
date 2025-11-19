<?php include_once __DIR__ . '/../Includes/Sidebar.php' ?>

<main>
    <div class="content">

    <h1 class="logo"><?= I18n::t('ticket.common.ticket') ?> <span><?= I18n::t('ticket.common.new') ?></span></h1>

        <div class="contact-wrapper animated bounceInUp">
            <div class="contact-form">
                <h3><?= I18n::t('ticket.create.heading') ?></h3>

                <?php if (isset($_GET['error'])): ?>
                    <?php
                      $errKey = (string)$_GET['error'];
                      $map = [
                        'deviceRequired' => 'ticket.create.error.deviceRequired',
                        'deviceOwnership' => 'ticket.create.error.deviceOwnership',
                        'deviceActive' => 'ticket.create.error.deviceActive',
                        'noDevices' => 'ticket.create.error.noDevices',
                      ];
                      $langKey = $map[$errKey] ?? null;
                    ?>
                    <div class="alert alert-warning">
                        <?= $langKey ? I18n::t( $langKey ) : htmlspecialchars($errKey) ?>
                    </div>
                <?php elseif (!empty($errorCode ?? '')): ?>
                    <?php $langKey = 'ticket.create.error.' . $errorCode; ?>
                    <div class="alert alert-warning">
                        <?= I18n::t($langKey) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?route=Ticket/Crear">
                    <?= Csrf::input(); ?>
                    <input type="hidden" name="recarga_cliente" value="1">

                    <?php if (isset($isAdmin) && $isAdmin && isset($clientes)): ?>
                        <p>
                            <label for="cliente_id"><?= I18n::t('ticket.create.select.client') ?></label>
                            <select id="cliente_id" name="cliente_id" required>
                                <option value=""><?= I18n::t('ticket.common.select') ?></option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= $cliente['id'] ?>" <?= (isset($_POST['cliente_id']) && $_POST['cliente_id'] == $cliente['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cliente['name']) ?> (<?= htmlspecialchars($cliente['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    <?php endif; ?>

                    <p>
                        <label for="dispositivoSelect"><?= I18n::t('ticket.create.select.device') ?></label>
                        <select id="dispositivoSelect" name="dispositivo_id" required>
                            <option value=""><?= I18n::t('ticket.create.select.device.placeholder') ?></option>
                            <?php foreach ($data as $dispositivo): ?>
                                <?php 
                                    $hasActive = !empty($dispositivo['hasActive']);
                                    $label = ($dispositivo['marca'] ?? '') . ' ' . ($dispositivo['modelo'] ?? '');
                                    if ($hasActive) { $label .= ' ' . I18n::t('ticket.common.activeSuffix'); }
                                ?>
                                <option value="<?= $dispositivo['id'] ?>" data-descripcion="<?= htmlspecialchars($dispositivo['descripcion_falla'] ?? '') ?>" <?= $hasActive ? 'disabled' : '' ?>>
                                    <?= htmlspecialchars(trim($label)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </p>

                    <p class="block">
                        <label for="descripcion"><?= I18n::t('ticket.common.description') ?>:</label>
                        <textarea id="descripcion" name="descripcion" rows="4" required></textarea>
                    </p>

                    <p>
                        <button type="submit" id="btnCrearTicket"><?= I18n::t('ticket.create.submit') ?></button>
                    </p>

                    <p>
                        <a href="<?php echo (isset($isAdmin) && $isAdmin) 
                            ? 'index.php?route=Ticket/Listar' 
                            : 'index.php?route=Cliente/MisTicketActivo'; ?>" 
                            class="btn-volver"><?= I18n::t('common.cancel') ?></a>
                    </p>
                </form>

            </div>
        </div>
    </div>
</main>

<script src="js/ticket-crear.js" defer></script>
