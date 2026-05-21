<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /pages/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOXARA - Invest Smarter, Grow Faster</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0A0E1A">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0A0E1A;
            color: #FFFFFF;
            overflow-x: hidden;
            min-height: 100vh;
        }
        a { text-decoration: none; color: inherit; }


        /* Navigation */
        .nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 20px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 100;
            backdrop-filter: blur(20px);
            background: rgba(10, 14, 26, 0.8);
            border-bottom: 1px solid rgba(0, 212, 255, 0.1);
        }
        .nav-logo {
            font-size: 24px;
            font-weight: 900;
            letter-spacing: 3px;
            background: linear-gradient(135deg, #00D4FF, #7B2FFF);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .nav-links { display: flex; gap: 15px; }
        .btn {
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-block;
        }
        .btn-outline {
            border: 1px solid rgba(0, 212, 255, 0.5);
            color: #00D4FF;
            background: transparent;
        }
        .btn-outline:hover { background: rgba(0, 212, 255, 0.1); }
        .btn-primary {
            background: linear-gradient(135deg, #00D4FF, #7B2FFF);
            color: #FFFFFF;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 212, 255, 0.3);
        }
        .btn-gold {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #0A0E1A;
            font-weight: 700;
        }


        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 120px 20px 80px;
            position: relative;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(123, 47, 255, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 50%, rgba(0, 212, 255, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        .hero-content { position: relative; z-index: 1; max-width: 800px; }
        .hero-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            background: rgba(255, 215, 0, 0.1);
            border: 1px solid rgba(255, 215, 0, 0.3);
            color: #FFD700;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 24px;
        }
        .hero h1 {
            font-size: clamp(40px, 8vw, 72px);
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #FFFFFF 0%, #00D4FF 50%, #7B2FFF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero p {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .hero-buttons { display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }
        .hero-buttons .btn { padding: 14px 32px; font-size: 16px; }


        /* Features Section */
        .features {
            padding: 100px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .section-title {
            text-align: center;
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 60px;
            background: linear-gradient(135deg, #00D4FF, #7B2FFF);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px;
        }
        .feature-card {
            background: linear-gradient(145deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 32px 24px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            border-color: rgba(0, 212, 255, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 28px;
        }
        .feature-icon-cyan { background: rgba(0, 212, 255, 0.15); }
        .feature-icon-purple { background: rgba(123, 47, 255, 0.15); }
        .feature-icon-gold { background: rgba(255, 215, 0, 0.15); }
        .feature-icon-green { background: rgba(0, 255, 136, 0.15); }
        .feature-card h3 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .feature-card p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.6;
        }


        /* Stats Section */
        .stats {
            padding: 80px 20px;
            background: linear-gradient(180deg, rgba(0,212,255,0.03) 0%, transparent 100%);
        }
        .stats-grid {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            text-align: center;
        }
        .stat-item h2 {
            font-size: 36px;
            font-weight: 900;
            background: linear-gradient(135deg, #00D4FF, #7B2FFF);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }
        .stat-item p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }

        /* CTA Section */
        .cta {
            padding: 100px 20px;
            text-align: center;
        }
        .cta-box {
            max-width: 600px;
            margin: 0 auto;
            padding: 60px 40px;
            background: linear-gradient(145deg, rgba(0,212,255,0.05), rgba(123,47,255,0.05));
            border: 1px solid rgba(0, 212, 255, 0.2);
            border-radius: 24px;
        }
        .cta-box h2 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 16px;
        }
        .cta-box p {
            color: rgba(255,255,255,0.7);
            margin-bottom: 30px;
        }

        /* Footer */
        .footer {
            padding: 40px 20px;
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.05);
            color: rgba(255,255,255,0.4);
            font-size: 13px;
        }

        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        @keyframes pulse-glow {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav { padding: 15px 20px; }
            .nav-logo { font-size: 20px; }
            .btn { padding: 8px 16px; font-size: 13px; }
            .hero { padding: 100px 20px 60px; }
            .hero-buttons .btn { padding: 12px 24px; font-size: 14px; }
            .section-title { font-size: 28px; }
        }
    </style>
</head>
<body>


    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-logo">NOXARA</div>
        <div class="nav-links">
            <a href="/auth/login.php" class="btn btn-outline">Masuk</a>
            <a href="/auth/register.php" class="btn btn-primary">Daftar</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <span class="hero-badge">Platform Investasi Digital #1</span>
            <h1>Invest Smarter, Grow Faster</h1>
            <p>Bergabunglah dengan ribuan investor cerdas yang telah memilih NOXARA sebagai platform investasi digital terpercaya mereka.</p>
            <div class="hero-buttons">
                <a href="/auth/register.php" class="btn btn-gold">Daftar Sekarang</a>
                <a href="/auth/login.php" class="btn btn-outline">Masuk</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <h2 class="section-title">Mengapa Memilih NOXARA?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon feature-icon-cyan">
                    <svg width="28" height="28" fill="none" stroke="#00D4FF" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                    </svg>
                </div>
                <h3>Cloud Mining</h3>
                <p>Tambang profit otomatis 24/7 tanpa ribet. Cukup aktifkan dan lihat saldo bertambah.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon feature-icon-purple">
                    <svg width="28" height="28" fill="none" stroke="#7B2FFF" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3>Referral System</h3>
                <p>Ajak teman dan dapatkan komisi hingga 3 level kedalaman. Passive income tanpa batas.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon feature-icon-gold">
                    <svg width="28" height="28" fill="none" stroke="#FFD700" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <h3>Daily Bonus</h3>
                <p>Klaim bonus harian gratis setiap hari. Streak berturut-turut untuk bonus lebih besar.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon feature-icon-green">
                    <svg width="28" height="28" fill="none" stroke="#00FF88" stroke-width="2" viewBox="0 0 24 24">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                </div>
                <h3>VIP Membership</h3>
                <p>Upgrade ke VIP untuk profit multiplier, prioritas withdraw, dan akses fitur eksklusif.</p>
            </div>
        </div>
    </section>


    <!-- Stats Section -->
    <section class="stats">
        <div class="stats-grid">
            <div class="stat-item">
                <h2 id="stat-users">58,742</h2>
                <p>Total Member</p>
            </div>
            <div class="stat-item">
                <h2 id="stat-profit">Rp 12.8M</h2>
                <p>Profit Dibagikan</p>
            </div>
            <div class="stat-item">
                <h2 id="stat-withdraw">24,391</h2>
                <p>Withdraw Sukses</p>
            </div>
            <div class="stat-item">
                <h2 id="stat-uptime">99.9%</h2>
                <p>Server Uptime</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="cta-box">
            <h2>Siap Memulai Perjalanan Investasi?</h2>
            <p>Daftar sekarang dan mulai hasilkan profit dari hari pertama.</p>
            <a href="/auth/register.php" class="btn btn-gold" style="padding:16px 40px;font-size:16px;">Daftar Sekarang</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>Copyright 2024 NOXARA. All rights reserved.</p>
    </footer>

    <script>
        // Animated counter
        function animateCounter(id, target, prefix, suffix) {
            const el = document.getElementById(id);
            if (!el) return;
            let current = 0;
            const increment = target / 60;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                el.textContent = prefix + Math.floor(current).toLocaleString('id-ID') + suffix;
            }, 30);
        }

        // Intersection observer for stats animation
        const statsSection = document.querySelector('.stats');
        let statsAnimated = false;
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !statsAnimated) {
                    statsAnimated = true;
                    animateCounter('stat-users', 58742, '', '');
                    animateCounter('stat-withdraw', 24391, '', '');
                }
            });
        }, { threshold: 0.5 });
        if (statsSection) observer.observe(statsSection);

        // Register service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js');
        }
    </script>
</body>
</html>
