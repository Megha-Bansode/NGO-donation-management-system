-- database/ngo_management.sql
-- NGO Donation & Volunteer Management System Database (Full Schema + Data)
-- Version 2.0 - Fully Normalized (3NF), InnoDB, utf8mb4

CREATE DATABASE IF NOT EXISTS `ngo_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ngo_management`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ==========================================================================
-- TABLE CREATION (SCHEMA)
-- ==========================================================================

-- 1. ROLES TABLE
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. USERS TABLE
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `phone_verified` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive','suspended','banned') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  KEY `email_verified` (`email_verified`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEW: CAMPAIGN CATEGORIES TABLE
CREATE TABLE `campaign_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CAMPAIGNS TABLE
CREATE TABLE `campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `target_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `collected_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `goal_completed_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `campaign_image` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `beneficiaries_count` int(11) NOT NULL DEFAULT 0,
  `views` int(11) NOT NULL DEFAULT 0,
  `status` enum('draft','active','completed','cancelled') NOT NULL DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`),
  KEY `featured` (`featured`),
  CONSTRAINT `fk_campaigns_category` FOREIGN KEY (`category_id`) REFERENCES `campaign_categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_campaigns_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_campaigns_updated` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEW: CAMPAIGN IMAGES TABLE
CREATE TABLE `campaign_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `campaign_id` (`campaign_id`),
  CONSTRAINT `fk_campaign_images_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. DONATIONS TABLE
CREATE TABLE `donations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `donor_id` int(11) NOT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `donor_message` text DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_gateway` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `gateway_response` text DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `receipt_number` varchar(50) NOT NULL,
  `donation_date` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  UNIQUE KEY `receipt_number` (`receipt_number`),
  KEY `donor_id` (`donor_id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `donation_date` (`donation_date`),
  CONSTRAINT `fk_donations_donor` FOREIGN KEY (`donor_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_donations_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. DONATION RECEIPTS TABLE
CREATE TABLE `donation_receipts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `receipt_number` varchar(50) NOT NULL,
  `donation_id` int(11) NOT NULL,
  `generated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `pdf_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `receipt_number` (`receipt_number`),
  KEY `donation_id` (`donation_id`),
  CONSTRAINT `fk_receipts_donation` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. EVENTS TABLE
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `venue` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `registration_deadline` datetime DEFAULT NULL,
  `max_volunteers` int(11) NOT NULL DEFAULT 0,
  `expected_budget` decimal(12,2) DEFAULT NULL,
  `actual_budget` decimal(12,2) DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `coordinator_id` int(11) NOT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled') NOT NULL DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `coordinator_id` (`coordinator_id`),
  KEY `event_date` (`event_date`),
  KEY `registration_deadline` (`registration_deadline`),
  CONSTRAINT `fk_events_coordinator` FOREIGN KEY (`coordinator_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEW: EVENT IMAGES TABLE
CREATE TABLE `event_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `fk_event_images_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. VOLUNTEER REGISTRATIONS TABLE
CREATE TABLE `volunteer_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `volunteer_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `registration_date` datetime NOT NULL DEFAULT current_timestamp(),
  `approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `attendance_status` enum('registered','attended','absent') NOT NULL DEFAULT 'registered',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `volunteer_event` (`volunteer_id`,`event_id`),
  KEY `event_id` (`event_id`),
  KEY `approval_status` (`approval_status`),
  CONSTRAINT `fk_volreg_volunteer` FOREIGN KEY (`volunteer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_volreg_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEW: VOLUNTEER SKILLS TABLE
CREATE TABLE `volunteer_skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `volunteer_id` int(11) NOT NULL,
  `skill_name` varchar(100) NOT NULL,
  `skill_level` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `volunteer_id` (`volunteer_id`),
  CONSTRAINT `fk_volunteer_skills_user` FOREIGN KEY (`volunteer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. TASKS TABLE
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `volunteer_id` int(11) DEFAULT NULL,
  `event_id` int(11) NOT NULL,
  `task_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `deadline` datetime DEFAULT NULL,
  `completion_status` enum('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `volunteer_id` (`volunteer_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `fk_tasks_volunteer` FOREIGN KEY (`volunteer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tasks_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. ATTENDANCE TABLE
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `volunteer_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `check_in` datetime DEFAULT NULL,
  `check_out` datetime DEFAULT NULL,
  `attendance_status` enum('present','absent','late') NOT NULL DEFAULT 'absent',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `volunteer_event_att` (`volunteer_id`,`event_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `fk_attendance_volunteer` FOREIGN KEY (`volunteer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_attendance_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. NOTIFICATIONS TABLE
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `read_status` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `recipient_id` (`recipient_id`),
  KEY `read_status` (`read_status`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. ANNOUNCEMENTS TABLE
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `publish_date` datetime NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `fk_announcements_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. CONTACT MESSAGES TABLE
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied','closed') NOT NULL DEFAULT 'new',
  `assigned_to` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `reply_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `assigned_to` (`assigned_to`),
  CONSTRAINT `fk_contact_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. ACTIVITY LOGS TABLE
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `browser` varchar(255) DEFAULT NULL,
  `device` varchar(100) DEFAULT NULL,
  `operating_system` varchar(100) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. SETTINGS TABLE
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ngo_name` varchar(255) NOT NULL,
  `mission` text DEFAULT NULL,
  `vision` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `social_media_links` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEW: TESTIMONIALS TABLE
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `rating` tinyint(1) NOT NULL DEFAULT 5,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `featured` (`featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEW: PARTNERS TABLE
CREATE TABLE `partners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEW: FAQS TABLE
CREATE TABLE `faqs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEW: NEWSLETTER SUBSCRIBERS TABLE
CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `subscription_status` enum('subscribed','unsubscribed') NOT NULL DEFAULT 'subscribed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `subscription_status` (`subscription_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEW: PASSWORD RESET TOKENS TABLE
CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_password_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEW: EMAIL VERIFICATIONS TABLE
CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `verification_token` varchar(255) NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_email_verification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEW: DASHBOARD PREFERENCES TABLE
CREATE TABLE `dashboard_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `theme` enum('light','dark','system') NOT NULL DEFAULT 'light',
  `sidebar_state` enum('expanded','collapsed') NOT NULL DEFAULT 'expanded',
  `dashboard_layout` varchar(50) DEFAULT 'default',
  `language` varchar(10) DEFAULT 'en',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_dashboard_pref_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================================================
-- SAMPLE DATA INJECTION
-- ==========================================================================

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'Super Admin', 'Full system access'),
(2, 'NGO Admin', 'Manages campaigns, donations, and reports'),
(3, 'Donor', 'Makes donations and tracks their contributions'),
(4, 'Volunteer', 'Participates in events and completes tasks'),
(5, 'Event Coordinator', 'Manages events and volunteers');

INSERT INTO `users` (`id`, `role_id`, `full_name`, `email`, `phone`, `password`, `status`, `email_verified`) VALUES
(1, 1, 'System Administrator', 'admin@ngosystem.com', '1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(2, 2, 'Jane Smith (NGO Admin)', 'jane@ngosystem.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(3, 2, 'Robert Johnson (NGO Admin)', 'robert@ngosystem.com', '5551234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(4, 5, 'Emily Davis (Coordinator)', 'emily@ngosystem.com', '1112223333', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(5, 5, 'Michael Wilson (Coordinator)', 'michael@ngosystem.com', '4445556666', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(6, 3, 'Alice Donor', 'alice@example.com', '7778889999', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(7, 3, 'Bob Donor', 'bob@example.com', '1231231234', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(8, 3, 'Charlie Donor', 'charlie@example.com', '3213214321', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(9, 3, 'Diana Donor', 'diana@example.com', '4564564567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(10, 3, 'Edward Donor', 'edward@example.com', '6546546543', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(11, 4, 'Fiona Volunteer', 'fiona@example.com', '9990001111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(12, 4, 'George Volunteer', 'george@example.com', '8887776666', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(13, 4, 'Hannah Volunteer', 'hannah@example.com', '5554443333', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(14, 4, 'Ian Volunteer', 'ian@example.com', '2223334444', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(15, 4, 'Jack Volunteer', 'jack@example.com', '1119998888', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(16, 4, 'Karen Volunteer', 'karen@example.com', '7776665555', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1);

INSERT INTO `dashboard_preferences` (`user_id`, `theme`, `sidebar_state`) VALUES
(1, 'dark', 'expanded'),
(2, 'light', 'expanded'),
(3, 'system', 'collapsed');

INSERT INTO `volunteer_skills` (`volunteer_id`, `skill_name`, `skill_level`) VALUES
(11, 'First Aid', 'Expert'),
(11, 'Event Management', 'Intermediate'),
(12, 'Logistics', 'Expert'),
(13, 'Public Speaking', 'Beginner');

INSERT INTO `campaign_categories` (`id`, `name`, `description`, `icon`) VALUES
(1, 'Health & Sanitation', 'Health, medicine, and sanitation initiatives.', 'fa-heartbeat'),
(2, 'Education', 'Schools, books, and education initiatives.', 'fa-book'),
(3, 'Emergency Relief', 'Disaster and emergency response.', 'fa-ambulance'),
(4, 'Community Development', 'Skill development and community building.', 'fa-users'),
(5, 'Social Welfare', 'Food, clothing, and basic needs.', 'fa-hands-helping');

INSERT INTO `campaigns` (`id`, `category_id`, `name`, `slug`, `short_description`, `description`, `target_amount`, `collected_amount`, `goal_completed_percentage`, `start_date`, `end_date`, `status`, `created_by`, `featured`) VALUES
(1, 1, 'Clean Water Initiative', 'clean-water-initiative', 'Clean water for rural areas', 'Providing clean drinking water to rural areas to prevent waterborne diseases.', 50000.00, 15000.00, 30.00, '2026-01-01', '2026-12-31', 'active', 2, 1),
(2, 2, 'Education for All', 'education-for-all', 'Books for children', 'Supplying books and stationery for underprivileged children.', 25000.00, 25000.00, 100.00, '2026-02-15', '2026-06-30', 'completed', 2, 0),
(3, 3, 'Disaster Relief Fund', 'disaster-relief-fund', 'Flood victim relief', 'Emergency funds for flood victims.', 100000.00, 45000.50, 45.00, '2026-08-01', '2027-01-31', 'active', 3, 1),
(4, 4, 'Women Empowerment Project', 'women-empowerment-project', 'Skill development', 'Skill development workshops for women.', 30000.00, 5000.00, 16.67, '2026-09-01', '2027-03-31', 'active', 3, 0),
(5, 5, 'Winter Blanket Drive', 'winter-blanket-drive', 'Blankets for winter', 'Distributing blankets to the homeless during winter.', 10000.00, 0.00, 0.00, '2026-11-01', '2027-02-28', 'draft', 2, 0);

INSERT INTO `campaign_images` (`campaign_id`, `image_path`, `caption`, `display_order`) VALUES
(1, 'images/campaigns/clean-water-1.jpg', 'Digging the new well', 1),
(1, 'images/campaigns/clean-water-2.jpg', 'Community gathering', 2),
(3, 'images/campaigns/relief-1.jpg', 'Rescue operations', 1);

INSERT INTO `donations` (`id`, `donor_id`, `campaign_id`, `amount`, `payment_method`, `transaction_id`, `payment_status`, `receipt_number`, `is_anonymous`, `donor_message`, `donation_date`) VALUES
(1, 6, 1, 1000.00, 'Credit Card', 'TXN-10001', 'completed', 'REC-2026-0001', 0, 'Happy to help!', '2026-03-10 10:00:00'),
(2, 7, 1, 500.00, 'PayPal', 'TXN-10002', 'completed', 'REC-2026-0002', 1, NULL, '2026-03-12 11:30:00'),
(3, 8, 2, 2000.00, 'Bank Transfer', 'TXN-10003', 'completed', 'REC-2026-0003', 0, 'For education', '2026-04-05 09:15:00'),
(4, 9, 2, 150.00, 'Credit Card', 'TXN-10004', 'completed', 'REC-2026-0004', 0, NULL, '2026-04-10 14:20:00'),
(5, 10, 3, 5000.00, 'Credit Card', 'TXN-10005', 'completed', 'REC-2026-0005', 0, 'Emergency support', '2026-08-15 16:45:00'),
(6, 6, 3, 200.00, 'PayPal', 'TXN-10006', 'completed', 'REC-2026-0006', 0, NULL, '2026-08-16 08:30:00'),
(7, 7, 4, 300.00, 'Credit Card', 'TXN-10007', 'completed', 'REC-2026-0007', 0, NULL, '2026-09-05 11:00:00'),
(8, 8, 1, 1500.00, 'Bank Transfer', 'TXN-10008', 'completed', 'REC-2026-0008', 0, NULL, '2026-03-20 13:10:00'),
(9, 9, 3, 750.00, 'PayPal', 'TXN-10009', 'completed', 'REC-2026-0009', 1, 'God bless', '2026-08-20 15:00:00'),
(10, 10, 2, 1200.00, 'Credit Card', 'TXN-10010', 'completed', 'REC-2026-0010', 0, NULL, '2026-05-10 10:30:00'),
(11, 6, 4, 100.00, 'PayPal', 'TXN-10011', 'completed', 'REC-2026-0011', 0, NULL, '2026-09-10 09:45:00'),
(12, 7, 1, 250.00, 'Credit Card', 'TXN-10012', 'pending', 'REC-2026-0012', 0, NULL, '2026-07-20 12:00:00'),
(13, 8, 3, 500.00, 'Bank Transfer', 'TXN-10013', 'completed', 'REC-2026-0013', 0, NULL, '2026-08-25 14:15:00'),
(14, 9, 4, 600.00, 'Credit Card', 'TXN-10014', 'completed', 'REC-2026-0014', 0, NULL, '2026-09-15 16:30:00'),
(15, 10, 1, 2000.00, 'PayPal', 'TXN-10015', 'completed', 'REC-2026-0015', 1, 'Anonymous gift', '2026-04-01 10:00:00');

INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `venue`, `event_date`, `event_time`, `registration_deadline`, `max_volunteers`, `expected_budget`, `coordinator_id`, `status`) VALUES
(1, 'River Cleaning Drive', 'Annual cleaning of the city river banks.', 'Environment', 'City River Bank', '2026-10-15', '08:00:00', '2026-10-10 23:59:59', 50, 500.00, 4, 'upcoming'),
(2, 'Food Distribution Camp', 'Distributing food packets in slum areas.', 'Welfare', 'Community Hall 1', '2026-11-20', '10:00:00', '2026-11-15 23:59:59', 30, 1000.00, 5, 'upcoming'),
(3, 'Blood Donation Camp', 'Organizing blood donation with local hospitals.', 'Health', 'City Hospital', '2026-12-05', '09:00:00', '2026-12-01 23:59:59', 20, 200.00, 4, 'upcoming'),
(4, 'Tree Plantation Drive', 'Planting 1000 saplings across the city.', 'Environment', 'City Park', '2026-06-05', '07:30:00', '2026-06-01 23:59:59', 100, 1500.00, 5, 'completed'),
(5, 'Orphanage Visit', 'Spending time and organizing games for orphans.', 'Community', 'Hope Orphanage', '2026-11-25', '14:00:00', '2026-11-20 23:59:59', 15, 100.00, 4, 'upcoming');

INSERT INTO `event_images` (`event_id`, `image_path`, `caption`, `display_order`) VALUES
(4, 'images/events/plantation-1.jpg', 'Team working', 1),
(4, 'images/events/plantation-2.jpg', 'Saplings ready', 2);

INSERT INTO `volunteer_registrations` (`volunteer_id`, `event_id`, `approval_status`, `attendance_status`) VALUES
(11, 1, 'approved', 'registered'),
(12, 1, 'approved', 'registered'),
(13, 1, 'approved', 'registered'),
(14, 2, 'approved', 'registered'),
(15, 2, 'pending', 'registered'),
(16, 2, 'approved', 'registered'),
(11, 3, 'approved', 'registered'),
(12, 4, 'approved', 'attended'),
(13, 4, 'approved', 'attended'),
(14, 5, 'pending', 'registered');

INSERT INTO `notifications` (`recipient_id`, `title`, `message`, `notification_type`) VALUES
(11, 'Registration Approved', 'Your registration for River Cleaning Drive is approved.', 'Event'),
(14, 'New Event Added', 'Food Distribution Camp needs volunteers.', 'System'),
(6, 'Donation Received', 'Thank you for your generous donation to Clean Water Initiative.', 'Donation'),
(2, 'Campaign Goal Reached', 'Education for All campaign has reached its target!', 'Campaign');

INSERT INTO `announcements` (`id`, `title`, `content`, `created_by`, `publish_date`, `status`) VALUES
(1, 'Welcome to the New Portal', 'We are glad to launch our upgraded NGO management system Version 2.0.', 1, '2026-07-01 10:00:00', 'published'),
(2, 'Upcoming Blood Donation Drive', 'Please register early for the upcoming blood donation camp in December.', 2, '2026-11-01 09:00:00', 'published');

INSERT INTO `settings` (`id`, `ngo_name`, `mission`, `vision`, `address`, `email`, `phone`, `website`) VALUES
(1, 'Global Hope Foundation', 'To eradicate poverty and provide equal opportunities to everyone.', 'A world where everyone has a fair chance to succeed.', '123 Hope Street, Charity City, 10001', 'contact@globalhope.org', '+1 800 123 4567', 'www.globalhope.org');

INSERT INTO `testimonials` (`name`, `designation`, `message`, `rating`, `featured`, `status`) VALUES
('Sarah Jenkins', 'Top Donor', 'I love the transparency of this platform. It feels great to see exactly where my money goes.', 5, 1, 'approved'),
('Mark Thompson', 'Volunteer', 'Being a part of the tree plantation drive was amazing. The coordinators are highly professional.', 5, 1, 'approved');

INSERT INTO `partners` (`name`, `logo`, `website`, `description`, `display_order`, `status`) VALUES
('Tech for Good', 'images/partners/tech-for-good.png', 'https://techforgood.example.com', 'Technology partner providing infrastructure.', 1, 'active'),
('City Hospital', 'images/partners/city-hospital.png', 'https://cityhospital.example.com', 'Healthcare partner for blood donation camps.', 2, 'active');

INSERT INTO `faqs` (`question`, `answer`, `display_order`, `status`) VALUES
('How can I become a volunteer?', 'You can register on our portal and apply for upcoming events.', 1, 'active'),
('Are my donations tax deductible?', 'Yes, all donations made are eligible for tax deductions under section 80G.', 2, 'active');

INSERT INTO `newsletter_subscribers` (`email`, `subscription_status`) VALUES
('supporter1@example.com', 'subscribed'),
('supporter2@example.com', 'subscribed'),
('ex-supporter@example.com', 'unsubscribed');

COMMIT;
