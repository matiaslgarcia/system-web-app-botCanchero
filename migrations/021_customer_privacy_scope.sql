CREATE TABLE IF NOT EXISTS `customer_privacy_scope` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `customer_id` INT NOT NULL,
  `establishment_id` INT NOT NULL,
  `status` ENUM('active','anonymized') NOT NULL DEFAULT 'active',
  `reason` VARCHAR(255) DEFAULT NULL,
  `anonymized_at` DATETIME DEFAULT NULL,
  `anonymized_by_user_id` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_customer_privacy_scope` (`customer_id`, `establishment_id`),
  KEY `idx_customer_privacy_scope_status` (`establishment_id`, `status`, `anonymized_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
