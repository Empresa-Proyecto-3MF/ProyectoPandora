const sidebar = document.querySelector(".sidebar");
const sidebarOpenBtn = document.querySelector("#sidebar-open");
const sidebarCloseBtn = document.querySelector("#sidebar-close");

const toggleSidebar = () => {
  if (window.innerWidth <= 800) {
    sidebar.classList.toggle("open");
  }
};

// Ocultar automáticamente si cambia el tamaño de pantalla
window.addEventListener("resize", () => {
  if (window.innerWidth > 800) {
    sidebar.classList.remove("open");
  }
});

sidebarOpenBtn?.addEventListener("click", toggleSidebar);
sidebarCloseBtn?.addEventListener("click", toggleSidebar);
