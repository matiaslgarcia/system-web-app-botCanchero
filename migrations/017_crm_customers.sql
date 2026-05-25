SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `customer_tag` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `establishment_id` INT NOT NULL,
  `name` VARCHAR(80) NOT NULL,
  `color` VARCHAR(20) NOT NULL DEFAULT 'primary',
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by_user_id` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_customer_tag_est_name` (`establishment_id`, `name`),
  KEY `idx_customer_tag_est_status` (`establishment_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `customer_tag_map` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `customer_id` INT NOT NULL,
  `tag_id` INT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_customer_tag_map` (`customer_id`, `tag_id`),
  KEY `idx_customer_tag_map_customer` (`customer_id`),
  KEY `idx_customer_tag_map_tag` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `customer_note` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `establishment_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  `user_id` INT DEFAULT NULL,
  `note` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer_note_scope` (`establishment_id`, `customer_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `customer_tag` (`establishment_id`, `name`, `color`, `status`, `created_at`, `updated_at`)
SELECT e.id, 'Frecuente', 'success', 1, NOW(), NOW()
  FROM `establishment` e
ON DUPLICATE KEY UPDATE
  `color` = VALUES(`color`),
  `status` = VALUES(`status`),
  `updated_at` = VALUES(`updated_at`);

INSERT INTO `customer_tag` (`establishment_id`, `name`, `color`, `status`, `created_at`, `updated_at`)
SELECT e.id, 'Saldo pendiente', 'warning', 1, NOW(), NOW()
  FROM `establishment` e
ON DUPLICATE KEY UPDATE
  `color` = VALUES(`color`),
  `status` = VALUES(`status`),
  `updated_at` = VALUES(`updated_at`);

INSERT INTO `customer_tag` (`establishment_id`, `name`, `color`, `status`, `created_at`, `updated_at`)
SELECT e.id, 'Cancelaciones altas', 'danger', 1, NOW(), NOW()
  FROM `establishment` e
ON DUPLICATE KEY UPDATE
  `color` = VALUES(`color`),
  `status` = VALUES(`status`),
  `updated_at` = VALUES(`updated_at`);
