/* ============================================
   NOXARA - Core JavaScript
   ============================================ */

(function() {
  'use strict';

  /* Sidebar Toggle */
  const sidebar = document.querySelector('.sidebar');
  const sidebarOverlay = document.querySelector('.sidebar-overlay');
  const sidebarOpenBtn = document.querySelector('[data-sidebar-open]');
  const sidebarCloseBtn = document.querySelector('[data-sidebar-close]');

  function openSidebar() {
    if (!sidebar) return;
    sidebar.classList.add('active');
    if (sidebarOverlay) sidebarOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    if (!sidebar) return;
    sidebar.classList.remove('active');
    if (sidebarOverlay) sidebarOverlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  if (sidebarOpenBtn) sidebarOpenBtn.addEventListener('click', openSidebar);
  if (sidebarCloseBtn) sidebarCloseBtn.addEventListener('click', closeSidebar);
  if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);

  /* FAB Sheet Toggle */
  const fabBtn = document.querySelector('.fab-circle');
  const fabSheet = document.querySelector('.fab-sheet');
  const fabOverlay = document.querySelector('.fab-sheet-overlay');

  function toggleFabSheet() {
    if (!fabSheet) return;
    const isActive = fabSheet.classList.toggle('active');
    if (fabOverlay) {
      fabOverlay.classList.toggle('active', isActive);
    }
    if (isActive) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
  }

  function closeFabSheet() {
    if (!fabSheet) return;
    fabSheet.classList.remove('active');
    if (fabOverlay) fabOverlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  if (fabBtn) fabBtn.addEventListener('click', toggleFabSheet);
  if (fabOverlay) fabOverlay.addEventListener('click', closeFabSheet);

  /* Toast System */
  window.showToast = function(message, type) {
    type = type || 'info';
    var container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    var toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(function() {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(100%)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(function() { toast.remove(); }, 300);
    }, 3500);
  };

  /* Saldo Toggle */
  var saldoToggle = document.querySelector('[data-saldo-toggle]');
  var saldoAmount = document.querySelector('.saldo-amount');
  var saldoHidden = false;

  if (saldoToggle && saldoAmount) {
    var originalSaldo = saldoAmount.textContent;
    saldoToggle.addEventListener('click', function() {
      saldoHidden = !saldoHidden;
      if (saldoHidden) {
        saldoAmount.textContent = '********';
        saldoAmount.setAttribute('data-hidden', 'true');
      } else {
        saldoAmount.textContent = originalSaldo;
        saldoAmount.removeAttribute('data-hidden');
      }
    });
  }

  /* Scroll Reveal Observer */
  var revealElements = document.querySelectorAll('.scroll-reveal');
  if (revealElements.length > 0 && 'IntersectionObserver' in window) {
    var revealObserver = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          revealObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    revealElements.forEach(function(el) {
      revealObserver.observe(el);
    });
  }

  /* Banner Slider */
  var sliderTrack = document.querySelector('.slider-track');
  var slides = document.querySelectorAll('.slider-slide');
  var dots = document.querySelectorAll('.slider-dots .dot');
  var currentSlide = 0;
  var sliderInterval = null;
  var startX = 0;
  var isDragging = false;

  function goToSlide(index) {
    if (!sliderTrack || slides.length === 0) return;
    currentSlide = index;
    if (currentSlide >= slides.length) currentSlide = 0;
    if (currentSlide < 0) currentSlide = slides.length - 1;
    sliderTrack.style.transform = 'translateX(-' + (currentSlide * 100) + '%)';
    dots.forEach(function(dot, i) {
      dot.classList.toggle('active', i === currentSlide);
    });
  }

  function startSlider() {
    if (slides.length <= 1) return;
    sliderInterval = setInterval(function() {
      goToSlide(currentSlide + 1);
    }, 5000);
  }

  function stopSlider() {
    if (sliderInterval) clearInterval(sliderInterval);
  }

  dots.forEach(function(dot, i) {
    dot.addEventListener('click', function() {
      stopSlider();
      goToSlide(i);
      startSlider();
    });
  });

  if (sliderTrack) {
    sliderTrack.addEventListener('touchstart', function(e) {
      startX = e.touches[0].clientX;
      isDragging = true;
      stopSlider();
    }, { passive: true });

    sliderTrack.addEventListener('touchend', function(e) {
      if (!isDragging) return;
      var diff = startX - e.changedTouches[0].clientX;
      if (Math.abs(diff) > 50) {
        goToSlide(diff > 0 ? currentSlide + 1 : currentSlide - 1);
      }
      isDragging = false;
      startSlider();
    }, { passive: true });
  }

  startSlider();

  /* Activity Feed Rotation */
  var feedItems = document.querySelectorAll('.feed-item');
  var feedIndex = 0;

  function rotateFeed() {
    if (feedItems.length === 0) return;
    feedItems.forEach(function(item) { item.classList.remove('active'); });
    feedItems[feedIndex].classList.add('active');
    feedIndex = (feedIndex + 1) % feedItems.length;
  }

  if (feedItems.length > 0) {
    rotateFeed();
    setInterval(rotateFeed, 4500);
  }

  /* Haptic Feedback Helper */
  window.haptic = function(style) {
    if (navigator.vibrate) {
      switch (style) {
        case 'light': navigator.vibrate(10); break;
        case 'medium': navigator.vibrate(25); break;
        case 'heavy': navigator.vibrate(50); break;
        default: navigator.vibrate(10);
      }
    }
  };

  /* Mobile Nav Active State */
  var navItems = document.querySelectorAll('.bottom-nav .nav-item');
  var currentPath = window.location.pathname;

  navItems.forEach(function(item) {
    var href = item.getAttribute('href');
    if (href && currentPath.indexOf(href) !== -1) {
      item.classList.add('active');
    }
  });

})();
