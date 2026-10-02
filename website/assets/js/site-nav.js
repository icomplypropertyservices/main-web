(function () {
  var header = document.querySelector('[data-site-header]');
  if (!header) return;

  var drawer = document.getElementById('mega-drawer');
  var burger = document.getElementById('nav-toggle');
  var items = header.querySelectorAll('[data-mega]');
  var desktopMq = window.matchMedia('(min-width: 1024px)');

  function closePanels(except) {
    items.forEach(function (item) {
      if (except && item === except) return;
      var btn = item.querySelector('.mega-trigger');
      var panel = item.querySelector('.mega-panel');
      item.classList.remove('is-open');
      if (btn) btn.setAttribute('aria-expanded', 'false');
      if (panel) panel.hidden = true;
    });
  }

  function openItem(item) {
    closePanels(item);
    var btn = item.querySelector('.mega-trigger');
    var panel = item.querySelector('.mega-panel');
    item.classList.add('is-open');
    if (btn) btn.setAttribute('aria-expanded', 'true');
    if (panel) panel.hidden = false;
  }

  function toggleItem(item) {
    if (item.classList.contains('is-open')) closePanels();
    else openItem(item);
  }

  function setDrawer(open) {
    if (!drawer || !burger) return;
    drawer.hidden = !open;
    drawer.classList.toggle('is-open', open);
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    burger.textContent = open ? 'Close' : 'Menu';
    document.body.classList.toggle('mega-drawer-open', open);
  }

  items.forEach(function (item) {
    var btn = item.querySelector('.mega-trigger');
    if (!btn) return;
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      toggleItem(item);
    });
    item.addEventListener('mouseenter', function () {
      if (desktopMq.matches && window.matchMedia('(hover: hover)').matches) openItem(item);
    });
    item.addEventListener('mouseleave', function () {
      if (desktopMq.matches && window.matchMedia('(hover: hover)').matches) closePanels();
    });
  });

  if (burger && drawer) {
    burger.addEventListener('click', function () {
      setDrawer(drawer.hidden);
    });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    closePanels();
    setDrawer(false);
  });

  document.addEventListener('click', function (e) {
    if (!header.contains(e.target)) closePanels();
  });

  desktopMq.addEventListener('change', function () {
    closePanels();
    setDrawer(false);
  });
})();
