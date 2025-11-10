<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<main>
    <div class="content">

        <h1 class="logo">Ticket <span>Nuevo Estado</span></h1>

        <div class="estado-wrapper animated bounceInUp">
            <div class="form-container">
                <h3>Agregar Estado de Ticket</h3>

                <form action="/ProyectoPandora/Public/index.php?route=EstadoTicket/Crear" method="POST">
                    <?= Csrf::input(); ?>
                    <label for="name">Nombre del Estado:</label>
                    <input type="text" id="name" name="name" required>

                    <button type="submit">Agregar Estado</button>
                </form>

                <a href="<?= $_SESSION['prev_url'] ?? '/ProyectoPandora/Public/index.php?route=Default/Index' ?>" class="btn-volver">Volver a la lista de estados</a>
            </div>
        </div>

    </div>
</main>
