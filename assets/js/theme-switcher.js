/* ============================================
   NOXARA - Theme Switcher
   ============================================ */

(function() {
  'use strict';

  var THEME_DARK = 'theme-dark';
  var THEME_LIGHT = 'theme-light';
  var STORAGE_KEY = 'noxara_theme';

  function getCurrentTheme() {
    if (document.body.classList.contains(THEME_LIGHT)) return THEME_LIGHT;
    return THEME_DARK;
  }

  function applyTheme(theme) {
    document.body.classList.remove(THEME_DARK, THEME_LIGHT);
    document.body.classList.add(theme);

    var metaTheme = document.querySelector('meta[name="theme-color"]');
    if (metaTheme) {
      metaTheme.setAttribute('content', theme === THEME_LIGHT ? '#F5F7FA' : '#0A0E1A');
    }
  }

  function saveThemePreference(theme) {
    localStorage.setItem(STORAGE_KEY, theme);

    /* Save to server via API */
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/api/wallet.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('action=update_theme&theme=' + encodeURIComponent(theme));
  }

  function toggleTheme() {
    var current = getCurrentTheme();
    var next = current === THEME_DARK ? THEME_LIGHT : THEME_DARK;
    applyTheme(next);
    saveThemePreference(next);
    return next;
  }

  /* Initialize from localStorage or body class */
  function initTheme() {
    var saved = localStorage.getItem(STORAGE_KEY);
    if (saved && (saved === THEME_DARK || saved === THEME_LIGHT)) {
      applyTheme(saved);
    }
  }

  /* Bind toggle buttons */
  document.addEventListener('click', function(e) {
    var trigger = e.target.closest('[data-theme-toggle]');
    if (trigger) {
      e.preventDefault();
      var newTheme = toggleTheme();
      /* Update icon if present */
      var icon = trigger.querySelector('.theme-icon');
      if (icon) {
        icon.textContent = newTheme === THEME_DARK ? 'dark_mode' : 'light_mode';
      }
    }
  });

  /* Expose globally */
  window.NoxaraTheme = {
    toggle: toggleTheme,
    get: getCurrentTheme,
    set: function(theme) {
      applyTheme(theme);
      saveThemePreference(theme);
    }
  };

  /* Init */
  initTheme();

})();
