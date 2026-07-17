-- Migration: Create sales table
-- Database: jointasoft

CREATE TABLE IF NOT EXISTS `sales` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `sale_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `purchase_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `contribution_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('pending', 'approved', 'rejected', 'missing_receipt') NOT NULL DEFAULT 'missing_receipt',
    `notes` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_sales_status` (`status`),
    INDEX `idx_sales_user_id` (`user_id`),
    INDEX `idx_sales_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
