-- ============================================================
-- Migración 002 — Tabla `history` para persistir conversaciones del bot
-- ============================================================
-- builderbot guarda en esta tabla el contexto de cada interacción para
-- poder reanudar conversaciones después de un restart.
-- Antes el bot usaba MemoryDB (RAM), por lo que toda conversación a medias
-- se perdía al reiniciar. SQLAdapter persiste contra esta tabla.
--
-- Ejecutar en la misma base de datos que usa botCanchero (DB_DATABASE en .env).
-- ============================================================

CREATE TABLE IF NOT EXISTS `history` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ref` VARCHAR(255) DEFAULT NULL,
  `keyword` VARCHAR(255) DEFAULT NULL,
  `answer` TEXT DEFAULT NULL,
  `refSerialize` TEXT DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `options` LONGTEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_phone_id` (`phone`, `id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Nota: el índice (phone, id) acelera `getPrevByNumber` que hace
-- SELECT * FROM history WHERE phone = ? ORDER BY id DESC LIMIT 1.
--
-- Mantenimiento sugerido: limpiar registros viejos (>30 días) con cron mensual:
--   DELETE FROM history WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
