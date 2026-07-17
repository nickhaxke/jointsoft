-- Migration: Create settings table
-- Database: jointasoft

CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT DEFAULT NULL,
    `setting_type` ENUM('string', 'number', 'boolean', 'json') NOT NULL DEFAULT 'string',
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('company_name', 'JointaSoft', 'string', 'Company name displayed in the application'),
('contribution_rate_approved', '3', 'number', 'Contribution rate when receipt is approved (%)'),
('contribution_rate_default', '10', 'number', 'Default contribution rate when no receipt or rejected (%)'),
('max_upload_size', '10', 'number', 'Maximum file upload size in MB'),
('currency', 'TZS', 'string', 'Currency symbol used in the application'),
('fiscal_year_start', '1', 'number', 'Fiscal year start month (1-12)');
