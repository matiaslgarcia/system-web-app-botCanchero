-- Tabla para ingresos extras (no vinculados a reservas).
-- Permite auditar cobros adicionales por fecha y cancha.
CREATE TABLE IF NOT EXISTS extra_income (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    id_field         INT NOT NULL,
    date_income      DATE NOT NULL,
    description      VARCHAR(255) NOT NULL,
    amount           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    method_payment   ENUM('efectivo','transferencia','mercado_pago','otro') NOT NULL DEFAULT 'efectivo',
    created_by_user_id INT DEFAULT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_field_date (id_field, date_income)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
