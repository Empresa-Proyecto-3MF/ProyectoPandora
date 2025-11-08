<?php include_once __DIR__ . '/../Includes/Sidebar.php' ?>

<main>
    <div class="content">

        <h1 class="logo">Ticket <span>Nuevo</span></h1>

        <div class="contact-wrapper animated bounceInUp">
            <div class="contact-form">
                <h3>Crear nuevo Ticket</h3>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-warning">
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/ProyectoPandora/Public/index.php?route=Ticket/Crear">
                    <input type="hidden" name="recarga_cliente" value="1">

                    <?php if (isset($isAdmin) && $isAdmin && isset($clientes)): ?>
                        <p>
                            <label for="cliente_id">Seleccione un cliente:</label>
                            <select id="cliente_id" name="cliente_id" required>
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= $cliente['id'] ?>" <?= (isset($_POST['cliente_id']) && $_POST['cliente_id'] == $cliente['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cliente['name']) ?> (<?= htmlspecialchars($cliente['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    <?php endif; ?>

                    <p>
                        <label for="dispositivoSelect">Seleccione un dispositivo:</label>
                        <select id="dispositivoSelect" name="dispositivo_id" required>
                            <option value="">Selecciona un dispositivo</option>
                            <?php foreach ($data as $dispositivo): ?>
                                <?php 
                                    $hasActive = !empty($dispositivo['hasActive']);
                                    $label = ($dispositivo['marca'] ?? '') . ' ' . ($dispositivo['modelo'] ?? '');
                                    if ($hasActive) { $label .= ' — (con ticket activo)'; }
                                ?>
                                <option value="<?= $dispositivo['id'] ?>" data-descripcion="<?= htmlspecialchars($dispositivo['descripcion_falla'] ?? '') ?>" <?= $hasActive ? 'disabled' : '' ?>>
                                    <?= htmlspecialchars(trim($label)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </p>

                    <p class="block">
                        <label for="descripcion">Descripción de la falla:</label>
                        <textarea id="descripcion" name="descripcion" rows="4" required></textarea>
                    </p>

                    <p>
                        <button type="submit" id="btnCrearTicket">Crear Ticket</button>
                    </p>

                    <p>
                        <a href="<?php echo (isset($isAdmin) && $isAdmin) 
                            ? '/ProyectoPandora/Public/index.php?route=Ticket/Listar' 
                            : '/ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo'; ?>" 
                            class="btn-volver">Cancelar</a>
                    </p>
                </form>

            </div>
        </div>
    </div>
</main>

<script src="/ProyectoPandora/Public/js/ticket-crear.js" defer></script>
