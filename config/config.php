<?php
/**
 * NOXARA - Configuration
 * Edit credentials sebelum deploy
 */

// Database
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'noxara_oke12');
define('DB_USER', 'noxara_Oke12');
define('DB_PASS', 'GANTI_PASSWORD_DATABASE');

// Site
define('BASE_URL', 'https://noxara.page');
define('SITE_NAME', 'NOXARA');
define('SITE_TAGLINE', 'Invest Smarter, Grow Faster');

// Security
define('SETUP_TOKEN', 'NOXARA_SETUP_GANTI_TOKEN_INI');
define('SESSION_LIFETIME', 7200);
define('CSRF_TOKEN_LIFETIME', 3600);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 30);
define('BCRYPT_COST', 10);

// Cashify QRIS
define('CASHIFY_API_KEY', 'cashify_261885e5c5f830e68f929de05e3bfdf72e118d859edc5419472f79a813eed3ea');
define('CASHIFY_QRIS_ID', '1b935c41-bf43-4075-8f57-56b6cbfa2d07');
define('CASHIFY_PACKAGE_IDS', '["com.orderkuota.app"]');
define('CASHIFY_BASE_URL', 'https://cashify.my.id/api/generate');
define('CASHIFY_EXPIRED_MINUTES', 15);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', __DIR__);
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');
define('BACKUPS_PATH', ROOT_PATH . '/backups');
