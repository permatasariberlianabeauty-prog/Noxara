/* ============================================
   NOXARA - Countdown Timer
   ============================================ */

(function() {
  'use strict';

  function padZero(n) {
    return n < 10 ? '0' + n : String(n);
  }

  function formatTime(seconds) {
    if (seconds <= 0) return '00:00:00';
    var h = Math.floor(seconds / 3600);
    var m = Math.floor((seconds % 3600) / 60);
    var s = seconds % 60;
    return padZero(h) + ':' + padZero(m) + ':' + padZero(s);
  }

  function initCountdowns() {
    var elements = document.querySelectorAll('[data-finish]');
    if (elements.length === 0) return;

    function update() {
      var now = Math.floor(Date.now() / 1000);

      elements.forEach(function(el) {
        var finish = parseInt(el.getAttribute('data-finish'), 10);
        var remaining = finish - now;

        if (remaining <= 0) {
          el.textContent = '00:00:00';
          el.classList.add('expired');
          var expiredText = el.getAttribute('data-expired-text');
          if (expiredText) {
            el.textContent = expiredText;
          }
        } else {
          el.textContent = formatTime(remaining);
          el.classList.remove('expired');
        }
      });
    }

    update();
    setInterval(update, 1000);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCountdowns);
  } else {
    initCountdowns();
  }

})();
