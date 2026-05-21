/* ============================================
   NOXARA - Animation Helpers
   ============================================ */

(function() {
  'use strict';

  /* Counter Animate - tick up numbers on scroll */
  function animateCounter(el) {
    var target = parseInt(el.getAttribute('data-count'), 10) || 0;
    var duration = parseInt(el.getAttribute('data-duration'), 10) || 1500;
    var start = 0;
    var startTime = null;
    var prefix = el.getAttribute('data-prefix') || '';
    var suffix = el.getAttribute('data-suffix') || '';

    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      var progress = Math.min((timestamp - startTime) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 3);
      var current = Math.floor(eased * target);
      el.textContent = prefix + current.toLocaleString() + suffix;
      if (progress < 1) {
        requestAnimationFrame(step);
      } else {
        el.textContent = prefix + target.toLocaleString() + suffix;
      }
    }

    requestAnimationFrame(step);
  }

  /* Init counter on scroll into view */
  function initCounters() {
    var counters = document.querySelectorAll('[data-count]');
    if (counters.length === 0 || !('IntersectionObserver' in window)) return;

    var observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.3 });

    counters.forEach(function(el) { observer.observe(el); });
  }

  /* Scroll Reveal Init */
  function initScrollReveal() {
    var elements = document.querySelectorAll('.scroll-reveal');
    if (elements.length === 0 || !('IntersectionObserver' in window)) return;

    var observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });

    elements.forEach(function(el) { observer.observe(el); });
  }

  /* Stagger Animation on Card Grids */
  function initStaggerAnimation() {
    var grids = document.querySelectorAll('[data-stagger]');
    if (grids.length === 0 || !('IntersectionObserver' in window)) return;

    var observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          var children = entry.target.children;
          var delay = parseInt(entry.target.getAttribute('data-stagger'), 10) || 80;
          Array.prototype.forEach.call(children, function(child, index) {
            child.style.opacity = '0';
            child.style.transform = 'translateY(16px)';
            child.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            child.style.transitionDelay = (index * delay) + 'ms';
            setTimeout(function() {
              child.style.opacity = '1';
              child.style.transform = 'translateY(0)';
            }, 50);
          });
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    grids.forEach(function(grid) { observer.observe(grid); });
  }

  /* Initialize all */
  document.addEventListener('DOMContentLoaded', function() {
    initCounters();
    initScrollReveal();
    initStaggerAnimation();
  });

})();
