SET NAMES utf8mb4;

-- OP-02: "Meta Diaria" en Mis Ingresos estaba hardcodeada en $100.000 sin
-- forma de configurarla, así que para cualquier establecimiento con un
-- volumen distinto la barra quedaba pegada cerca de 0% sin que signifique nada.
ALTER TABLE `establishment`
  ADD COLUMN `daily_revenue_goal` DECIMAL(10,2) NULL DEFAULT NULL AFTER `recurring_setup_fee_amount`;
