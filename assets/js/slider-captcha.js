/**
 * NOXARA - Slider Captcha (FIXED)
 * 
 * Fixes from previous version:
 * 1. Uses percentage-based positioning for cross-device consistency
 * 2. Accounts for actual rendered track width vs server track width
 * 3. Touch events properly handled (no accidental scrolling)
 * 4. Tolerance increased for better UX on mobile
 * 5. Proper cleanup on success/failure
 */

(function() {
    'use strict';
    
    const API_URL = (document.querySelector('meta[name="base-url"]')?.content || '') + '/api/captcha.php';
    
    let state = {
        token: null,
        targetPercent: 0,
        trackWidth: 0,
        thumbWidth: 44,
        isDragging: false,
        startX: 0,
        currentX: 0,
        dragStartTime: 0,
        verified: false
    };
    
    const track = document.getElementById('captcha-track');
    const thumb = document.getElementById('captcha-thumb');
    const target = document.getElementById('captcha-target');
    const status = document.getElementById('captcha-status');
    const tokenInput = document.getElementById('captcha_token');
    const submitBtn = document.querySelector('[id^="btn-"]');
    
    if (!track || !thumb) return;
    
    // Initialize
    initCaptcha();
    
    async function initCaptcha() {
        try {
            const res = await fetch(API_URL + '?action=create', { credentials: 'same-origin' });
            const data = await res.json();
            
            if (data.success) {
                state.token = data.token;
                state.targetPercent = data.target_percent;
                state.thumbWidth = data.thumb_width || 44;
                
                // Calculate actual track dimensions after render
                requestAnimationFrame(() => {
                    state.trackWidth = track.offsetWidth;
                    
                    // Position target indicator
                    const maxDrag = state.trackWidth - state.thumbWidth;
                    const targetPx = (state.targetPercent / 100) * maxDrag;
                    target.style.left = (targetPx + state.thumbWidth / 2) + 'px';
                    target.style.display = 'block';
                    
                    if (tokenInput) tokenInput.value = state.token;
                });
            }
        } catch (e) {
            console.error('Captcha init error:', e);
            status.innerHTML = '<span class="text-error">Gagal memuat captcha. Muat ulang.</span>';
        }
    }
    
    // Mouse events
    thumb.addEventListener('mousedown', startDrag);
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', endDrag);
    
    // Touch events
    thumb.addEventListener('touchstart', startDrag, { passive: false });
    document.addEventListener('touchmove', onDrag, { passive: false });
    document.addEventListener('touchend', endDrag);
    
    function startDrag(e) {
        if (state.verified) return;
        e.preventDefault();
        
        state.isDragging = true;
        state.dragStartTime = Date.now();
        state.startX = getClientX(e);
        state.currentX = 0;
        
        thumb.classList.add('dragging');
        track.classList.add('active');
    }
    
    function onDrag(e) {
        if (!state.isDragging) return;
        e.preventDefault();
        
        const clientX = getClientX(e);
        let deltaX = clientX - state.startX;
        
        // Clamp within track bounds
        const maxDrag = state.trackWidth - state.thumbWidth;
        deltaX = Math.max(0, Math.min(deltaX, maxDrag));
        
        state.currentX = deltaX;
        thumb.style.transform = `translateX(${deltaX}px)`;
        
        // Visual fill
        const percent = (deltaX / maxDrag) * 100;
        track.style.setProperty('--fill-percent', percent + '%');
    }
    
    function endDrag(e) {
        if (!state.isDragging) return;
        state.isDragging = false;
        
        thumb.classList.remove('dragging');
        track.classList.remove('active');
        
        const duration = Date.now() - state.dragStartTime;
        
        // Verify position
        verifyCaptcha(state.currentX, duration);
    }
    
    async function verifyCaptcha(finalX, duration) {
        try {
            const res = await fetch(API_URL + '?action=verify', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    token: state.token,
                    final_x: finalX,
                    duration_ms: duration,
                    track_width: state.trackWidth
                })
            });
            
            const data = await res.json();
            
            if (data.valid) {
                // SUCCESS
                state.verified = true;
                track.classList.add('success');
                thumb.classList.add('success');
                status.innerHTML = '<span class="text-success">&#10003; Verifikasi berhasil!</span>';
                
                // Enable submit button
                if (submitBtn) submitBtn.disabled = false;
                
                // Haptic feedback
                if (navigator.vibrate) navigator.vibrate(20);
            } else {
                // FAILED - reset
                track.classList.add('error');
                status.innerHTML = '<span class="text-error">' + (data.message || 'Coba lagi') + '</span>';
                
                if (navigator.vibrate) navigator.vibrate([50, 30, 50]);
                
                setTimeout(() => {
                    resetCaptcha();
                    initCaptcha();
                }, 1000);
            }
        } catch (e) {
            console.error('Captcha verify error:', e);
            resetCaptcha();
            initCaptcha();
        }
    }
    
    function resetCaptcha() {
        state.verified = false;
        state.currentX = 0;
        thumb.style.transform = 'translateX(0)';
        thumb.classList.remove('success', 'dragging');
        track.classList.remove('success', 'error', 'active');
        track.style.setProperty('--fill-percent', '0%');
        status.innerHTML = '<span>Geser untuk verifikasi</span>';
        if (submitBtn) submitBtn.disabled = true;
    }
    
    function getClientX(e) {
        return e.touches ? e.touches[0].clientX : e.clientX;
    }
})();
