-- Onboarding de nuevo establecimiento (template manual)
-- Reemplazar tokens {{...}} antes de ejecutar.
-- Ejecutar en entorno de staging primero.

START TRANSACTION;

-- 1) Alta del establecimiento
INSERT INTO establishment (
    owner_user_id,
    name,
    phone,
    address,
    tax_id,
    plan_type,
    fee_pct,
    recurring_setup_fee_amount,
    active
) VALUES (
    {{OWNER_USER_ID}},
    '{{ESTABLISHMENT_NAME}}',
    '{{PHONE}}',
    '{{ADDRESS}}',
    '{{TAX_ID}}',
    '{{PLAN_TYPE}}', -- subscription | fee
    {{FEE_PCT}},     -- Ej: 5.00
    {{RECURRING_SETUP_FEE_AMOUNT}},
    1
);

SET @establishment_id = LAST_INSERT_ID();

-- 2) Alta de canchas (repetir este bloque por cancha)
INSERT INTO soccer_field (
    establishment_id,
    name,
    full_name,
    threshold,
    value_booking,
    status
) VALUES (
    @establishment_id,
    'Cancha 1',
    '{{ESTABLISHMENT_NAME}}',
    {{THRESHOLD_CANCHA_1}},
    {{PRECIO_CANCHA_1}},
    1
);

-- 3) Opcional: guardar datos de Meta en establishment si ya estan disponibles
-- UPDATE establishment
--    SET whatsapp_phone_id = '{{WHATSAPP_PHONE_ID}}',
--        whatsapp_business_id = '{{WHATSAPP_BUSINESS_ID}}',
--        whatsapp_access_token = '{{WHATSAPP_ACCESS_TOKEN}}',
--        whatsapp_verify_token = '{{WHATSAPP_VERIFY_TOKEN}}'
--  WHERE id = @establishment_id;

-- 4) Opcional: guardar datos de MercadoPago OAuth si ya estan disponibles
-- UPDATE establishment
--    SET mp_user_id = '{{MP_USER_ID}}',
--        mp_access_token = '{{MP_ACCESS_TOKEN}}',
--        mp_refresh_token = '{{MP_REFRESH_TOKEN}}',
--        mp_public_key = '{{MP_PUBLIC_KEY}}',
--        mp_token_expires_at = '{{MP_TOKEN_EXPIRES_AT}}'
--  WHERE id = @establishment_id;

COMMIT;

-- Verificacion
SELECT id, name, phone, active FROM establishment WHERE id = @establishment_id;
SELECT id, establishment_id, name, threshold, value_booking
  FROM soccer_field
 WHERE establishment_id = @establishment_id;
