-- ============================================================
-- Migracion 012 - Planes, modulos y feature gates comerciales
-- ============================================================
-- Crea la capa comercial para habilitar funcionalidades por
-- establecimiento sin ramificar codigo por cliente.
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `plan_catalog` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(64) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_plan_catalog_code` (`code`),
  KEY `idx_plan_catalog_status` (`status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `feature_catalog` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(64) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `scope` VARCHAR(32) NOT NULL DEFAULT 'shared',
  `is_premium` TINYINT(1) NOT NULL DEFAULT 1,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_feature_catalog_code` (`code`),
  KEY `idx_feature_catalog_status` (`status`, `scope`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `plan_feature_map` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `plan_id` INT NOT NULL,
  `feature_id` INT NOT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_plan_feature_map` (`plan_id`, `feature_id`),
  KEY `idx_plan_feature_plan` (`plan_id`),
  KEY `idx_plan_feature_feature` (`feature_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `establishment_subscription` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `establishment_id` INT NOT NULL,
  `plan_id` INT NOT NULL,
  `status` ENUM('trial','active','past_due','suspended','cancelled') NOT NULL DEFAULT 'trial',
  `billing_mode` ENUM('manual','subscription','fee','none') NOT NULL DEFAULT 'manual',
  `price_amount` DECIMAL(10,2) DEFAULT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'ARS',
  `starts_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ends_at` DATETIME DEFAULT NULL,
  `trial_ends_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_establishment_subscription_est` (`establishment_id`),
  KEY `idx_establishment_subscription_plan` (`plan_id`),
  KEY `idx_establishment_subscription_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `establishment_feature_override` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `establishment_id` INT NOT NULL,
  `feature_id` INT NOT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `reason` VARCHAR(255) DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL,
  `created_by_user_id` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_establishment_feature_override` (`establishment_id`, `feature_id`),
  KEY `idx_establishment_feature_override_est` (`establishment_id`),
  KEY `idx_establishment_feature_override_feature` (`feature_id`),
  KEY `idx_establishment_feature_override_exp` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `plan_catalog` (`code`, `name`, `description`, `status`, `sort_order`)
VALUES
  ('base_mvp', 'Plan Base', 'Incluye el MVP actual y la operacion esencial del establecimiento.', 1, 10),
  ('premium', 'Plan Premium', 'Incluye modulos avanzados de operacion, analitica y autoservicio.', 1, 20)
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `status` = VALUES(`status`),
  `sort_order` = VALUES(`sort_order`);

INSERT INTO `feature_catalog` (`code`, `name`, `description`, `scope`, `is_premium`, `status`, `sort_order`)
VALUES
  ('mod_finance', 'Caja y egresos', 'Gestion de caja, egresos y cierres operativos.', 'web', 1, 1, 10),
  ('mod_crm', 'CRM de clientes', 'Ficha consolidada del cliente, tags y notas internas.', 'web', 1, 1, 20),
  ('mod_waitlist', 'Lista de espera', 'Registro y reasignacion de vacantes liberadas.', 'shared', 1, 1, 30),
  ('mod_analytics', 'Dashboard gerencial', 'Metricas avanzadas de ocupacion, ingresos y conversion.', 'web', 1, 1, 40),
  ('mod_bot_advanced', 'Bot avanzado', 'Autoservicio extendido en WhatsApp para clientes.', 'bot', 1, 1, 50),
  ('mod_split_payments', 'Pagos compartidos', 'Pagos parciales coordinados entre varios jugadores.', 'shared', 1, 1, 60),
  ('mod_recurring_self_service', 'Reservas fijas self-service', 'Pausa, reactivacion y salto puntual desde cliente.', 'shared', 1, 1, 70)
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
  INNER JOIN `feature_catalog` f
          ON f.code IN (
              'mod_finance',
              'mod_crm',
              'mod_waitlist',
              'mod_analytics',
              'mod_bot_advanced',
              'mod_split_payments',
              'mod_recurring_self_service'
          )
 WHERE (p.code = 'premium')
    OR (p.code = 'base_mvp' AND f.code = 'mod_crm')
ON DUPLICATE KEY UPDATE
  `enabled` = VALUES(`enabled`);

INSERT INTO `establishment_subscription`
    (`establishment_id`, `plan_id`, `status`, `billing_mode`, `price_amount`, `currency`, `starts_at`, `created_at`, `updated_at`)
SELECT e.id,
       p.id,
       CASE
         WHEN COALESCE(e.subscription_status, 'trial') = 'cancelled' THEN 'cancelled'
         WHEN COALESCE(e.subscription_status, 'trial') = 'suspended' THEN 'suspended'
         WHEN COALESCE(e.subscription_status, 'trial') = 'past_due' THEN 'past_due'
         WHEN COALESCE(e.subscription_status, 'trial') = 'active' THEN 'active'
         ELSE 'trial'
       END,
       CASE
         WHEN COALESCE(e.plan_type, 'fee') = 'subscription' THEN 'subscription'
         WHEN COALESCE(e.plan_type, 'fee') = 'fee' THEN 'fee'
         ELSE 'manual'
       END,
       NULL,
       'ARS',
       COALESCE(e.created_at, NOW()),
       NOW(),
       NOW()
  FROM `establishment` e
  INNER JOIN `plan_catalog` p ON p.code = 'base_mvp'
 LEFT JOIN `establishment_subscription` es ON es.establishment_id = e.id
 WHERE es.id IS NULL;
