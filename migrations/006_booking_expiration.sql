-- ============================================================
-- Migración 006 — Booking Expiration & Pending Status
-- ============================================================

ALTER TABLE `booking`
  ADD COLUMN `expires_at` DATETIME NULL COMMENT 'Tiempo límite para completar el pago',
  ADD INDEX `idx_status_expires` (`status`, `expires_at`);

-- Actualizar listAvailable para filtrar expirados
-- (Esto se manejará mejor en el código PHP, pero el índice ayuda)
