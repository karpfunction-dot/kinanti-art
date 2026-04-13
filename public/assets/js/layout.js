// layout.js - toggles and small UI helpers
document.addEventListener('DOMContentLoaded', function() {
  const menuToggle = document.getElementById('menu-toggle');
  const sidebar = document.getElementById('sidebar');
  const userBtn = document.getElementById('user-btn');
  const userMenu = document.getElementById('user-menu');

  if (menuToggle && sidebar) {
    menuToggle.addEventListener('click', function(e){
      sidebar.classList.toggle('open');
    });
    // close sidebar when click outside (mobile)
    document.addEventListener('click', function(e){
      if (!sidebar.contains(e.target) && !menuToggle.contains(e.target) && window.innerWidth <= 768) {
        sidebar.classList.remove('open');
      }
    });
  }

  if (userBtn && userMenu) {
    userBtn.addEventListener('click', function(e){
      const expanded = userBtn.getAttribute('aria-expanded') === 'true';
      userBtn.setAttribute('aria-expanded', !expanded);
      userMenu.style.display = expanded ? 'none' : 'block';
      userMenu.setAttribute('aria-hidden', expanded);
    });
    // click outside to close user menu
    document.addEventListener('click', function(e){
      if (!userBtn.contains(e.target) && !userMenu.contains(e.target)) {
        userMenu.style.display = 'none';
        userBtn.setAttribute('aria-expanded', false);
        userMenu.setAttribute('aria-hidden', true);
      }
    });
  }

  // keyboard escape to close overlays
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') {
      if (sidebar) sidebar.classList.remove('open');
      if (userMenu) { userMenu.style.display = 'none'; userBtn.setAttribute('aria-expanded', false); }
    }
  });
});