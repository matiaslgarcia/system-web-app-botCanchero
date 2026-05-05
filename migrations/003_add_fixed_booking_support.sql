-- Migración 003: Soporte para Reservas Fijas (Recurrentes)
-- Añade flag a bookings individuales y crea tabla maestra para fijos.

-- 1. Añadir columna is_fixed a la tabla de bookings
-- Esto permite identificar qué turnos puntuales pertenecen a una serie fija.
ALTER TABLE `booking` 
ADD COLUMN `is_fixed` TINYINT(1) DEFAULT 0 AFTER `status`;

-- 2. Crear tabla de reservas recurrentes (Maestra)
-- Aquí se guarda la configuración semanal del cliente.
CREATE TABLE IF NOT EXISTS `recurring_booking` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_customer` INT NOT NULL,
    `id_field` INT NOT NULL,
    `day_of_week` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME,
    `setup_fee_paid` DECIMAL(10,2) DEFAULT 0.00,
    `status` ENUM('Active', 'Paused', 'Cancelled') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_customer`) REFERENCES `customers`(`id`),
    FOREIGN KEY (`id_field`) REFERENCES `soccer_field`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabla para registrar pausas en las reservas fijas
CREATE TABLE IF NOT EXISTS `recurring_booking_pause` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_recurring` INT NOT NULL,
    `pause_date` DATE NOT NULL, -- Fecha específica que se pausa
    `reason` TEXT,
    `approved` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_recurring`) REFERENCES `recurring_booking`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
