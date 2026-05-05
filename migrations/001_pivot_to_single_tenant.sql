-- ============================================================
-- Migración 001 — Pivot de marketplace a single-tenant + reserva fija
-- ============================================================
-- Crea: establishment, recurring_booking, recurring_booking_pause,
--       notification_log, subscription
-- Modifica: booking (total/paid/balance/recurring), soccer_field (nueva relación)
-- NO ejecutar todavía. Revisar, validar contra datos reales (phpMyAdmin),
-- backup completo antes de aplicar.
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ============================================================
-- 1. ESTABLISHMENT — el negocio del canchero (1 por cuenta)
-- ============================================================
-- Antes: cada `soccer_field` actual era un establecimiento completo
-- (tenía phone, address, token_mercadopago). Ahora separamos:
--   establishment = el negocio   |   soccer_field = una cancha dentro
-- ============================================================
CREATE TABLE `establishment` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `owner_user_id` INT NOT NULL COMMENT 'FK a users (dueño/canchero)',
  `name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` VARCHAR(200) DEFAULT NULL,
  `latitude` VARCHAR(50) DEFAULT NULL,
  `longitude` VARCHAR(50) DEFAULT NULL,
  `tax_id` VARCHAR(20) DEFAULT NULL,
  `logo` VARCHAR(150) DEFAULT NULL,

  -- Integración WhatsApp Cloud API (Meta Embedded Signup)
  `whatsapp_phone_id` VARCHAR(50) DEFAULT NULL COMMENT 'Meta phone_number_id',
  `whatsapp_business_id` VARCHAR(50) DEFAULT NULL,
  `whatsapp_access_token` TEXT DEFAULT NULL COMMENT 'Token cifrado idealmente',
  `whatsapp_verify_token` VARCHAR(100) DEFAULT NULL,

  -- Integración MercadoPago (OAuth flow)
  `mp_user_id` VARCHAR(50) DEFAULT NULL,
  `mp_access_token` TEXT DEFAULT NULL,
  `mp_refresh_token` TEXT DEFAULT NULL,
  `mp_public_key` VARCHAR(100) DEFAULT NULL,
  `mp_token_expires_at` DATETIME DEFAULT NULL,

  -- Plan / billing
  `plan_type` ENUM('subscription','fee') NOT NULL DEFAULT 'fee',
  `fee_pct` DECIMAL(5,2) NOT NULL DEFAULT 5.00 COMMENT '% que cobra el bot por reserva (solo plan fee)',
  `subscription_status` ENUM('trial','active','past_due','suspended','cancelled') DEFAULT 'trial',

  -- Reserva fija (configuración del establecimiento)
  `recurring_setup_fee_amount` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Pago único del cliente al fijar un horario',

  `active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Suspende todas las operaciones del bot',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_owner` (`owner_user_id`),
  KEY `idx_whatsapp_phone` (`whatsapp_phone_id`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ============================================================
-- 2. SOCCER_FIELD — ALTER para apuntar a establishment
-- ============================================================
-- Mantener columnas viejas durante la migración. Borrarlas en migración 002
-- después de confirmar que toda la app web/PHP fue actualizada.
-- ============================================================
ALTER TABLE `soccer_field`
  ADD COLUMN `establishment_id` INT NULL AFTER `id`,
  ADD COLUMN `name` VARCHAR(100) NULL AFTER `full_name` COMMENT 'Nombre corto de la cancha (Cancha 1, Cancha Techada, etc.)',
  ADD COLUMN `price_per_hour` DECIMAL(10,2) DEFAULT NULL COMMENT 'Precio configurado por el canchero',
  ADD COLUMN `surface` VARCHAR(50) DEFAULT NULL COMMENT 'cesped, sintetico, cemento, etc',
  ADD COLUMN `players_capacity` INT DEFAULT NULL COMMENT 'F5, F7, F11',
  ADD KEY `idx_establishment` (`establishment_id`);

-- FK queda comentada porque establishment_id arranca NULL para datos legacy.
-- Aplicar después del backfill (sección 7).
-- ALTER TABLE `soccer_field`
--   ADD CONSTRAINT `fk_field_establishment`
--   FOREIGN KEY (`establishment_id`) REFERENCES `establishment`(`id`)
--   ON DELETE RESTRICT ON UPDATE CASCADE;


-- ============================================================
-- 3. BOOKING — agregar saldo / pago presencial / link a recurrente
-- ============================================================
ALTER TABLE `booking`
  ADD COLUMN `total_amount` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Precio total acordado',
  ADD COLUMN `deposit_amount` DECIMAL(10,2) DEFAULT NULL COMMENT 'Seña pagada online',
  ADD COLUMN `paid_amount` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Suma de TODOS los pagos (online + presencial)',
  ADD COLUMN `payment_status` ENUM('pending','partial','paid','refunded') DEFAULT 'pending',
  ADD COLUMN `paid_in_cash_at` DATETIME DEFAULT NULL,
  ADD COLUMN `paid_in_cash_by_user_id` INT DEFAULT NULL COMMENT 'Empleado que registró pago presencial',
  ADD COLUMN `recurring_booking_id` INT DEFAULT NULL COMMENT 'NULL = reserva suelta',
  ADD COLUMN `source` ENUM('one_off','recurring') DEFAULT 'one_off',
  ADD KEY `idx_recurring` (`recurring_booking_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_date_field` (`date_booking`, `id_field`);

-- balance_due se calcula en query como (total_amount - paid_amount).
-- No usamos columna generada para no obligar engine version >= 5.7 estricto.


-- ============================================================
-- 4. RECURRING_BOOKING — reserva fija
-- ============================================================
CREATE TABLE `recurring_booking` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `establishment_id` INT NOT NULL,
  `field_id` INT NOT NULL COMMENT 'FK a soccer_field',
  `customer_id` INT NOT NULL COMMENT 'FK a customers',

  `day_of_week` TINYINT NOT NULL COMMENT '1=Lunes ... 7=Domingo (ISO)',
  `start_time` TIME NOT NULL COMMENT 'Hora de inicio (HH:MM:SS)',
  `duration_min` INT NOT NULL DEFAULT 60,

  `valid_from` DATE NOT NULL,
  `valid_until` DATE DEFAULT NULL COMMENT 'NULL = indefinido',

  `setup_fee_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `setup_fee_payment_id` VARCHAR(100) DEFAULT NULL COMMENT 'ID del pago MP del setup fee',
  `setup_paid_at` DATETIME DEFAULT NULL,

  `status` ENUM('pending_payment','active','paused','cancelled') NOT NULL DEFAULT 'pending_payment',
  `created_by` ENUM('customer_bot','owner_web') NOT NULL,
  `cancelled_at` DATETIME DEFAULT NULL,
  `cancelled_reason` TEXT DEFAULT NULL,

  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_establishment_status` (`establishment_id`, `status`),
  KEY `idx_field_day_time` (`field_id`, `day_of_week`, `start_time`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_validity` (`valid_from`, `valid_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ============================================================
-- 5. RECURRING_BOOKING_PAUSE — pausas con aprobación canchero
-- ============================================================
CREATE TABLE `recurring_booking_pause` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `recurring_booking_id` INT NOT NULL,
  `from_date` DATE NOT NULL,
  `to_date` DATE NOT NULL,
  `reason` TEXT DEFAULT NULL,

  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `requested_by` ENUM('customer_bot','owner_web') NOT NULL,
  `requested_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by_user_id` INT DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `review_note` TEXT DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `idx_recurring_status` (`recurring_booking_id`, `status`),
  KEY `idx_dates` (`from_date`, `to_date`),
  CONSTRAINT `fk_pause_recurring` FOREIGN KEY (`recurring_booking_id`)
    REFERENCES `recurring_booking`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ============================================================
-- 6. NOTIFICATION_LOG — idempotencia del cron de notificaciones
-- ============================================================
-- Evita que reinicios del cron disparen duplicados o que el `diffHours === N`
-- exacto pierda la ventana.
-- ============================================================
CREATE TABLE `notification_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `booking_id` INT NOT NULL,
  `milestone` ENUM('24h','12h','6h','1h','post_match') NOT NULL,
  `sent_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_booking_milestone` (`booking_id`, `milestone`),
  KEY `idx_sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ============================================================
-- 7. SUBSCRIPTION — solo plan A (cobro al canchero)
-- ============================================================
CREATE TABLE `subscription` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `establishment_id` INT NOT NULL,
  `mp_preapproval_id` VARCHAR(100) NOT NULL COMMENT 'ID del preapproval MP que cobra al canchero',
  `plan_amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'ARS',
  `status` ENUM('authorized','paused','cancelled','past_due') NOT NULL DEFAULT 'authorized',
  `current_period_end` DATETIME DEFAULT NULL,
  `last_payment_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_establishment` (`establishment_id`),
  UNIQUE KEY `uniq_preapproval` (`mp_preapproval_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ============================================================
-- 8. BACKFILL — migrar datos legacy de soccer_field → establishment
-- ============================================================
-- Por cada soccer_field existente crear un establishment espejo.
-- Asume que `soccer_field.user` (o `users.id` por convención) identifica al dueño.
-- Revisar ANTES de ejecutar — la columna `user` en soccer_field NO existe en
-- el dump actual; hay que decidir mapeo dueño → establishment manualmente.
-- ============================================================

-- Plantilla (NO ejecutar tal cual hasta confirmar mapping owner_user_id):
--
-- INSERT INTO `establishment`
--   (id, owner_user_id, name, phone, address, latitude, longitude, tax_id, logo,
--    mp_access_token, plan_type, fee_pct, active, created_at)
-- SELECT
--    sf.id,                               -- mismo id que soccer_field para 1-a-1
--    1,                                   -- TODO: owner real (users.id)
--    sf.full_name,
--    sf.phone, sf.address, sf.latitude, sf.length,
--    sf.tax_id, sf.logo,
--    sf.token_mercadopago,
--    'fee', 5.00, sf.status, NOW()
-- FROM `soccer_field` sf;

-- Después backfill establishment_id en soccer_field:
-- UPDATE `soccer_field` SET `establishment_id` = `id`, `name` = CONCAT('Cancha ', `id`);

-- Y recién entonces aplicar la FK comentada en sección 2.


-- ============================================================
-- 9. PENDIENTE para migración 002 (después de validar y migrar app PHP):
-- ============================================================
-- ALTER TABLE `soccer_field` DROP COLUMN `phone`;
-- ALTER TABLE `soccer_field` DROP COLUMN `address`;
-- ALTER TABLE `soccer_field` DROP COLUMN `latitude`;
-- ALTER TABLE `soccer_field` DROP COLUMN `length`;
-- ALTER TABLE `soccer_field` DROP COLUMN `tax_id`;
-- ALTER TABLE `soccer_field` DROP COLUMN `token_mercadopago`;
-- ALTER TABLE `soccer_field` DROP COLUMN `id_province`;
-- ALTER TABLE `soccer_field` DROP COLUMN `id_city`;
-- DROP TABLE `province`;
-- DROP TABLE `city`;
