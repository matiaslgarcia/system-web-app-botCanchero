-- Migración 005 — soporta multi-tenant API token + login throttle + booking expiration.
-- Aplicar después de 004. Idempotente.

-- ============================================================
-- api_token: bind a un establishment + flag active
-- (Auth::getEstablishmentId depende de estas dos columnas)
-- ============================================================
ALTER TABLE api_token
    ADD COLUMN IF NOT EXISTS establishment_id INT UNSIGNED NULL AFTER token,
    ADD COLUMN IF NOT EXISTS active TINYINT(1) NOT NULL DEFAULT 1 AFTER establishment_id,
    ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN IF NOT EXISTS revoked_at DATETIME NULL,
    ADD KEY IF NOT EXISTS idx_token_active (token, active),
    ADD KEY IF NOT EXISTS idx_token_est (establishment_id);

-- ============================================================
-- users: throttle de login (PHP-B5)
-- ============================================================
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS login_attempts INT NOT NULL DEFAULT 0 AFTER status,
    ADD COLUMN IF NOT EXISTS last_attempt DATETIME NULL AFTER login_attempts;

-- ============================================================
-- booking: expires_at usado para liberar slot tras N min sin pago
-- ============================================================
ALTER TABLE booking
    ADD COLUMN IF NOT EXISTS expires_at DATETIME NULL AFTER source,
    ADD KEY IF NOT EXISTS idx_booking_expires (expires_at);
