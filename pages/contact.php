<?php
/**
 * NOXARA - Contact
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Hubungi Kami';
$show_back = true;

// Get contact settings
$whatsapp = get_setting('contact_whatsapp', '');
$telegram = get_setting('contact_telegram', '');
$email = get_setting('contact_email', '');
$support_hours = get_setting('contact_support_hours', '09:00 - 21:00 WIB');

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <div class="section-header">
        <h2 class="section-title">Hubungi Kami</h2>
    </div>

    <div class="glassmorphism contact-card">
        <p class="contact-desc">Butuh bantuan? Hubungi tim support kami melalui channel berikut:</p>

        <div class="contact-channels">
            <?php if ($whatsapp): ?>
            <a href="https://wa.me/<?= sanitize($whatsapp) ?>" target="_blank" class="contact-item glassmorphism">
                <div class="contact-icon whatsapp">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492l4.634-1.215A11.95 11.95 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75c-2.115 0-4.142-.57-5.913-1.652l-.424-.253-2.75.721.735-2.686-.278-.44A9.72 9.72 0 012.25 12C2.25 6.624 6.624 2.25 12 2.25S21.75 6.624 21.75 12 17.376 21.75 12 21.75z"/></svg>
                </div>
                <div class="contact-detail">
                    <span class="channel-name">WhatsApp</span>
                    <span class="channel-value">+<?= sanitize($whatsapp) ?></span>
                </div>
            </a>
            <?php endif; ?>

            <?php if ($telegram): ?>
            <a href="https://t.me/<?= sanitize($telegram) ?>" target="_blank" class="contact-item glassmorphism">
                <div class="contact-icon telegram">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0h-.056zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.492-1.302.48-.428-.012-1.252-.242-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                </div>
                <div class="contact-detail">
                    <span class="channel-name">Telegram</span>
                    <span class="channel-value">@<?= sanitize($telegram) ?></span>
                </div>
            </a>
            <?php endif; ?>

            <?php if ($email): ?>
            <a href="mailto:<?= sanitize($email) ?>" class="contact-item glassmorphism">
                <div class="contact-icon email">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <div class="contact-detail">
                    <span class="channel-name">Email</span>
                    <span class="channel-value"><?= sanitize($email) ?></span>
                </div>
            </a>
            <?php endif; ?>
        </div>

        <div class="support-hours">
            <p>Jam operasional: <strong><?= sanitize($support_hours) ?></strong></p>
        </div>
    </div>
</section>

<?php include INCLUDES_PATH . '/footer.php'; ?>
