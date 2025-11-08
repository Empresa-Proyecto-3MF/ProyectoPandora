<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<main class="guia-container">
    <section class="guia-hero" aria-label="Guía de uso de Innovasys">
        <div class="guia-header">
            <span class="badge" data-lang="guia_rapida">Guía rápida</span>
            <button id="toggleLang" class="lang-btn">EN 🇬🇧</button>
        </div>
        <h1 data-lang="titulo">Cómo usar Innovasys</h1>
        <p data-lang="descripcion">
            Seguí estos pasos para registrar tus dispositivos, crear tickets y hacer seguimiento de tus reparaciones.
        </p>
        <div class="guia-cta">
            <a class="btn-prim" href="/ProyectoPandora/Public/index.php?route=Register/Register" data-lang="registrarme">Registrarme</a>
            <a class="btn-sec" href="/ProyectoPandora/Public/index.php?route=Auth/Login" data-lang="ya_cuenta">Ya tengo cuenta</a>
        </div>
    </section>

    <div class="guia-wrap">
        <div class="guia-grid" role="list" aria-label="Pasos de uso">
            <article class="guia-card" role="listitem">
                <div class="guia-num">1</div>
                <div class="guia-body">
                    <h3 data-lang="registro_titulo">Registro</h3>
                    <p data-lang="registro_desc">
                        Creá tu cuenta desde <strong>Registrarse</strong> con tu nombre, email y contraseña.
                    </p>
                </div>
            </article>
            <article class="guia-card" role="listitem">
                <div class="guia-num">2</div>
                <div class="guia-body">
                    <h3 data-lang="acceso_titulo">Acceso</h3>
                    <p data-lang="acceso_desc">
                        Ingresá a tu cuenta desde <strong>Iniciar sesión</strong> para entrar a tu panel.
                    </p>
                </div>
            </article>
            <article class="guia-card" role="listitem">
                <div class="guia-num">3</div>
                <div class="guia-body">
                    <h3 data-lang="panel_titulo">Panel de usuario</h3>
                    <p data-lang="panel_desc">
                        Gestioná tus <em>dispositivos</em> y consultá tus <em>tickets</em> en curso.
                    </p>
                </div>
            </article>
            <article class="guia-card" role="listitem">
                <div class="guia-num">4</div>
                <div class="guia-body">
                    <h3 data-lang="reparacion_titulo">Solicitar reparación</h3>
                    <p data-lang="reparacion_desc">
                        Agregá tu dispositivo y <strong>creá un ticket</strong>. Podés ver el estado en todo momento.
                    </p>
                </div>
            </article>
            <article class="guia-card" role="listitem">
                <div class="guia-num">5</div>
                <div class="guia-body">
                    <h3 data-lang="soporte_titulo">Soporte y seguimiento</h3>
                    <p data-lang="soporte_desc">
                        Ante dudas, consultá la ayuda o contactá a soporte desde tu panel.
                    </p>
                </div>
            </article>
        </div>

        <p class="guia-thanks" data-lang="gracias">¡Gracias por confiar en <strong>Innovasys</strong>! 💜</p>
    </div>
</main>
<script src="/ProyectoPandora/Public/js/guia.js?v=<?= time(); ?>" defer></script>

