-- Fix for booking_logs schema mismatch
DROP TABLE IF EXISTS booking_logs;

CREATE TABLE booking_logs (
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

-- Recreate triggers with new schema
DROP TRIGGER IF EXISTS `new_booking`;
DELIMITER $$
CREATE TRIGGER `new_booking` AFTER INSERT ON `booking` FOR EACH ROW 
BEGIN
    INSERT INTO booking_logs(id_booking, id_user, action, note, created_at) 
    VALUES (NEW.id, NEW.user, 'crear', 'Reserva creada', NOW());
END
$$
DELIMITER ;

DROP TRIGGER IF EXISTS `update_reserva`;
DELIMITER $$
CREATE TRIGGER `update_reserva` AFTER UPDATE ON `booking` FOR EACH ROW 
BEGIN
    DECLARE v_action VARCHAR(64);
    IF NEW.status = 2 AND OLD.status != 2 THEN
        SET v_action = 'cancelar';
    ELSEIF NEW.date_booking != OLD.date_booking OR NEW.time_booking != OLD.time_booking THEN
        SET v_action = 'reagendar';
    ELSE
        SET v_action = 'actualizar';
    END IF;

    INSERT INTO booking_logs(id_booking, id_user, action, note, created_at)
    VALUES (NEW.id, NEW.user, v_action, 'Reserva actualizada', NOW());
END
$$
DELIMITER ;
