ALTER TABLE `customer_privacy_scope`
  MODIFY COLUMN `status` ENUM('active','inactive','anonymized') NOT NULL DEFAULT 'active';
