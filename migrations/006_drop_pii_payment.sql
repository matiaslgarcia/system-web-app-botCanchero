-- Migración 006 — sacar PII PCI-relevante de tabla `payment`.
-- BOT-A8 + PHP-B14: el bot dejó de enviar estos campos (set_payment_result_mp.js reescrito).
-- Aplicar después de validar que ningún consumer los lee.

ALTER TABLE payment
    DROP COLUMN IF EXISTS cardholder_identification_number,
    DROP COLUMN IF EXISTS cardholder_identification_type,
    DROP COLUMN IF EXISTS card_name,
    DROP COLUMN IF EXISTS first_six_digits,
    DROP COLUMN IF EXISTS last_four_digits,
    DROP COLUMN IF EXISTS expiration_month,
    DROP COLUMN IF EXISTS expiration_year,
    DROP COLUMN IF EXISTS ip_address;
