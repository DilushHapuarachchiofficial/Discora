USE `discora_db`;

CREATE TABLE IF NOT EXISTS `invoices` (
    `invoice_id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL UNIQUE,
    `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
    `generated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_invoices_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_notifications` (
    `notification_id` INT AUTO_INCREMENT PRIMARY KEY,
    `type` ENUM('New Order', 'New Message', 'System') NOT NULL DEFAULT 'New Order',
    `reference_id` INT NULL,
    `message` VARCHAR(255) NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
