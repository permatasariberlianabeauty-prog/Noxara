<?php
/**
 * NOXARA - Live Stats Counter (Fake auto-increment di frontend)
 */
$stat_members = (int)get_setting('stats_member_count', '284751');
$stat_wd = (float)get_setting('stats_total_withdraw', '15800000000');
$stat_topup = (float)get_setting('stats_total_topup', '28400000000');
?>
<section class="stats-live scroll-reveal">
    <h3 class="section-title">Platform Statistics</h3>
    <div class="stats-grid">
        <div class="stat-item">
            <span class="stat-number counter-animate" data-target="<?= $stat_members ?>" data-increment="3" data-interval="4000"><?= format_number($stat_members) ?>+</span>
            <span class="stat-label">Member Aktif</span>
        </div>
        <div class="stat-item">
            <span class="stat-number counter-animate" data-target="<?= $stat_wd ?>" data-increment="250000" data-interval="5000"><?= format_rupiah($stat_wd) ?>+</span>
            <span class="stat-label">Total Withdraw</span>
        </div>
        <div class="stat-item">
            <span class="stat-number counter-animate" data-target="<?= $stat_topup ?>" data-increment="400000" data-interval="5000"><?= format_rupiah($stat_topup) ?>+</span>
            <span class="stat-label">Total Top Up</span>
        </div>
    </div>
</section>
