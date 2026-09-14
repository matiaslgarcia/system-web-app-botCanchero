SET NAMES utf8mb4;

-- Item 11 (auditoria UX/UI, 13/09): el envio masivo de WhatsApp no dejaba
-- ningun registro de a quien le llego un mensaje, asi que si algo fallaba
-- a mitad de camino no habia forma de saber quien se quedo sin avisar.

CREATE TABLE IF NOT EXISTS `whatsapp_broadcast` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_field` INT DEFAULT NULL,
  `sent_by_user_id` INT DEFAULT NULL,
  `template_key` VARCHAR(60) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `total_recipients` INT NOT NULL DEFAULT 0,
  `total_sent` INT NOT NULL DEFAULT 0,
  `total_failed` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_wa_broadcast_field_date` (`id_field`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `whatsapp_broadcast_recipient` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `broadcast_id` INT NOT NULL,
  `customer_id` INT DEFAULT NULL,
  `full_name` VARCHAR(190) DEFAULT NULL,
  `phone` VARCHAR(40) NOT NULL,
  `status` ENUM('sent','failed') NOT NULL DEFAULT 'failed',
  `error` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_wa_recipient_broadcast` (`broadcast_id`),
  CONSTRAINT `fk_wa_recipient_broadcast` FOREIGN KEY (`broadcast_id`)
    REFERENCES `whatsapp_broadcast` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
