<?php
/**
 * NOXARA - Banner Slider Component
 */
$banners = db()->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
if (empty($banners)) return;
?>
<section class="banner-slider" id="banner-slider">
    <div class="slider-track">
        <?php foreach ($banners as $i => $banner): ?>
        <div class="slider-slide <?= $i === 0 ? 'active' : '' ?>">
            <a href="<?= $banner['link'] ? sanitize($banner['link']) : '#' ?>">
                <img src="<?= BASE_URL ?>/uploads/banners/<?= sanitize($banner['image']) ?>" alt="<?= sanitize($banner['title'] ?? 'Banner') ?>" loading="lazy">
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="slider-dots">
        <?php foreach ($banners as $i => $b): ?>
        <span class="dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>"></span>
        <?php endforeach; ?>
    </div>
</section>
