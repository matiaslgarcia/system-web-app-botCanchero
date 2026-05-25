SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `customer_consent` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `customer_id` INT NOT NULL,
  `establishment_id` INT NOT NULL,
  `consent_type` ENUM('communications','waitlist') NOT NULL,
  `status` ENUM('granted','revoked','pending') NOT NULL DEFAULT 'pending',
  `source` VARCHAR(40) NOT NULL DEFAULT 'web_admin',
  `created_by_user_id` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer_consent_scope` (`customer_id`, `establishment_id`, `consent_type`, `created_at`),
  KEY `idx_customer_consent_est_type` (`establishment_id`, `consent_type`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
