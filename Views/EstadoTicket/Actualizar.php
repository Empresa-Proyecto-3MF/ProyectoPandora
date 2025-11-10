<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
    <div class="content">

        <div class="estado-wrapper animated bounceInUp">
            <div class="contact-form">
                <h3>Actualizar Estado</h3>

                <form method="POST" action="/ProyectoPandora/Public/index.php?route=EstadoTicket/Actualizar">
                    <?= Csrf::input(); ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($estado['id']) ?>">

                    <p>
                        <label for="name">Nombre del estado:</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($estado['name']) ?>" required>
                    </p>

                    <p class="block">
                        <button type="submit">Actualizar</button>
                    </p>
                </form>

                <a href="/ProyectoPandora/Public/index.php?route=EstadoTicket/ListarEstados" class="btn-volver">Volver a la lista de Estados</a>
            </div>
        </div>

    </div>
</main>
