/* ============================================
   NOXARA - Advanced Animations (Pro)
   ============================================ */

(function() {
  'use strict';

  /* Confetti Burst */
  window.triggerConfetti = function(options) {
    options = options || {};
    var count = options.count || 80;
    var duration = options.duration || 3000;
    var colors = options.colors || ['#00D4FF', '#7B2FFF', '#FFD700', '#10B981', '#EF4444', '#F59E0B'];

    var canvas = document.createElement('canvas');
    canvas.style.cssText = 'position:fixed;inset:0;width:100%;height:100%;pointer-events:none;z-index:99999;';
    document.body.appendChild(canvas);
    var ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    var particles = [];
    for (var i = 0; i < count; i++) {
      particles.push({
        x: canvas.width * 0.5 + (Math.random() - 0.5) * 200,
        y: canvas.height * 0.4,
        vx: (Math.random() - 0.5) * 12,
        vy: Math.random() * -14 - 4,
        color: colors[Math.floor(Math.random() * colors.length)],
        size: Math.random() * 8 + 4,
        rotation: Math.random() * 360,
        rotSpeed: (Math.random() - 0.5) * 10,
        gravity: 0.3 + Math.random() * 0.2,
        opacity: 1
      });
    }

    var startTime = Date.now();

    function animate() {
      var elapsed = Date.now() - startTime;
      if (elapsed > duration) {
        canvas.remove();
        return;
      }

      ctx.clearRect(0, 0, canvas.width, canvas.height);
      var fadeStart = duration * 0.7;

      particles.forEach(function(p) {
        p.x += p.vx;
        p.vy += p.gravity;
        p.y += p.vy;
        p.rotation += p.rotSpeed;
        p.vx *= 0.99;

        if (elapsed > fadeStart) {
          p.opacity = 1 - ((elapsed - fadeStart) / (duration - fadeStart));
        }

        ctx.save();
        ctx.translate(p.x, p.y);
        ctx.rotate(p.rotation * Math.PI / 180);
        ctx.globalAlpha = p.opacity;
        ctx.fillStyle = p.color;
        ctx.fillRect(-p.size / 2, -p.size / 4, p.size, p.size / 2);
        ctx.restore();
      });

      requestAnimationFrame(animate);
    }

    animate();
  };

  /* Coin Shower */
  window.triggerCoinShower = function(options) {
    options = options || {};
    var count = options.count || 30;
    var duration = options.duration || 3500;

    var canvas = document.createElement('canvas');
    canvas.style.cssText = 'position:fixed;inset:0;width:100%;height:100%;pointer-events:none;z-index:99999;';
    document.body.appendChild(canvas);
    var ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    var coins = [];
    for (var i = 0; i < count; i++) {
      coins.push({
        x: Math.random() * canvas.width,
        y: -40 - Math.random() * 200,
        vy: 2 + Math.random() * 3,
        vx: (Math.random() - 0.5) * 2,
        size: 16 + Math.random() * 12,
        rotation: Math.random() * 360,
        rotSpeed: 3 + Math.random() * 5,
        opacity: 1
      });
    }

    var startTime = Date.now();

    function animate() {
      var elapsed = Date.now() - startTime;
      if (elapsed > duration) {
        canvas.remove();
        return;
      }

      ctx.clearRect(0, 0, canvas.width, canvas.height);

      coins.forEach(function(c) {
        c.y += c.vy;
        c.x += c.vx;
        c.rotation += c.rotSpeed;

        if (elapsed > duration * 0.7) {
          c.opacity = 1 - ((elapsed - duration * 0.7) / (duration * 0.3));
        }

        ctx.save();
        ctx.translate(c.x, c.y);
        ctx.globalAlpha = c.opacity;

        var scaleX = Math.cos(c.rotation * Math.PI / 180);
        ctx.scale(scaleX, 1);

        ctx.beginPath();
        ctx.arc(0, 0, c.size / 2, 0, Math.PI * 2);
        ctx.fillStyle = '#FFD700';
        ctx.fill();
        ctx.strokeStyle = '#FF8C00';
        ctx.lineWidth = 2;
        ctx.stroke();

        ctx.fillStyle = '#FF8C00';
        ctx.font = 'bold ' + (c.size * 0.5) + 'px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('$', 0, 0);

        ctx.restore();
      });

      requestAnimationFrame(animate);
    }

    animate();
  };

  /* Particle Burst on Click */
  document.addEventListener('click', function(e) {
    var target = e.target.closest('[data-particle-burst]');
    if (!target) return;

    var rect = target.getBoundingClientRect();
    var x = rect.left + rect.width / 2;
    var y = rect.top + rect.height / 2;
    var color = target.getAttribute('data-particle-burst') || '#00D4FF';

    for (var i = 0; i < 12; i++) {
      var particle = document.createElement('div');
      var angle = (i / 12) * 360;
      var distance = 30 + Math.random() * 20;
      var dx = Math.cos(angle * Math.PI / 180) * distance;
      var dy = Math.sin(angle * Math.PI / 180) * distance;

      particle.style.cssText = 'position:fixed;width:6px;height:6px;border-radius:50%;pointer-events:none;z-index:99999;' +
        'left:' + x + 'px;top:' + y + 'px;background:' + color + ';' +
        'transition:all 0.5s cubic-bezier(0.25,0.46,0.45,0.94);opacity:1;';
      document.body.appendChild(particle);

      requestAnimationFrame(function() {
        particle.style.transform = 'translate(' + dx + 'px,' + dy + 'px) scale(0)';
        particle.style.opacity = '0';
      });

      setTimeout(function() { particle.remove(); }, 600);
    }
  });

})();
