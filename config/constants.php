<?php
/**
 * NOXARA - Constants
 */

// VIP Levels
define('VIP_0', 0);
define('VIP_1', 1);
define('VIP_2', 2);
define('VIP_3', 3);

// Mining
define('MINING_DURATION_HOURS', 2);
define('MINING_DURATION_SECONDS', 7200);
define('MINING_RESET_HOUR', '00:01');
define('PACKAGE_DURATION_DAYS', 30);

// Referral Commission %
define('REFERRAL_L1_PERCENT', 3.00);
define('REFERRAL_L2_PERCENT', 1.50);
define('REFERRAL_L3_PERCENT', 0.75);

// Registration Bonus
define('REGISTRATION_BONUS', 10000);

// Topup Limits
define('MIN_TOPUP', 10000);
define('MAX_TOPUP', 50000000);

// Daily Bonus Limits
define('DAILY_BONUS_MAX', 2000);
define('FIRST_TOPUP_BONUS_PERCENT', 50);
define('FIRST_TOPUP_BONUS_MAX', 100000);

// Captcha
define('CAPTCHA_TOLERANCE', 8);
define('CAPTCHA_MIN_DURATION_MS', 300);
define('CAPTCHA_EXPIRE_SECONDS', 300);

// Upload Limits
define('MAX_AVATAR_SIZE', 2 * 1024 * 1024); // 2MB
define('MAX_BANNER_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
