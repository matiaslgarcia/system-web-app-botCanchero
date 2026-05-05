-- Migración 004 — Idempotencia de webhook MercadoPago + auditoría base.
-- Aplicar antes del próximo deploy del bot.

CREATE TABLE IF NOT EXISTS mp_webhook_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    notification_id VARCHAR(64) NOT NULL,
    data_id VARCHAR(64) DEFAULT NULL,
    type VARCHAR(32) DEFAULT NULL,
    processed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_notif (notification_id),
    KEY idx_data (data_id),
    KEY idx_processed (processed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla `booking_logs` referenciada por Payment::registerCash pero faltaba migración.
CREATE TABLE IF NOT EXISTS booking_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_booking BIGINT UNSIGNED NOT NULL,
    id_user BIGINT UNSIGNED DEFAULT NULL,
    action VARCHAR(64) NOT NULL,
    note TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_booking (id_booking),
    KEY idx_action (action),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Auditoría general (admin actions, security events). Opcional, sin índices pesados al inicio.
CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    actor_user_id BIGINT UNSIGNED DEFAULT NULL,
    actor_ip VARCHAR(45) DEFAULT NULL,
    action VARCHAR(64) NOT NULL,
    target_type VARCHAR(64) DEFAULT NULL,
    target_id VARCHAR(64) DEFAULT NULL,
    payload JSON DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_actor (actor_user_id),
    KEY idx_action_created (action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
