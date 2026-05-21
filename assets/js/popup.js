/* ============================================
   NOXARA - Popup / Modal Handlers
   ============================================ */

(function() {
  'use strict';

  /* Welcome Popup - show once per session */
  function initWelcomePopup() {
    var popup = document.querySelector('[data-welcome-popup]');
    if (!popup) return;

    var key = 'noxara_welcome_shown';
    if (sessionStorage.getItem(key)) return;

    setTimeout(function() {
      popup.classList.add('active');
      sessionStorage.setItem(key, '1');
    }, 1500);

    var closeBtn = popup.querySelector('[data-popup-close]');
    if (closeBtn) {
      closeBtn.addEventListener('click', function() {
        popup.classList.remove('active');
      });
    }

    popup.addEventListener('click', function(e) {
      if (e.target === popup) {
        popup.classList.remove('active');
      }
    });
  }

  /* Generic Modal Open/Close */
  function initModals() {
    /* Open modal triggers */
    document.addEventListener('click', function(e) {
      var openTrigger = e.target.closest('[data-modal-open]');
      if (openTrigger) {
        e.preventDefault();
        var targetId = openTrigger.getAttribute('data-modal-open');
        var modal = document.getElementById(targetId);
        if (modal) {
          modal.classList.add('active');
          document.body.style.overflow = 'hidden';
        }
      }

      /* Close modal triggers */
      var closeTrigger = e.target.closest('[data-modal-close]');
      if (closeTrigger) {
        var modal = closeTrigger.closest('.modal-overlay');
        if (modal) {
          modal.classList.remove('active');
          document.body.style.overflow = '';
        }
      }

      /* Close on overlay click */
      if (e.target.classList.contains('modal-overlay') && e.target.classList.contains('active')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
      }
    });

    /* Close on Escape key */
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        var activeModal = document.querySelector('.modal-overlay.active');
        if (activeModal) {
          activeModal.classList.remove('active');
          document.body.style.overflow = '';
        }
      }
    });
  }

  /* Initialize */
  document.addEventListener('DOMContentLoaded', function() {
    initWelcomePopup();
    initModals();
  });

})();
