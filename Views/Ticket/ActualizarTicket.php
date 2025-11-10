<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
    <div class="content">

        <div class="contact-wrapper animated bounceInUp">
            <div class="contact-form">
                <h3><?= __('ticket.edit.title') ?></h3>

                <form id="formActualizar" method="POST" action="/ProyectoPandora/Public/index.php?route=Ticket/Actualizar" enctype="multipart/form-data">
                    <?= Csrf::input(); ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($ticket['id'] ?? '') ?>">

                    
                    <p class="block">
                        <label for="descripcionFalla"><?= __('ticket.common.description') ?></label>
                        <textarea name="descripcion_falla" id="descripcionFalla" rows="3" required><?= htmlspecialchars($ticket['descripcion_falla'] ?? $ticket['descripcion'] ?? '') ?></textarea>
                    </p>

                    
                    <?php if ($rol === 'Cliente'): ?>
                        <p>
                            <label for="marca"><?= __('ticket.edit.device.brand') ?></label>
                            <input type="text" id="marca" name="marca" value="<?= htmlspecialchars($ticket['marca'] ?? '') ?>" required>
                        </p>

                        <p>
                            <label for="modelo"><?= __('ticket.edit.device.model') ?></label>
                            <input type="text" id="modelo" name="modelo" value="<?= htmlspecialchars($ticket['modelo'] ?? '') ?>" required>
                        </p>
                    <?php endif; ?>

                    
                    <?php if (in_array($rol, ['Tecnico', 'Supervisor', 'Administrador'])): ?>
                        <p>
                            <label for="estado_id"><?= __('ticket.edit.state') ?></label>
                            <select id="estado_id" name="estado_id" required>
                                <?php foreach ($estados as $estado): ?>
                                    <option value="<?= $estado['id'] ?>" <?= ($estado['id'] == ($ticket['estado_id'] ?? '')) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($estado['name'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    <?php endif; ?>

                        
                        <?php if (in_array($rol, ['Supervisor'])): ?>
                        <p>
                            <label for="tecnico_id"><?= __('ticket.edit.assignTech') ?></label>
                            <select id="tecnico_id" name="tecnico_id">
                                <option value=""><?= __('common.unassignedOption') ?></option>
                                <?php foreach ($tecnicos as $tecnico): ?>
                                    <option value="<?= $tecnico['id'] ?>" <?= ($tecnico['id'] == ($ticket['tecnico_id'] ?? '')) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tecnico['name'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    <?php endif; ?>

                    <fieldset style="margin-top:12px;">
                        <legend><?= __('ticket.photos.title') ?></legend>
                        <p class="block">
                            <label for="fotos"><?= __('ticket.photos.addEdit') ?></label>
                            <input type="file" id="fotos" name="fotos[]" accept="image/*" multiple />
                        </p>
                        <?php if (!empty($fotos ?? [])): ?>
                            <div class="galeria-slider" style="display:flex; gap:8px; overflow-x:auto; padding:6px 0;">
                                <?php foreach (($fotos ?? []) as $src): ?>
                                    <img src="<?= htmlspecialchars($src) ?>" alt="<?= __('ticket.photos.alt') ?>" style="height:120px; border-radius:8px; object-fit:cover;"/>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </fieldset>

                    <p class="block" style="margin-top:12px;">
                        <button type="submit"><?= __('common.saveChanges') ?></button>
                    </p>
                </form>

                <a href="<?= $_SESSION['prev_url'] ?? '/ProyectoPandora/Public/index.php?route=Default/Index' ?>" class="btn-volver"><?= __('common.back') ?></a>
            </div>
        </div>

    </div>
</main>
