-- Te Quiero Verde POS Database Setup
-- Run this in your XAMPP MySQL to create the database and tables

CREATE DATABASE IF NOT EXISTS `tqv` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tqv`;

-- Users table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `full_name` varchar(100) NOT NULL,
  `pin_code` varchar(4) NOT NULL,
  `role` enum('admin','mesera','inventario','super_user') NOT NULL DEFAULT 'mesera',
  `avatar_color` varchar(20) DEFAULT 'var(--p2)',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert the 4 users as specified
INSERT INTO `users` (`username`, `full_name`, `pin_code`, `role`, `avatar_color`) VALUES
('martina', 'Martina G.', '1234', 'mesera', 'var(--p2)'),
('alisson', 'Alisson M.', '2580', 'mesera', 'var(--p3)'),
('delfina', 'Delfina R.', '3691', 'mesera', 'var(--p5)'),
('jan', 'Jan (Admin)', '9999', 'admin', 'var(--p7)');

-- User sessions table for login tracking
CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `login_time` timestamp DEFAULT CURRENT_TIMESTAMP,
  `last_activity` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX (`session_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System configuration
CREATE TABLE `config` (
  `config_key` varchar(100) PRIMARY KEY,
  `config_value` text,
  `description` varchar(255),
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert basic configuration
INSERT INTO `config` (`config_key`, `config_value`, `description`) VALUES
('business_name', 'Te quiero verde', 'Restaurant name'),
('currency', 'MXN', 'Currency code'),
('timezone', 'America/Mexico_City', 'System timezone'),
('language', 'es', 'System language');
