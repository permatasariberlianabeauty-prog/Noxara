-- NOXARA Database Schema v2
-- Platform Mining Digital - Full Build
-- Engine: MySQL/MariaDB
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- =============================================
-- CORE TABLES
-- =============================================

CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` ENUM('superadmin','cs','finance') DEFAULT 'cs',
  `status` ENUM('active','blocked') DEFAULT 'active',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `pin` VARCHAR(255) DEFAULT NULL,
  `full_name` VARCHAR(100) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `referral_code` VARCHAR(20) NOT NULL UNIQUE,
  `referred_by` INT UNSIGNED DEFAULT NULL,
  `vip_level` TINYINT UNSIGNED DEFAULT 0,
  `total_topup` DECIMAL(15,2) DEFAULT 0.00,
  `status` ENUM('active','blocked','suspended') DEFAULT 'active',
  `birthday` DATE DEFAULT NULL,
  `theme_pref` ENUM('dark','light','auto') DEFAULT 'dark',
  `sound_enabled` TINYINT(1) DEFAULT 1,
  `auto_mine` TINYINT(1) DEFAULT 0,
  `last_checkin_date` DATE DEFAULT NULL,
  `current_streak` INT UNSIGNED DEFAULT 0,
  `mining_streak` INT UNSIGNED DEFAULT 0,
  `total_referral_pending` DECIMAL(15,2) DEFAULT 0.00,
  `two_fa_enabled` TINYINT(1) DEFAULT 0,
  `two_fa_secret` VARCHAR(255) DEFAULT NULL,
  `login_attempts` TINYINT UNSIGNED DEFAULT 0,
  `locked_until` DATETIME DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_referral_code` (`referral_code`),
  INDEX `idx_referred_by` (`referred_by`),
  INDEX `idx_vip_level` (`vip_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `user_wallets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL UNIQUE,
  `balance` DECIMAL(15,2) DEFAULT 0.00,
  `bonus_balance` DECIMAL(15,2) DEFAULT 0.00,
  `total_earned` DECIMAL(15,2) DEFAULT 0.00,
  `total_withdrawn` DECIMAL(15,2) DEFAULT 0.00,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `session_id` VARCHAR(128) NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT,
  `device_info` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_activity` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user_sessions` (`user_id`, `is_active`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_login_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `username` VARCHAR(50) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT,
  `status` ENUM('success','failed','blocked') DEFAULT 'failed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_login_logs` (`user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =============================================
-- VIP & BANK
-- =============================================

CREATE TABLE IF NOT EXISTS `vip_levels` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `level` TINYINT UNSIGNED NOT NULL UNIQUE,
  `name` VARCHAR(50) NOT NULL,
  `min_topup` DECIMAL(15,2) DEFAULT 0.00,
  `min_withdraw` DECIMAL(15,2) DEFAULT 0.00,
  `withdraw_fee_percent` DECIMAL(5,2) DEFAULT 0.00,
  `benefits` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `vip_codes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(30) NOT NULL UNIQUE,
  `target_vip_level` TINYINT UNSIGNED NOT NULL,
  `max_uses` INT UNSIGNED DEFAULT 1,
  `used_count` INT UNSIGNED DEFAULT 0,
  `expires_at` DATETIME DEFAULT NULL,
  `status` ENUM('active','expired','used') DEFAULT 'active',
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `bank_accounts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `bank_name` VARCHAR(50) NOT NULL,
  `account_number` VARCHAR(30) NOT NULL,
  `account_holder` VARCHAR(100) NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 0,
  `is_verified` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_bank_user` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =============================================
-- PRODUCTS & MINING
-- =============================================

CREATE TABLE IF NOT EXISTS `product_categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `description` TEXT,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `price` DECIMAL(15,2) NOT NULL,
  `daily_profit` DECIMAL(15,2) NOT NULL,
  `duration_days` INT UNSIGNED DEFAULT 30,
  `total_profit` DECIMAL(15,2) NOT NULL,
  `roi_percent` DECIMAL(5,2) NOT NULL,
  `min_vip` TINYINT UNSIGNED DEFAULT 0,
  `image` VARCHAR(255) DEFAULT NULL,
  `description` TEXT,
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_product_cat` (`category_id`),
  FOREIGN KEY (`category_id`) REFERENCES `product_categories`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `paid_amount` DECIMAL(15,2) NOT NULL,
  `paid_from_balance` DECIMAL(15,2) DEFAULT 0.00,
  `paid_from_bonus` DECIMAL(15,2) DEFAULT 0.00,
  `daily_profit` DECIMAL(15,2) NOT NULL,
  `duration_days` INT UNSIGNED DEFAULT 30,
  `days_claimed` INT UNSIGNED DEFAULT 0,
  `total_earned` DECIMAL(15,2) DEFAULT 0.00,
  `status` ENUM('active','completed','cancelled') DEFAULT 'active',
  `mining_state` ENUM('idle','mining','done','missed') DEFAULT 'idle',
  `mining_started_at` DATETIME DEFAULT NULL,
  `mining_finished_at` DATETIME DEFAULT NULL,
  `last_mining_date` DATE DEFAULT NULL,
  `started_at` DATETIME NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_products` (`user_id`, `status`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `mining_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `user_product_id` INT UNSIGNED NOT NULL,
  `profit_amount` DECIMAL(15,2) NOT NULL,
  `status` ENUM('mining','completed','missed') DEFAULT 'mining',
  `started_at` DATETIME DEFAULT NULL,
  `finished_at` DATETIME DEFAULT NULL,
  `mining_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_mining_user` (`user_id`, `mining_date`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_product_id`) REFERENCES `user_products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- TRANSACTIONS & LEDGER
-- =============================================

CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `type` ENUM('topup','withdraw','purchase','mining_profit','referral_commission','bonus','refund','capital_return','adjustment') NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `fee` DECIMAL(15,2) DEFAULT 0.00,
  `net_amount` DECIMAL(15,2) DEFAULT 0.00,
  `description` VARCHAR(255) DEFAULT NULL,
  `reference_id` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('pending','completed','failed','cancelled') DEFAULT 'completed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_tx_user` (`user_id`, `type`, `created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `transaction_ledger` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `wallet_type` ENUM('balance','bonus_balance') NOT NULL,
  `type` ENUM('credit','debit') NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `balance_before` DECIMAL(15,2) NOT NULL,
  `balance_after` DECIMAL(15,2) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `reference_type` VARCHAR(50) DEFAULT NULL,
  `reference_id` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_ledger_user` (`user_id`, `created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =============================================
-- TOPUP & WITHDRAWALS
-- =============================================

CREATE TABLE IF NOT EXISTS `topups` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `total_amount` DECIMAL(15,2) NOT NULL,
  `unique_nominal` INT DEFAULT 0,
  `transaction_id` VARCHAR(100) DEFAULT NULL,
  `qr_string` TEXT,
  `voucher_id` INT UNSIGNED DEFAULT NULL,
  `voucher_bonus` DECIMAL(15,2) DEFAULT 0.00,
  `status` ENUM('pending','paid','expired','cancelled') DEFAULT 'pending',
  `expires_at` DATETIME DEFAULT NULL,
  `paid_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_topup_user` (`user_id`, `status`),
  INDEX `idx_topup_txid` (`transaction_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `qris_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `api_key` VARCHAR(255) NOT NULL,
  `qris_id` VARCHAR(100) NOT NULL,
  `package_ids` JSON DEFAULT NULL,
  `expired_minutes` INT DEFAULT 15,
  `use_unique_code` TINYINT(1) DEFAULT 1,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `withdrawals` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `bank_account_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `fee` DECIMAL(15,2) DEFAULT 0.00,
  `net_amount` DECIMAL(15,2) NOT NULL,
  `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
  `admin_note` TEXT,
  `processed_by` INT UNSIGNED DEFAULT NULL,
  `processed_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_wd_user` (`user_id`, `status`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `wallet_adjustments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `admin_id` INT UNSIGNED NOT NULL,
  `wallet_type` ENUM('balance','bonus_balance') NOT NULL,
  `type` ENUM('credit','debit') NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `reason` TEXT,
  `count_as_topup` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =============================================
-- REFERRAL & COMMISSIONS
-- =============================================

CREATE TABLE IF NOT EXISTS `referrals` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `referred_id` INT UNSIGNED NOT NULL,
  `level` TINYINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_referral` (`user_id`, `referred_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`referred_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `commission_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `level` TINYINT UNSIGNED NOT NULL UNIQUE,
  `percentage` DECIMAL(5,2) NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `commissions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `from_user_id` INT UNSIGNED NOT NULL,
  `level` TINYINT UNSIGNED NOT NULL,
  `purchase_amount` DECIMAL(15,2) NOT NULL,
  `commission_amount` DECIMAL(15,2) NOT NULL,
  `user_product_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('paid','pending') DEFAULT 'paid',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_commission_user` (`user_id`, `created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`from_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- VOUCHERS
-- =============================================

CREATE TABLE IF NOT EXISTS `vouchers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(30) NOT NULL UNIQUE,
  `type` ENUM('product_discount','topup_bonus','cash_redeem') NOT NULL,
  `value_type` ENUM('percent','fixed') NOT NULL,
  `value` DECIMAL(15,2) NOT NULL,
  `min_vip` TINYINT UNSIGNED DEFAULT 0,
  `max_vip` TINYINT UNSIGNED DEFAULT 3,
  `max_uses` INT UNSIGNED DEFAULT 0,
  `used_count` INT UNSIGNED DEFAULT 0,
  `max_per_user` INT UNSIGNED DEFAULT 1,
  `min_purchase` DECIMAL(15,2) DEFAULT 0.00,
  `applicable_products` JSON DEFAULT NULL,
  `wallet_target` ENUM('balance','bonus_balance') DEFAULT 'balance',
  `expires_at` DATETIME DEFAULT NULL,
  `status` ENUM('active','inactive','expired') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_vouchers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `voucher_id` INT UNSIGNED NOT NULL,
  `used_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_uv` (`user_id`, `voucher_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =============================================
-- DAILY BONUS SYSTEM
-- =============================================

CREATE TABLE IF NOT EXISTS `daily_checkin_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `day_number` TINYINT UNSIGNED NOT NULL,
  `reward_amount` DECIMAL(10,2) NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `daily_checkins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `day_number` TINYINT UNSIGNED NOT NULL,
  `reward_amount` DECIMAL(10,2) NOT NULL,
  `checkin_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_checkin` (`user_id`, `checkin_date`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `spin_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `free_spins_per_day` INT UNSIGNED DEFAULT 1,
  `extra_spin_price` DECIMAL(10,2) DEFAULT 5000.00,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `spin_prizes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `label` VARCHAR(50) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `probability` DECIMAL(5,4) NOT NULL,
  `color` VARCHAR(10) DEFAULT '#00D4FF',
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `spin_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `prize_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `is_paid_spin` TINYINT(1) DEFAULT 0,
  `spin_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `quest_definitions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `quest_key` VARCHAR(50) NOT NULL UNIQUE,
  `reward_amount` DECIMAL(10,2) NOT NULL,
  `target_value` INT UNSIGNED DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_quest_progress` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `quest_id` INT UNSIGNED NOT NULL,
  `current_value` INT UNSIGNED DEFAULT 0,
  `is_completed` TINYINT(1) DEFAULT 0,
  `is_claimed` TINYINT(1) DEFAULT 0,
  `quest_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_quest` (`user_id`, `quest_id`, `quest_date`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`quest_id`) REFERENCES `quest_definitions`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `mystery_box_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `type` ENUM('free','paid_basic','paid_premium') NOT NULL,
  `price` DECIMAL(10,2) DEFAULT 0.00,
  `free_per_day` INT UNSIGNED DEFAULT 1,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mystery_box_prizes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `box_type` ENUM('free','paid_basic','paid_premium') NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `probability` DECIMAL(5,4) NOT NULL,
  `label` VARCHAR(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mystery_box_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `box_type` ENUM('free','paid_basic','paid_premium') NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `open_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `coin_hunt_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `duration_seconds` INT UNSIGNED DEFAULT 30,
  `coin_min_value` DECIMAL(10,2) DEFAULT 50.00,
  `coin_max_value` DECIMAL(10,2) DEFAULT 100.00,
  `daily_max_reward` DECIMAL(10,2) DEFAULT 2000.00,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `coin_hunt_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `coins_collected` INT UNSIGNED DEFAULT 0,
  `total_reward` DECIMAL(10,2) NOT NULL,
  `play_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `watch_videos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `video_url` VARCHAR(500) NOT NULL,
  `reward_amount` DECIMAL(10,2) NOT NULL,
  `duration_seconds` INT UNSIGNED DEFAULT 30,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `watch_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `video_id` INT UNSIGNED NOT NULL,
  `reward_amount` DECIMAL(10,2) NOT NULL,
  `watch_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_watch` (`user_id`, `video_id`, `watch_date`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `first_topup_claims` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL UNIQUE,
  `topup_amount` DECIMAL(15,2) NOT NULL,
  `bonus_amount` DECIMAL(15,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =============================================
-- ACHIEVEMENTS & LEADERBOARD
-- =============================================

CREATE TABLE IF NOT EXISTS `achievements` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `achievement_key` VARCHAR(50) NOT NULL UNIQUE,
  `icon` VARCHAR(50) DEFAULT 'trophy',
  `reward_amount` DECIMAL(10,2) DEFAULT 0.00,
  `criteria_type` VARCHAR(50) NOT NULL,
  `criteria_value` INT UNSIGNED DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_achievements` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `achievement_id` INT UNSIGNED NOT NULL,
  `is_claimed` TINYINT(1) DEFAULT 0,
  `unlocked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `claimed_at` DATETIME DEFAULT NULL,
  UNIQUE KEY `unique_ua` (`user_id`, `achievement_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`achievement_id`) REFERENCES `achievements`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `leaderboard_cache` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `type` ENUM('miner_daily','miner_weekly','miner_monthly','referrer_weekly','referrer_monthly','earner_alltime') NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `score` DECIMAL(15,2) DEFAULT 0.00,
  `rank_position` INT UNSIGNED DEFAULT 0,
  `period_key` VARCHAR(20) DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_lb` (`type`, `period_key`, `rank_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- LUCKY DRAW
-- =============================================

CREATE TABLE IF NOT EXISTS `lucky_draws` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `prize_1` DECIMAL(15,2) DEFAULT 500000.00,
  `prize_2` DECIMAL(15,2) DEFAULT 200000.00,
  `prize_3` DECIMAL(15,2) DEFAULT 100000.00,
  `extra_ticket_price` DECIMAL(10,2) DEFAULT 5000.00,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` ENUM('active','completed') DEFAULT 'active',
  `winner_1` INT UNSIGNED DEFAULT NULL,
  `winner_2` INT UNSIGNED DEFAULT NULL,
  `winner_3` INT UNSIGNED DEFAULT NULL,
  `drawn_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `lucky_draw_tickets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `lucky_draw_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `ticket_type` ENUM('auto','purchased') DEFAULT 'auto',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`lucky_draw_id`) REFERENCES `lucky_draws`(`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =============================================
-- CHAT & COMMUNITY
-- =============================================

CREATE TABLE IF NOT EXISTS `community_chat_messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `message` TEXT NOT NULL,
  `is_pinned` TINYINT(1) DEFAULT 0,
  `is_deleted` TINYINT(1) DEFAULT 0,
  `deleted_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_chat_time` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- NOTIFICATIONS
-- =============================================

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('system','topup','withdraw','mining','bonus','referral','vip','promo') DEFAULT 'system',
  `is_read` TINYINT(1) DEFAULT 0,
  `is_broadcast` TINYINT(1) DEFAULT 0,
  `link` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_notif_user` (`user_id`, `is_read`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- ACTIVITY FEED & BANNERS
-- =============================================

CREATE TABLE IF NOT EXISTS `activity_feed` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `message` VARCHAR(255) NOT NULL,
  `type` ENUM('topup','withdraw','mining','vip','purchase','referral','system') DEFAULT 'system',
  `is_fake` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_feed_time` (`created_at` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `banners` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) DEFAULT NULL,
  `image` VARCHAR(255) NOT NULL,
  `link` VARCHAR(500) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `marquee_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `content` TEXT NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `popup_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) DEFAULT 'Selamat Datang di NOXARA',
  `message` TEXT,
  `image` VARCHAR(255) DEFAULT NULL,
  `button_text` VARCHAR(50) DEFAULT 'Gabung Grup WhatsApp',
  `button_link` VARCHAR(500) DEFAULT NULL,
  `display_mode` ENUM('once_session','once_day','always') DEFAULT 'once_session',
  `is_active` TINYINT(1) DEFAULT 1,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =============================================
-- FLASH SALE, TEAM, APP, LEGAL
-- =============================================

CREATE TABLE IF NOT EXISTS `flash_sales` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `discount_percent` DECIMAL(5,2) NOT NULL,
  `start_at` DATETIME NOT NULL,
  `end_at` DATETIME NOT NULL,
  `max_purchases` INT UNSIGNED DEFAULT 0,
  `purchased_count` INT UNSIGNED DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `team_members` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `position` VARCHAR(100) NOT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `bio` TEXT,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `app_releases` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `version` VARCHAR(20) NOT NULL,
  `file_url` VARCHAR(500) DEFAULT '/uploads/app/noxara.apk',
  `file_size` VARCHAR(20) DEFAULT NULL,
  `min_android` VARCHAR(20) DEFAULT '5.0',
  `changelog` TEXT,
  `is_latest` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `legal_pages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `content` LONGTEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `contact_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `whatsapp` VARCHAR(20) DEFAULT NULL,
  `telegram` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `address` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- LOGS & MISC
-- =============================================

CREATE TABLE IF NOT EXISTS `admin_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT UNSIGNED NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `cron_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `cron_name` VARCHAR(50) NOT NULL,
  `status` ENUM('success','error') DEFAULT 'success',
  `message` TEXT,
  `records_affected` INT DEFAULT 0,
  `execution_time` DECIMAL(8,4) DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `token` VARCHAR(100) NOT NULL UNIQUE,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `backup_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `filename` VARCHAR(255) NOT NULL,
  `filesize` BIGINT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =============================================
-- SEED DATA
-- =============================================

-- Admin user (password: Admin@123456)
INSERT INTO `admin_users` (`username`, `password`, `full_name`, `role`) VALUES
('superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'superadmin');

-- VIP Levels
INSERT INTO `vip_levels` (`level`, `name`, `min_topup`, `min_withdraw`, `withdraw_fee_percent`, `benefits`) VALUES
(0, 'Member', 0.00, 100000.00, 10.00, 'Akses dasar platform'),
(1, 'VIP 1', 50000.00, 50000.00, 5.00, 'Fee lebih rendah, akses paket IRON IV-V'),
(2, 'VIP 2', 150000.00, 30000.00, 3.00, 'Fee minimal, akses paket GOLD I-III, VIP Lounge'),
(3, 'VIP 3 Diamond', 1000000.00, 0.00, 0.00, 'Tanpa fee, Auto-Mine, Priority WD, Early Access');

-- Commission Settings
INSERT INTO `commission_settings` (`level`, `percentage`) VALUES
(1, 3.00), (2, 1.50), (3, 0.75);

-- Product Categories
INSERT INTO `product_categories` (`name`, `slug`, `description`, `sort_order`) VALUES
('Mining Pemula', 'stone', 'Paket mining untuk pemula dengan modal terjangkau', 1),
('Mining Menengah', 'iron', 'Paket mining menengah dengan profit lebih besar', 2),
('Mining Premium', 'gold', 'Paket mining premium dengan ROI tertinggi', 3);

-- Products (15 products)
INSERT INTO `products` (`category_id`, `name`, `slug`, `price`, `daily_profit`, `duration_days`, `total_profit`, `roi_percent`, `min_vip`, `sort_order`) VALUES
(1, 'STONE I', 'stone-1', 50000, 2250, 30, 67500, 35, 0, 1),
(1, 'STONE II', 'stone-2', 150000, 7000, 30, 210000, 40, 0, 2),
(1, 'STONE III', 'stone-3', 300000, 14500, 30, 435000, 45, 0, 3),
(1, 'STONE IV', 'stone-4', 500000, 25000, 30, 750000, 50, 0, 4),
(1, 'STONE V', 'stone-5', 1000000, 53000, 30, 1590000, 59, 0, 5),
(2, 'IRON I', 'iron-1', 2500000, 137500, 30, 4125000, 65, 0, 6),
(2, 'IRON II', 'iron-2', 5000000, 283000, 30, 8490000, 70, 0, 7),
(2, 'IRON III', 'iron-3', 10000000, 583000, 30, 17490000, 75, 0, 8),
(2, 'IRON IV', 'iron-4', 20000000, 1200000, 30, 36000000, 80, 1, 9),
(2, 'IRON V', 'iron-5', 35000000, 2158000, 30, 64740000, 85, 1, 10),
(3, 'GOLD I', 'gold-1', 60000000, 3800000, 30, 114000000, 90, 2, 11),
(3, 'GOLD II', 'gold-2', 120000000, 7800000, 30, 234000000, 95, 2, 12),
(3, 'GOLD III', 'gold-3', 250000000, 16700000, 30, 501000000, 100, 2, 13),
(3, 'GOLD IV', 'gold-4', 500000000, 35000000, 30, 1050000000, 110, 3, 14),
(3, 'GOLD V', 'gold-5', 1000000000, 73300000, 30, 2199000000, 120, 3, 15);


-- Daily Check-in Settings (7 days)
INSERT INTO `daily_checkin_settings` (`day_number`, `reward_amount`) VALUES
(1, 100), (2, 200), (3, 300), (4, 500), (5, 750), (6, 1000), (7, 2000);

-- Spin Prizes
INSERT INTO `spin_settings` (`free_spins_per_day`, `extra_spin_price`) VALUES (1, 5000);
INSERT INTO `spin_prizes` (`label`, `amount`, `probability`, `color`, `sort_order`) VALUES
('Rp 100', 100, 0.3000, '#00D4FF', 1),
('Rp 200', 200, 0.2500, '#7B2FFF', 2),
('Rp 300', 300, 0.2000, '#00D4FF', 3),
('Rp 500', 500, 0.1200, '#FFD700', 4),
('Rp 1.000', 1000, 0.0800, '#7B2FFF', 5),
('Rp 2.000', 2000, 0.0500, '#FFD700', 6);

-- Quest Definitions
INSERT INTO `quest_definitions` (`title`, `description`, `quest_key`, `reward_amount`, `target_value`, `sort_order`) VALUES
('Login Hari Ini', 'Buka aplikasi hari ini', 'daily_login', 100, 1, 1),
('Klaim 1 Mining', 'Selesaikan 1 sesi mining', 'claim_mining', 200, 1, 2),
('Lihat Halaman Produk', 'Kunjungi halaman produk', 'view_products', 100, 1, 3),
('Share Referral', 'Bagikan link referral', 'share_referral', 500, 1, 4),
('Top Up Min Rp 50rb', 'Isi saldo minimal Rp 50.000', 'topup_50k', 2000, 1, 5);

-- Mystery Box Settings
INSERT INTO `mystery_box_settings` (`type`, `price`, `free_per_day`) VALUES
('free', 0, 1), ('paid_basic', 5000, 0), ('paid_premium', 25000, 0);

INSERT INTO `mystery_box_prizes` (`box_type`, `amount`, `probability`, `label`) VALUES
('free', 100, 0.4000, 'Rp 100'), ('free', 200, 0.3000, 'Rp 200'),
('free', 500, 0.2000, 'Rp 500'), ('free', 1000, 0.1000, 'Rp 1.000'),
('paid_basic', 200, 0.3000, 'Rp 200'), ('paid_basic', 500, 0.3000, 'Rp 500'),
('paid_basic', 1000, 0.2500, 'Rp 1.000'), ('paid_basic', 2000, 0.1500, 'Rp 2.000'),
('paid_premium', 1000, 0.3000, 'Rp 1.000'), ('paid_premium', 2000, 0.3000, 'Rp 2.000'),
('paid_premium', 5000, 0.2500, 'Rp 5.000'), ('paid_premium', 10000, 0.1500, 'Rp 10.000');

-- Coin Hunt Settings
INSERT INTO `coin_hunt_settings` (`duration_seconds`, `coin_min_value`, `coin_max_value`, `daily_max_reward`) VALUES
(30, 50, 100, 2000);

-- Achievements
INSERT INTO `achievements` (`title`, `description`, `achievement_key`, `icon`, `reward_amount`, `criteria_type`, `criteria_value`, `sort_order`) VALUES
('First Login', 'Login pertama kali', 'first_login', 'login', 100, 'login_count', 1, 1),
('First Purchase', 'Beli paket pertama', 'first_purchase', 'cart', 500, 'purchase_count', 1, 2),
('First Mining', 'Mining pertama kali', 'first_mining', 'pickaxe', 200, 'mining_count', 1, 3),
('Streak 7 Days', 'Login 7 hari berturut', 'streak_7', 'fire', 1000, 'login_streak', 7, 4),
('Streak 30 Days', 'Login 30 hari berturut', 'streak_30', 'fire', 2000, 'login_streak', 30, 5),
('Mining 30x', 'Selesaikan 30 sesi mining', 'mining_30', 'gem', 2000, 'mining_count', 30, 6),
('Mining Streak 7', 'Mining 7 hari berturut', 'mining_streak_7', 'bolt', 500, 'mining_streak', 7, 7),
('First Topup', 'Isi saldo pertama kali', 'first_topup', 'wallet', 500, 'topup_count', 1, 8),
('Refer 1 Downline', 'Ajak 1 member aktif', 'refer_1', 'users', 500, 'referral_active', 1, 9),
('Refer 10 Downlines', 'Ajak 10 member aktif', 'refer_10', 'users', 2000, 'referral_active', 10, 10),
('VIP 1', 'Naik ke VIP 1', 'vip_1', 'crown', 500, 'vip_level', 1, 11),
('VIP 2', 'Naik ke VIP 2', 'vip_2', 'crown', 1000, 'vip_level', 2, 12),
('VIP 3 Diamond', 'Naik ke VIP 3', 'vip_3', 'diamond', 2000, 'vip_level', 3, 13);


-- Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'NOXARA'),
('site_tagline', 'Invest Smarter, Grow Faster'),
('base_url', 'https://noxara.page'),
('min_topup', '10000'),
('max_topup', '50000000'),
('min_withdraw_global', '30000'),
('stats_member_count', '284751'),
('stats_total_withdraw', '15800000000'),
('stats_total_topup', '28400000000'),
('whatsapp_group', 'https://chat.whatsapp.com/example'),
('telegram_channel', 'https://t.me/noxara_official'),
('maintenance_mode', '0'),
('chat_min_vip', '0'),
('first_topup_bonus_percent', '50'),
('first_topup_bonus_max', '100000'),
('cashback_saturday_percent', '5'),
('cashback_saturday_max', '5000'),
('cashback_sunday_percent', '5'),
('cashback_sunday_max', '5000');

-- QRIS Settings (Cashify)
INSERT INTO `qris_settings` (`api_key`, `qris_id`, `package_ids`, `expired_minutes`, `use_unique_code`) VALUES
('cashify_261885e5c5f830e68f929de05e3bfdf72e118d859edc5419472f79a813eed3ea', '1b935c41-bf43-4075-8f57-56b6cbfa2d07', '["com.orderkuota.app"]', 15, 1);

-- Marquee
INSERT INTO `marquee_settings` (`content`, `is_active`) VALUES
('284.751+ Member Aktif ★ Total Withdraw Rp 15.8M+ ★ Platform Mining Digital #1 Indonesia', 1);

-- Popup
INSERT INTO `popup_settings` (`title`, `message`, `button_text`, `button_link`, `display_mode`, `is_active`) VALUES
('Selamat Datang di NOXARA', 'Bergabunglah dengan komunitas kami untuk info terbaru seputar mining dan bonus harian!', 'Gabung Grup WhatsApp', 'https://chat.whatsapp.com/example', 'once_session', 1);

-- Legal Pages (NO OJK disclaimers)
INSERT INTO `legal_pages` (`title`, `slug`, `content`) VALUES
('Syarat Umum Penggunaan', 'terms', '<h2>Syarat Umum Penggunaan NOXARA</h2><h3>1. Persyaratan Akun</h3><p>Pengguna wajib berusia minimal 18 tahun dan memberikan informasi yang valid saat pendaftaran.</p><h3>2. Tata Laku Pengguna</h3><p>Pengguna dilarang melakukan tindakan yang merugikan platform atau pengguna lain.</p><h3>3. Hak dan Kewajiban</h3><p>Platform berhak menangguhkan akun yang melanggar ketentuan. Pengguna berhak atas layanan sesuai paket yang dipilih.</p><h3>4. Penghentian Akun</h3><p>Akun dapat dihentikan atas permintaan pengguna atau karena pelanggaran ketentuan.</p><h3>5. Force Majeure</h3><p>Platform tidak bertanggung jawab atas gangguan layanan akibat keadaan di luar kendali.</p><h3>6. Hubungi Kami</h3><p>Untuk pertanyaan, hubungi tim support kami melalui WhatsApp atau Telegram.</p>'),
('Kebijakan Privasi', 'privacy', '<h2>Kebijakan Privasi NOXARA</h2><p>Kami menghargai privasi Anda. Data pribadi dikumpulkan hanya untuk keperluan operasional platform dan tidak dibagikan ke pihak ketiga tanpa persetujuan.</p>'),
('Tentang Kami', 'about', '<h2>Tentang NOXARA</h2><p>NOXARA adalah platform mining digital terdepan di Indonesia. Kami berkomitmen memberikan layanan investasi mining yang aman, transparan, dan menguntungkan.</p>');

-- Contact Settings
INSERT INTO `contact_settings` (`whatsapp`, `telegram`, `email`) VALUES
('6281234567890', '@noxara_support', 'support@noxara.page');

SET FOREIGN_KEY_CHECKS = 1;
