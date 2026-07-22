-- ==========================================================
-- NGO Donation and Volunteer Management System
-- Database Schema
-- DBMS: MySQL / MariaDB
-- Character Set: utf8mb4
-- ==========================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS `ngo_management` 
/*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `ngo_management`;

-- ==========================================================
-- 1. ROLES TABLE
-- ==========================================================
CREATE TABLE `roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_roles_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 2. USERS TABLE
-- ==========================================================
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_id` INT UNSIGNED NOT NULL,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `status` ENUM('active', 'inactive', 'banned') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_email` (`email`),
    KEY `idx_users_role_id` (`role_id`),
    CONSTRAINT `fk_users_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 3. NGO ADMINS TABLE
-- ==========================================================
CREATE TABLE `ngo_admins` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `department` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ngo_admins_user_id` (`user_id`),
    CONSTRAINT `fk_ngo_admins_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 4. VOLUNTEERS TABLE
-- ==========================================================
CREATE TABLE `volunteers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `date_of_birth` DATE NOT NULL,
    `address` TEXT DEFAULT NULL,
    `skills` TEXT DEFAULT NULL,
    `availability` VARCHAR(100) DEFAULT NULL,
    `joined_date` DATE NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_volunteers_user_id` (`user_id`),
    CONSTRAINT `fk_volunteers_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 5. DONORS TABLE
-- ==========================================================
CREATE TABLE `donors` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `donor_type` ENUM('individual', 'corporate') NOT NULL DEFAULT 'individual',
    `company_name` VARCHAR(100) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `total_donated` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_donors_user_id` (`user_id`),
    CONSTRAINT `fk_donors_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 6. EVENT COORDINATORS TABLE
-- ==========================================================
CREATE TABLE `event_coordinators` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `expertise_area` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_event_coordinators_user_id` (`user_id`),
    CONSTRAINT `fk_ec_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 7. CAMPAIGNS TABLE
-- ==========================================================
CREATE TABLE `campaigns` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ngo_admin_id` BIGINT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `goal_amount` DECIMAL(12,2) NOT NULL,
    `raised_amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `status` ENUM('draft', 'active', 'completed', 'cancelled') NOT NULL DEFAULT 'draft',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_campaigns_admin_id` (`ngo_admin_id`),
    KEY `idx_campaigns_status` (`status`),
    CONSTRAINT `fk_campaigns_admin_id` FOREIGN KEY (`ngo_admin_id`) REFERENCES `ngo_admins` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 8. EVENTS TABLE
-- ==========================================================
CREATE TABLE `events` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_id` BIGINT UNSIGNED NOT NULL,
    `coordinator_id` BIGINT UNSIGNED DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `location` VARCHAR(255) NOT NULL,
    `event_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `max_volunteers` INT NOT NULL DEFAULT '0',
    `status` ENUM('upcoming', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'upcoming',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_events_campaign_id` (`campaign_id`),
    KEY `idx_events_coordinator_id` (`coordinator_id`),
    KEY `idx_events_date` (`event_date`),
    CONSTRAINT `fk_events_campaign_id` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_events_coordinator_id` FOREIGN KEY (`coordinator_id`) REFERENCES `event_coordinators` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 9. EVENT REGISTRATIONS TABLE (Many-to-Many Events & Volunteers)
-- ==========================================================
CREATE TABLE `event_registrations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` BIGINT UNSIGNED NOT NULL,
    `volunteer_id` BIGINT UNSIGNED NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    `registered_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_registration_event_volunteer` (`event_id`, `volunteer_id`),
    KEY `idx_registration_volunteer_id` (`volunteer_id`),
    CONSTRAINT `fk_registration_event_id` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_registration_volunteer_id` FOREIGN KEY (`volunteer_id`) REFERENCES `volunteers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 10. TASKS TABLE
-- ==========================================================
CREATE TABLE `tasks` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` BIGINT UNSIGNED NOT NULL,
    `volunteer_id` BIGINT UNSIGNED NOT NULL,
    `assigned_by` BIGINT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `priority` ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    `status` ENUM('pending', 'in_progress', 'on_hold', 'completed') NOT NULL DEFAULT 'pending',
    `deadline` DATETIME NOT NULL,
    `estimated_hours` DECIMAL(5,2) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tasks_event_id` (`event_id`),
    KEY `idx_tasks_volunteer_id` (`volunteer_id`),
    KEY `idx_tasks_assigned_by` (`assigned_by`),
    CONSTRAINT `fk_tasks_event_id` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tasks_volunteer_id` FOREIGN KEY (`volunteer_id`) REFERENCES `volunteers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tasks_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 11. TASK UPDATES TABLE
-- ==========================================================
CREATE TABLE `task_updates` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `task_id` BIGINT UNSIGNED NOT NULL,
    `progress_percentage` TINYINT UNSIGNED NOT NULL DEFAULT '0',
    `hours_worked` DECIMAL(5,2) DEFAULT NULL,
    `remarks` TEXT NOT NULL,
    `update_date` DATE NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_task_updates_task_id` (`task_id`),
    CONSTRAINT `fk_task_updates_task_id` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 12. ATTENDANCE TABLE
-- ==========================================================
CREATE TABLE `attendance` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` BIGINT UNSIGNED NOT NULL,
    `volunteer_id` BIGINT UNSIGNED NOT NULL,
    `attendance_date` DATE NOT NULL,
    `check_in_time` TIME DEFAULT NULL,
    `check_out_time` TIME DEFAULT NULL,
    `status` ENUM('present', 'absent', 'late', 'excused') NOT NULL,
    `remarks` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_attendance_event_volunteer_date` (`event_id`, `volunteer_id`, `attendance_date`),
    KEY `idx_attendance_volunteer_id` (`volunteer_id`),
    CONSTRAINT `fk_attendance_event_id` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_attendance_volunteer_id` FOREIGN KEY (`volunteer_id`) REFERENCES `volunteers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 13. DONATIONS TABLE
-- ==========================================================
CREATE TABLE `donations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `donor_id` BIGINT UNSIGNED NOT NULL,
    `campaign_id` BIGINT UNSIGNED NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL,
    `currency` VARCHAR(3) NOT NULL DEFAULT 'USD',
    `donation_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    `message` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_donations_donor_id` (`donor_id`),
    KEY `idx_donations_campaign_id` (`campaign_id`),
    CONSTRAINT `fk_donations_donor_id` FOREIGN KEY (`donor_id`) REFERENCES `donors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_donations_campaign_id` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 14. PAYMENTS TABLE
-- ==========================================================
CREATE TABLE `payments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `donation_id` BIGINT UNSIGNED NOT NULL,
    `transaction_id` VARCHAR(100) NOT NULL,
    `payment_method` ENUM('credit_card', 'paypal', 'bank_transfer', 'crypto', 'other') NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL,
    `payment_status` ENUM('processing', 'success', 'failed') NOT NULL DEFAULT 'processing',
    `payment_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_payments_donation_id` (`donation_id`),
    UNIQUE KEY `uk_payments_transaction_id` (`transaction_id`),
    CONSTRAINT `fk_payments_donation_id` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 15. NOTIFICATIONS TABLE
-- ==========================================================
CREATE TABLE `notifications` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('info', 'warning', 'success', 'error') NOT NULL DEFAULT 'info',
    `is_read` BOOLEAN NOT NULL DEFAULT FALSE,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notifications_user_id` (`user_id`),
    KEY `idx_notifications_is_read` (`is_read`),
    CONSTRAINT `fk_notifications_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================================
-- DUMMY DATA INSERTION
-- ==========================================================

-- Insert Roles
INSERT INTO `roles` (`name`, `description`) VALUES
('Super Admin', 'Full system access'),
('NGO Admin', 'Manages campaigns and operations'),
('Volunteer', 'Participates in events and tasks'),
('Donor', 'Makes contributions to campaigns'),
('Event Coordinator', 'Manages specific events and tasks');

-- Insert Users (Password is 'password123' bcrypt hashed)
-- User 1: Super Admin
-- User 2: NGO Admin
-- User 3: Volunteer
-- User 4: Donor
-- User 5: Event Coordinator
INSERT INTO `users` (`role_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `status`) VALUES
(1, 'System', 'Admin', 'admin@ngo.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1234567890', 'active'),
(2, 'Sarah', 'Jenkins', 'sarah@ngo.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654321', 'active'),
(3, 'John', 'Doe', 'john@volunteer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '5551234567', 'active'),
(4, 'Alice', 'Smith', 'alice@donor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '5559876543', 'active'),
(5, 'Mike', 'Johnson', 'mike@ngo.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '5554567890', 'active');

-- Insert NGO Admin Details
INSERT INTO `ngo_admins` (`user_id`, `department`) VALUES
(2, 'Operations and Funding');

-- Insert Volunteer Details
INSERT INTO `volunteers` (`user_id`, `date_of_birth`, `address`, `skills`, `availability`, `joined_date`) VALUES
(3, '1995-05-15', '123 Volunteer Street, City', 'First Aid, Event Planning, Logistics', 'Weekends', '2026-01-10');

-- Insert Donor Details
INSERT INTO `donors` (`user_id`, `donor_type`, `company_name`, `address`, `total_donated`) VALUES
(4, 'individual', NULL, '456 Donor Avenue, City', 500.00);

-- Insert Event Coordinator Details
INSERT INTO `event_coordinators` (`user_id`, `expertise_area`) VALUES
(5, 'Logistics and Health Camps');

-- Insert Campaign
INSERT INTO `campaigns` (`ngo_admin_id`, `title`, `description`, `goal_amount`, `raised_amount`, `start_date`, `end_date`, `status`) VALUES
(1, 'Winter Food Drive', 'Providing hot meals to the homeless during winter.', 10000.00, 2500.00, '2026-11-01', '2027-01-31', 'active');

-- Insert Event
INSERT INTO `events` (`campaign_id`, `coordinator_id`, `title`, `description`, `location`, `event_date`, `start_time`, `end_time`, `max_volunteers`, `status`) VALUES
(1, 1, 'Downtown Food Distribution', 'Distributing food packets in the downtown shelter area.', 'Main Shelter Downtown', '2026-12-15', '10:00:00', '16:00:00', 50, 'upcoming');

-- Insert Event Registration (Volunteer joins Event)
INSERT INTO `event_registrations` (`event_id`, `volunteer_id`, `status`) VALUES
(1, 1, 'approved');

-- Insert Task for Volunteer
INSERT INTO `tasks` (`event_id`, `volunteer_id`, `assigned_by`, `title`, `description`, `priority`, `status`, `deadline`, `estimated_hours`) VALUES
(1, 1, 2, 'Food Distribution Logistics', 'Organize the food packets into delivery vehicles.', 'high', 'in_progress', '2026-12-15 09:00:00', 4.00);

-- Insert Task Update
INSERT INTO `task_updates` (`task_id`, `progress_percentage`, `hours_worked`, `remarks`, `update_date`) VALUES
(1, 40, 1.5, 'Packets sorted and ready for loading.', '2026-12-14');

-- Insert Attendance (Future event example, but marked for completeness)
INSERT INTO `attendance` (`event_id`, `volunteer_id`, `attendance_date`, `check_in_time`, `check_out_time`, `status`, `remarks`) VALUES
(1, 1, '2026-12-15', '09:45:00', '16:30:00', 'present', 'Excellent coordination');

-- Insert Donation
INSERT INTO `donations` (`donor_id`, `campaign_id`, `amount`, `currency`, `donation_date`, `status`, `message`) VALUES
(1, 1, 500.00, 'USD', '2026-11-15 14:30:00', 'completed', 'Keep up the good work!');

-- Insert Payment for Donation
INSERT INTO `payments` (`donation_id`, `transaction_id`, `payment_method`, `amount`, `payment_status`, `payment_date`) VALUES
(1, 'TXN-987654321', 'credit_card', 500.00, 'success', '2026-11-15 14:35:00');

-- Insert Notification
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `is_read`) VALUES
(3, 'Task Assigned', 'You have been assigned to: Food Distribution Logistics.', 'info', FALSE);
