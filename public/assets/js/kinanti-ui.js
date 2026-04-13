// =============================================================
// ⚙️ KINANTI-UI.JS - Basic UI Interactions
// Version: 2.0
// =============================================================

// Sidebar toggle
document.addEventListener('DOMContentLoaded', () => {
  const burger = document.getElementById('menuToggle');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');

  if (burger && sidebar) {
    burger.addEventListener('click', e => {
      e.stopPropagation();
      sidebar.classList.toggle('open');
      if (overlay) overlay.classList.toggle('active');
    });

    document.addEventListener('click', e => {
      if (!sidebar.contains(e.target) && !burger.contains(e.target)) {
        sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('active');
      }
    });
  }
});

// Toast Notification
function showToast(message, duration = 3000) {
  let toast = document.getElementById('toastBox');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'toastBox';
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.className = 'show';
  setTimeout(() => toast.classList.remove('show'), duration);
}