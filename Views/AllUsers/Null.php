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

<script>
const langBtn = document.getElementById("toggleLang");

const textos = {
  es: {
    guia_rapida: "Guía rápida",
    titulo: "Cómo usar Innovasys",
    descripcion: "Seguí estos pasos para registrar tus dispositivos, crear tickets y hacer seguimiento de tus reparaciones.",
    registrarme: "Registrarme",
    ya_cuenta: "Ya tengo cuenta",
    registro_titulo: "Registro",
    registro_desc: "Creá tu cuenta desde <strong>Registrarse</strong> con tu nombre, email y contraseña.",
    acceso_titulo: "Acceso",
    acceso_desc: "Ingresá a tu cuenta desde <strong>Iniciar sesión</strong> para entrar a tu panel.",
    panel_titulo: "Panel de usuario",
    panel_desc: "Gestioná tus <em>dispositivos</em> y consultá tus <em>tickets</em> en curso.",
    reparacion_titulo: "Solicitar reparación",
    reparacion_desc: "Agregá tu dispositivo y <strong>creá un ticket</strong>. Podés ver el estado en todo momento.",
    soporte_titulo: "Soporte y seguimiento",
    soporte_desc: "Ante dudas, consultá la ayuda o contactá a soporte desde tu panel.",
    gracias: "¡Gracias por confiar en <strong>Innovasys</strong>! 💜"
  },
  en: {
    guia_rapida: "Quick Guide",
    titulo: "How to use Innovasys",
    descripcion: "Follow these steps to register your devices, create tickets, and track your repairs.",
    registrarme: "Sign up",
    ya_cuenta: "I already have an account",
    registro_titulo: "Register",
    registro_desc: "Create your account from <strong>Register</strong> with your name, email, and password.",
    acceso_titulo: "Access",
    acceso_desc: "Log in from <strong>Sign in</strong> to enter your dashboard.",
    panel_titulo: "User panel",
    panel_desc: "Manage your <em>devices</em> and check your <em>tickets</em> in progress.",
    reparacion_titulo: "Request repair",
    reparacion_desc: "Add your device and <strong>create a ticket</strong>. You can track the status anytime.",
    soporte_titulo: "Support and tracking",
    soporte_desc: "If you have questions, check the help section or contact support from your dashboard.",
    gracias: "Thanks for trusting <strong>Innovasys</strong>! 💜"
  },
  pt: {
    guia_rapida: "Guia rápida",
    titulo: "Como usar o Innovasys",
    descripcion: "Siga estes passos para registrar seus dispositivos, criar tickets e acompanhar seus reparos.",
    registrarme: "Registrar-me",
    ya_cuenta: "Já tenho conta",
    registro_titulo: "Registro",
    registro_desc: "Crie sua conta em <strong>Registrar</strong> com seu nome, email e senha.",
    acceso_titulo: "Acesso",
    acceso_desc: "Faça login em <strong>Entrar</strong> para acessar seu painel.",
    panel_titulo: "Painel do usuário",
    panel_desc: "Gerencie seus <em>dispositivos</em> e verifique seus <em>tickets</em> em andamento.",
    reparacion_titulo: "Solicitar reparo",
    reparacion_desc: "Adicione seu dispositivo e <strong>crie um ticket</strong>. Você pode acompanhar o status a qualquer momento.",
    soporte_titulo: "Suporte e acompanhamento",
    soporte_desc: "Em caso de dúvidas, consulte a seção de ajuda ou entre em contato com o suporte através do seu painel.",
    gracias: "Obrigado por confiar no <strong>Innovasys</strong>! 💜"
  }
};
let currentLang = "es";
const langs = ["es", "en", "pt"];
let langIndex = 0;

langBtn.addEventListener("click", () => {
  langIndex = (langIndex + 1) % langs.length;
  currentLang = langs[langIndex];

  langBtn.textContent =
    currentLang === "es" ? "EN 🇬🇧" :
    currentLang === "en" ? "PT 🇧🇷" :
    "ES 🇪🇸";
  
  document.querySelectorAll("[data-lang]").forEach(el => {
    const key = el.getAttribute("data-lang");
    el.innerHTML = textos[currentLang][key];
  });
});
</script>

