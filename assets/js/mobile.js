/* ============================================
   NOXARA - Mobile Specific
   ============================================ */

(function() {
  'use strict';

  /* Pull to Refresh */
  var pullStart = 0;
  var pulling = false;
  var pullThreshold = 80;
  var pullIndicator = null;

  function createPullIndicator() {
    pullIndicator = document.createElement('div');
    pullIndicator.style.cssText = 'position:fixed;top:0;left:50%;transform:translateX(-50%);' +
      'width:40px;height:40px;border-radius:50%;background:rgba(15,22,41,0.9);' +
      'border:2px solid rgba(0,212,255,0.3);display:flex;align-items:center;justify-content:center;' +
      'z-index:9999;opacity:0;transition:opacity 0.2s,top 0.2s;pointer-events:none;';
    pullIndicator.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00D4FF" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7"/></svg>';
    document.body.appendChild(pullIndicator);
  }

  document.addEventListener('touchstart', function(e) {
    if (window.scrollY === 0 && e.touches.length === 1) {
      pullStart = e.touches[0].clientY;
      pulling = true;
    }
  }, { passive: true });

  document.addEventListener('touchmove', function(e) {
    if (!pulling) return;
    var diff = e.touches[0].clientY - pullStart;
    if (diff > 10 && diff < 150) {
      if (!pullIndicator) createPullIndicator();
      pullIndicator.style.opacity = Math.min(diff / pullThreshold, 1).toString();
      pullIndicator.style.top = Math.min(diff * 0.4, 60) + 'px';
      if (diff > pullThreshold) {
        pullIndicator.style.borderColor = '#00D4FF';
      }
    }
  }, { passive: true });

  document.addEventListener('touchend', function() {
    if (!pulling) return;
    pulling = false;
    if (pullIndicator) {
      var opacity = parseFloat(pullIndicator.style.opacity);
      if (opacity >= 1) {
        window.location.reload();
      } else {
        pullIndicator.style.opacity = '0';
        pullIndicator.style.top = '0px';
      }
    }
  }, { passive: true });

  /* Swipe Detection */
  var swipeStartX = 0;
  var swipeStartY = 0;
  var swipeThreshold = 60;

  window.initSwipeDetection = function(element, callbacks) {
    if (!element) return;

    element.addEventListener('touchstart', function(e) {
      swipeStartX = e.touches[0].clientX;
      swipeStartY = e.touches[0].clientY;
    }, { passive: true });

    element.addEventListener('touchend', function(e) {
      var diffX = e.changedTouches[0].clientX - swipeStartX;
      var diffY = e.changedTouches[0].clientY - swipeStartY;

      if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > swipeThreshold) {
        if (diffX > 0 && callbacks.onSwipeRight) {
          callbacks.onSwipeRight();
        } else if (diffX < 0 && callbacks.onSwipeLeft) {
          callbacks.onSwipeLeft();
        }
      } else if (Math.abs(diffY) > swipeThreshold) {
        if (diffY > 0 && callbacks.onSwipeDown) {
          callbacks.onSwipeDown();
        } else if (diffY < 0 && callbacks.onSwipeUp) {
          callbacks.onSwipeUp();
        }
      }
    }, { passive: true });
  };

  /* Prevent bounce scroll on iOS */
  var scrollableEls = document.querySelectorAll('.scroll-x, .sidebar, .modal-content');
  document.body.addEventListener('touchmove', function(e) {
    var target = e.target;
    var isScrollable = false;
    while (target && target !== document.body) {
      if (target.classList && (
        target.classList.contains('scroll-x') ||
        target.classList.contains('sidebar') ||
        target.classList.contains('modal-content') ||
        target.scrollHeight > target.clientHeight
      )) {
        isScrollable = true;
        break;
      }
      target = target.parentNode;
    }
    if (!isScrollable && window.scrollY === 0) {
      /* Allow normal scroll, only prevent overscroll bounce */
    }
  }, { passive: true });

})();
