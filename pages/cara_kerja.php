<?php
/**
 * NOXARA - Cara Kerja (How It Works)
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Cara Kerja';
$show_back = true;

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <div class="section-header">
        <h2 class="section-title">Cara Kerja NOXARA</h2>
    </div>

    <div class="steps-container">
        <div class="step-card glassmorphism">
            <div class="step-number">1</div>
            <div class="step-content">
                <h4>Daftar & Verifikasi</h4>
                <p>Buat akun NOXARA dengan data yang valid. Verifikasi email dan nomor HP untuk keamanan akun.</p>
            </div>
        </div>

        <div class="step-card glassmorphism">
            <div class="step-number">2</div>
            <div class="step-content">
                <h4>Top Up Saldo</h4>
                <p>Isi saldo melalui berbagai metode pembayaran yang tersedia (QRIS, Transfer Bank, E-Wallet).</p>
            </div>
        </div>

        <div class="step-card glassmorphism">
            <div class="step-number">3</div>
            <div class="step-content">
                <h4>Beli Paket Mining</h4>
                <p>Pilih paket mining yang sesuai budget. Setiap paket memiliki durasi dan profit harian berbeda.</p>
            </div>
        </div>

        <div class="step-card glassmorphism">
            <div class="step-number">4</div>
            <div class="step-content">
                <h4>Mining Harian</h4>
                <p>Klik tombol "MINE NOW" setiap hari. Proses mining berjalan selama <?= MINING_DURATION_HOURS ?> jam. Setelah selesai, klaim profit.</p>
            </div>
        </div>

        <div class="step-card glassmorphism">
            <div class="step-number">5</div>
            <div class="step-content">
                <h4>Klaim Profit</h4>
                <p>Setelah mining selesai, klik "Klaim Profit" untuk menambahkan penghasilan ke saldo kamu.</p>
            </div>
        </div>

        <div class="step-card glassmorphism">
            <div class="step-number">6</div>
            <div class="step-content">
                <h4>Withdraw</h4>
                <p>Tarik saldo ke rekening bank atau e-wallet kapan saja. Proses cepat dan mudah.</p>
            </div>
        </div>

        <div class="step-card glassmorphism">
            <div class="step-number">7</div>
            <div class="step-content">
                <h4>Ajak Teman (Referral)</h4>
                <p>Dapatkan komisi <?= REFERRAL_L1_PERCENT ?>% (L1), <?= REFERRAL_L2_PERCENT ?>% (L2), dan <?= REFERRAL_L3_PERCENT ?>% (L3) dari mining downline kamu.</p>
            </div>
        </div>
    </div>

    <!-- FAQ -->
    <div class="glassmorphism faq-card">
        <h4>FAQ</h4>
        <div class="faq-item">
            <strong>Berapa lama durasi mining?</strong>
            <p><?= MINING_DURATION_HOURS ?> jam per sesi. Reset setiap hari pukul <?= MINING_RESET_HOUR ?> WIB.</p>
        </div>
        <div class="faq-item">
            <strong>Berapa durasi paket?</strong>
            <p>Setiap paket berlaku selama <?= PACKAGE_DURATION_DAYS ?> hari sejak pembelian.</p>
        </div>
        <div class="faq-item">
            <strong>Apakah profit dijamin setiap hari?</strong>
            <p>Profit didapat jika kamu melakukan mining dan klaim setiap hari sesuai jadwal.</p>
        </div>
    </div>
</section>

<?php include INCLUDES_PATH . '/footer.php'; ?>
