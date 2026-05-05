-- ============================================================
-- Migración 005 — Token Binding (Seguridad Multi-tenant)
-- ============================================================
-- Vincula cada token de API a un establecimiento específico.
-- Previene que un bot con un token vea datos de otro establecimiento.
-- ============================================================

ALTER TABLE `api_token`
  ADD COLUMN `establishment_id` INT NULL AFTER `token`,
  ADD COLUMN `name` VARCHAR(100) NULL AFTER `establishment_id` COMMENT 'Nombre del bot/cliente',
  ADD COLUMN `active` TINYINT(1) NOT NULL DEFAULT 1,
  ADD KEY `idx_establishment` (`establishment_id`);

-- Vincular el token actual al establecimiento 1 (suponiendo que es el principal)
-- Ajustar este ID si el establecimiento real tiene otro.
UPDATE `api_token` SET `establishment_id` = 1, `name` = 'Bot Oficial' WHERE `id` = 1;

-- En el futuro, establishment_id será NOT NULL.
