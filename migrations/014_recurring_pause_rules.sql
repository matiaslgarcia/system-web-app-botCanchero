-- ============================================================
-- Migracion 014 - Reglas y auditoria de pausa puntual de fija
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE `business_rules`
  ADD COLUMN `customer_pause_min_hours` INT NOT NULL DEFAULT 6
  AFTER `allow_customer_pause_request`;

UPDATE `business_rules`
   SET `customer_pause_min_hours` = `customer_cancel_min_hours`
 WHERE `customer_pause_min_hours` IS NULL
    OR `customer_pause_min_hours` = 6;

ALTER TABLE `recurring_booking_pause`
  ADD COLUMN `requested_min_hours` INT NOT NULL DEFAULT 0
  AFTER `requested_by`;
