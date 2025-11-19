<?php
require_once __DIR__ . '/../../Core/ImageHelper.php';
$fallbackTicketImg = device_image_url('');
include_once __DIR__ . '/../Includes/Sidebar.php';
?>


<main>
<?php include_once __DIR__ . '/../Includes/Header.php'; ?>

<div class="Contenedor">
    <form method="get" action="index.php" class="filtros" style="display:flex;gap:10px;flex-wrap:wrap;margin:10px 0;align-items:center;">
        <input type="hidden" name="route" value="Cliente/MisTicketTerminados" />
        <?php $estadoSel = strtolower($_GET['estado'] ?? 'finalizados'); ?>
        <select name="estado" class="asignar-input asignar-input--small">
            <option value="finalizados" <?= $estadoSel==='finalizados'?'selected':'' ?>>Finalizados</option>
            <option value="activos" <?= $estadoSel==='activos'?'selected':'' ?>>Activos</option>
            <option value="todos" <?= $estadoSel==='todos'?'selected':'' ?>>Todos</option>
        </select>
        <input name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="asignar-input asignar-input--small" type="text" placeholder="Buscar..." />
        <input name="desde" value="<?= htmlspecialchars($_GET['desde'] ?? '') ?>" class="asignar-input asignar-input--small" type="date" />
        <input name="hasta" value="<?= htmlspecialchars($_GET['hasta'] ?? '') ?>" class="asignar-input asignar-input--small" type="date" />
        <button class="btn btn-primary" type="submit">Filtrar</button>
        <a class="btn btn-outline" href="index.php?route=Cliente/MisTicketTerminados">Limpiar</a>
    </form>

    <section class="section-mis-tickets">
        <h2 class="titulo-carrusel">Tickets Finalizados</h2>

        <div class="carousel-container">
            <button class="carousel-btn prev-btn" id="prevTicketBtnFinished">&#10094;</button>

            <div class="carousel-track" id="carouselTicketTrackFinished">
                <?php if (!empty($tickets)): ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <?php 
                            
                            $imgSrc = (string)($ticket['img_preview'] ?? '');
                            if ($imgSrc === '') { $imgSrc = $fallbackTicketImg; }

                            $estado = strtolower(trim($ticket['estado'] ?? 'finalizado'));
                            $estadoMap = [
                                'nuevo' => 'estado-nuevo',
                                'diagnóstico' => 'estado-diagnostico',
                                'diagnostico' => 'estado-diagnostico',
                                'presupuesto' => 'estado-presupuesto',
                                'en espera' => 'estado-espera',
                                'en reparación' => 'estado-reparacion',
                                'en reparacion' => 'estado-reparacion',
                                'en pruebas' => 'estado-pruebas',
                                'listo para retirar' => 'estado-retiro',
                                'finalizado' => 'estado-finalizado',
                                'cancelado' => 'estado-cancelado'
                            ];
                            $estadoClass = $estadoMap[$estado] ?? 'estado-default';
                            $isWorking = false; 
                        ?>
                        <article class="ticket-card">
                                                        <div class="ticket-img">
                                                                <img 
                                                                    src="<?= htmlspecialchars($imgSrc) ?>" 
                                                                    alt="Ticket #<?= (int)$ticket['id'] ?> - <?= htmlspecialchars(($ticket['dispositivo'] ?? '') . ' ' . ($ticket['modelo'] ?? '')) ?>"
                                                                    loading="lazy"
                                                                    decoding="async"
                                                                    onerror="this.onerror=null;this.src='<?= htmlspecialchars($fallbackTicketImg, ENT_QUOTES, 'UTF-8') ?>'"
                                                                >
                                                        </div>
                            <div class="ticket-info">
                                <h3><?= htmlspecialchars($ticket['dispositivo']) ?> <?= htmlspecialchars($ticket['modelo']) ?></h3>
                                <p class="line-clamp-3"><strong>Descripción:</strong> <?= htmlspecialchars($ticket['descripcion_falla']) ?></p>

                                <div class="ticket-estado-wrapper">
                                    <strong>Estado:</strong>
                                    <span class="estado-tag <?= $estadoClass ?>"><?= htmlspecialchars(ucfirst($estado)) ?></span>
                                </div>

                                <p><strong>Fecha:</strong> <time title="<?= htmlspecialchars($ticket['fecha_exact'] ?? '') ?>"><?= htmlspecialchars($ticket['fecha_human'] ?? '') ?></time></p>
                                <p><strong>Técnico:</strong> <?= htmlspecialchars($ticket['tecnico'] ?? 'Sin asignar') ?></p>
                            </div>

                            <div class="card-actions">
                                <a href="index.php?route=Ticket/Ver&id=<?= (int)$ticket['id'] ?>" class="btn btn-primary">Ver detalle</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No tienes tickets finalizados o cancelados.</p>
                <?php endif; ?>
            </div>

            <button class="carousel-btn next-btn" id="nextTicketBtnFinished">&#10095;</button>
        </div>
    </section>
</div>

</main>

<script src="js/clientes-mis-ticket-terminados.js" defer></script>
