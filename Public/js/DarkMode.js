     
     
     document.addEventListener("DOMContentLoaded", function() {
        const body = document.body;
        const modo = localStorage.getItem("modo") || "claro";

        // Aplicar el modo al cargar
        if (modo === "oscuro") {
          body.classList.add("dark-mode");
        } else {
          body.classList.remove("dark-mode");
        }

        // Si existe el toggle en la página actual, sincronizarlo
        const toggle = document.getElementById("toggle-darkmode");
        if (toggle) {
          toggle.checked = (modo === "oscuro");
          toggle.addEventListener("change", () => {
            if (toggle.checked) {
              body.classList.add("dark-mode");
              localStorage.setItem("modo", "oscuro");
            } else {
              body.classList.remove("dark-mode");
              localStorage.setItem("modo", "claro");
            }
          });
        }
      });
