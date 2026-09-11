-- ============================================================
-- Migracion 013 - Reglas operativas por establecimiento
-- ============================================================
-- Centraliza politicas de cancelacion, re-agenda, waitlist,
-- pagos compartidos y autoservicio para web y bot.
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `business_rules` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `establishment_id` INT NOT NULL,
  `allow_customer_self_service` TINYINT(1) NOT NULL DEFAULT 1,
  `allow_customer_view_balance` TINYINT(1) NOT NULL DEFAULT 1,
  `allow_customer_cancel` TINYINT(1) NOT NULL DEFAULT 1,
  `customer_cancel_min_hours` INT NOT NULL DEFAULT 6,
  `allow_customer_reschedule` TINYINT(1) NOT NULL DEFAULT 1,
  `customer_reschedule_min_hours` INT NOT NULL DEFAULT 6,
  `allow_customer_pause_request` TINYINT(1) NOT NULL DEFAULT 1,
  `pause_requires_approval` TINYINT(1) NOT NULL DEFAULT 1,
  `allow_customer_transfer` TINYINT(1) NOT NULL DEFAULT 0,
  `allow_waitlist` TINYINT(1) NOT NULL DEFAULT 0,
  `allow_shared_payments` TINYINT(1) NOT NULL DEFAULT 0,
  `refund_policy` ENUM('none','credit','manual','full') NOT NULL DEFAULT 'manual',
  `no_show_policy` ENUM('charge_full','charge_deposit','credit','manual') NOT NULL DEFAULT 'charge_full',
  `max_future_booking_days` INT NOT NULL DEFAULT 30,
  `hold_expiration_minutes` INT NOT NULL DEFAULT 15,
  `reminder_lead_minutes_csv` VARCHAR(120) NOT NULL DEFAULT '720,360',
  `public_policy_text` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_business_rules_establishment` (`establishment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `feature_catalog` (`code`, `name`, `description`, `scope`, `is_premium`, `status`, `sort_order`)
VALUES
  ('mod_operational_rules', 'Configuracion operativa', 'Politicas de autoservicio, cancelacion, re-agenda y cobro por establecimiento.', 'shared', 0, 1, 15)
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `scope` = VALUES(`scope`),
  `is_premium` = VALUES(`is_premium`),
  `status` = VALUES(`status`),
  `sort_order` = VALUES(`sort_order`);

INSERT INTO `plan_feature_map` (`plan_id`, `feature_id`, `enabled`)
SELECT p.id, f.id, 1
  FROM `plan_catalog` p
 INNER JOIN `feature_catalog` f ON f.code = 'mod_operational_rules'
 WHERE p.code IN ('base_mvp', 'premium')
ON DUPLICATE KEY UPDATE
  `enabled` = VALUES(`enabled`);

INSERT INTO `business_rules`
    (`establishment_id`, `allow_customer_self_service`, `allow_customer_view_balance`,
     `allow_customer_cancel`, `customer_cancel_min_hours`,
     `allow_customer_reschedule`, `customer_reschedule_min_hours`,
     `allow_customer_pause_request`, `pause_requires_approval`,
     `allow_customer_transfer`, `allow_waitlist`, `allow_shared_payments`,
     `refund_policy`, `no_show_policy`, `max_future_booking_days`,
     `hold_expiration_minutes`, `reminder_lead_minutes_csv`, `created_at`, `updated_at`)
SELECT e.id, 1, 1, 1, 6, 1, 6, 1, 1, 0, 0, 0, 'manual', 'charge_full', 30, 15, '720,360', NOW(), NOW()
  FROM `establishment` e
  LEFT JOIN `business_rules` br ON br.establishment_id = e.id
 WHERE br.id IS NULL;
