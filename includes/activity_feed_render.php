<?php
/**
 * NOXARA - Activity Feed Render (Rotating)
 */
$feeds = get_activity_feed(15);
if (empty($feeds)) {
    $feeds = [
        ['message' => 'R***Y baru klaim Rp 850.000', 'type' => 'mining'],
        ['message' => 'N***A baru topup Rp 2.000.000', 'type' => 'topup'],
        ['message' => 'B***I naik VIP 2', 'type' => 'vip'],
        ['message' => 'S***N beli paket STONE III', 'type' => 'purchase'],
        ['message' => 'D***A withdraw Rp 1.500.000', 'type' => 'withdraw'],
    ];
}
?>
<section class="activity-feed scroll-reveal">
    <h3 class="section-title">Aktivitas Terkini</h3>
    <div class="feed-container" id="activity-feed">
        <?php foreach ($feeds as $i => $feed): ?>
        <div class="feed-item <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>">
            <span class="feed-dot type-<?= $feed['type'] ?? 'system' ?>"></span>
            <span class="feed-text"><?= sanitize($feed['message']) ?></span>
            <span class="feed-time"><?= isset($feed['created_at']) ? time_ago($feed['created_at']) : 'Baru saja' ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</section>
