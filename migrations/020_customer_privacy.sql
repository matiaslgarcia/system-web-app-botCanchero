ALTER TABLE `customers`
  ADD COLUMN IF NOT EXISTS `privacy_status` ENUM('active','anonymized') NOT NULL DEFAULT 'active' AFTER `email`,
  ADD COLUMN IF NOT EXISTS `anonymized_at` DATETIME DEFAULT NULL AFTER `privacy_status`,
  ADD COLUMN IF NOT EXISTS `anonymized_by_user_id` INT DEFAULT NULL AFTER `anonymized_at`,
  ADD COLUMN IF NOT EXISTS `anonymization_reason` VARCHAR(255) DEFAULT NULL AFTER `anonymized_by_user_id`;

ALTER TABLE `customers`
  ADD INDEX `idx_customers_privacy_status` (`privacy_status`, `anonymized_at`);
