<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<main>
    <?php include_once __DIR__ . '/../Includes/Header.php'; ?>
    <div class="Contenedor-stats">
        <section class="tecnico-stats">
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-num"><?= (int)($counters['finalizados'] ?? 0) ?></span>
                    <span class="stat-label">Tickets finalizados</span>
                </div>
                <div class="stat-card">
                    <span class="stat-num"><?= (int)($counters['activos'] ?? 0) ?></span>
                    <span class="stat-label">Tickets activos</span>
                </div>
                <div class="stat-card">
                    <span class="stat-num"><?= isset($avg) ? htmlspecialchars(number_format((float)$avg,1,'.','')) : '—' ?></span>
                    <span class="stat-label">Honor promedio (★)</span>
                </div>
                <div class="stat-card">
                    <span class="stat-num"><?= (int)($count ?? 0) ?></span>
                    <span class="stat-label">Calificaciones</span>
                </div>
            </div>
        </section>
        <section class="tecnico-reviews">
            <h2>Reseñas de clientes</h2>
            <?php if (empty($reviews)): ?>
                <p class="empty-state">Aún no recibes reseñas.</p>
            <?php else: ?>
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <?php
                            $cliente = $review['cliente_nombre'] ?? 'Cliente';
                            $email = $review['cliente_email'] ?? '';
                            $stars = (int)($review['stars'] ?? 0);
                            $createdAt = $review['created_at'] ?? '';
                            $createdLabel = $createdAt ? date('d/m/Y H:i', strtotime($createdAt)) : '';
                            $ticketId = (int)($review['ticket_id'] ?? 0);
                        ?>
                        <article class="review-card">
                            <header class="review-header">
                                <strong><?= htmlspecialchars($cliente) ?></strong>
                                <?php if ($email !== ''): ?>
                                    <span class="review-email">(<?= htmlspecialchars($email) ?>)</span>
                                <?php endif; ?>
                                <span class="review-stars">Calificación: <?= $stars ?>/5</span>
                            </header>
                            <?php if (!empty($review['comment'])): ?>
                                <p class="review-comment"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                            <?php endif; ?>
                            <footer class="review-meta">
                                <?php if ($ticketId > 0): ?>
                                    <span>Ticket #<?= $ticketId ?></span>
                                <?php endif; ?>
                                <?php if ($createdLabel !== ''): ?>
                                    <span><?= htmlspecialchars($createdLabel) ?></span>
                                <?php endif; ?>
                            </footer>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

</main>

<script src="/ProyectoPandora/Public/js/tecnicos-mis-stats.js?v=<?= time(); ?>" defer></script>

