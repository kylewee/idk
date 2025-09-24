-- Database schema for MechanicSaintAugustine.com
-- Creates the necessary tables for quote leads and logging

CREATE DATABASE IF NOT EXISTS `mechanic_sa` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `mechanic_sa`;

-- Table for storing quote leads
CREATE TABLE `quote_leads` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `phone` varchar(20) NOT NULL,
    `phone_clean` varchar(10) NOT NULL,
    `email` varchar(100) DEFAULT NULL,
    `vehicle_year` int(4) NOT NULL,
    `vehicle_make` varchar(50) NOT NULL,
    `vehicle_model` varchar(50) NOT NULL,
    `service_type` varchar(50) NOT NULL,
    `description` text DEFAULT NULL,
    `preferred_time` datetime NOT NULL,
    `sms_opt_in` tinyint(1) DEFAULT 0,
    `estimate_min` decimal(10,2) NOT NULL,
    `estimate_max` decimal(10,2) NOT NULL,
    `estimate_notes` text DEFAULT NULL,
    `status` enum('new','contacted','scheduled','completed','cancelled') DEFAULT 'new',
    `crm_id` varchar(50) DEFAULT NULL,
    `sms_sent` tinyint(1) DEFAULT 0,
    `sms_sid` varchar(100) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `ip_address` varchar(45) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_phone_clean` (`phone_clean`),
    KEY `idx_email` (`email`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_status` (`status`),
    KEY `idx_service_type` (`service_type`),
    KEY `idx_preferred_time` (`preferred_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for logging API calls and system events
CREATE TABLE `system_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `level` enum('info','warning','error','debug') DEFAULT 'info',
    `message` text NOT NULL,
    `context` json DEFAULT NULL,
    `lead_id` int(11) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_level` (`level`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_lead_id` (`lead_id`),
    FOREIGN KEY (`lead_id`) REFERENCES `quote_leads`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for tracking SMS messages
CREATE TABLE `sms_messages` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `lead_id` int(11) NOT NULL,
    `phone_number` varchar(15) NOT NULL,
    `message` text NOT NULL,
    `status` enum('pending','sent','delivered','failed') DEFAULT 'pending',
    `twilio_sid` varchar(100) DEFAULT NULL,
    `error_message` text DEFAULT NULL,
    `sent_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lead_id` (`lead_id`),
    KEY `idx_phone_number` (`phone_number`),
    KEY `idx_status` (`status`),
    KEY `idx_sent_at` (`sent_at`),
    FOREIGN KEY (`lead_id`) REFERENCES `quote_leads`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for CRM integration tracking
CREATE TABLE `crm_integrations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `lead_id` int(11) NOT NULL,
    `crm_type` varchar(50) NOT NULL DEFAULT 'rukovoditel',
    `crm_id` varchar(100) DEFAULT NULL,
    `status` enum('pending','success','failed') DEFAULT 'pending',
    `request_data` json DEFAULT NULL,
    `response_data` json DEFAULT NULL,
    `error_message` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lead_id` (`lead_id`),
    KEY `idx_crm_type` (`crm_type`),
    KEY `idx_status` (`status`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`lead_id`) REFERENCES `quote_leads`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample service types for reference
INSERT INTO `system_logs` (`level`, `message`, `context`) VALUES 
('info', 'Database schema initialized', '{"version": "1.0", "tables_created": ["quote_leads", "system_logs", "sms_messages", "crm_integrations"]}');

-- Create database user (run these commands as root)
-- CREATE USER 'mechanic_user'@'localhost' IDENTIFIED BY 'secure_password_here';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON mechanic_sa.* TO 'mechanic_user'@'localhost';
-- FLUSH PRIVILEGES;