-- database/sample_data.sql
-- Sample Data for NGO Donation & Volunteer Management System - Version 2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ==========================================================================
-- 1. ROLES
-- ==========================================================================
INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'Super Admin', 'Full system access'),
(2, 'NGO Admin', 'Manages campaigns, donations, and reports'),
(3, 'Donor', 'Makes donations and tracks their contributions'),
(4, 'Volunteer', 'Participates in events and completes tasks'),
(5, 'Event Coordinator', 'Manages events and volunteers');

-- ==========================================================================
-- 2. USERS
-- Password is 'password123' hashed using bcrypt
-- ==========================================================================
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

-- ==========================================================================
-- 2.1 DASHBOARD PREFERENCES
-- ==========================================================================
INSERT INTO `dashboard_preferences` (`user_id`, `theme`, `sidebar_state`) VALUES
(1, 'dark', 'expanded'),
(2, 'light', 'expanded'),
(3, 'system', 'collapsed');

-- ==========================================================================
-- 2.2 VOLUNTEER SKILLS
-- ==========================================================================
INSERT INTO `volunteer_skills` (`volunteer_id`, `skill_name`, `skill_level`) VALUES
(11, 'First Aid', 'Expert'),
(11, 'Event Management', 'Intermediate'),
(12, 'Logistics', 'Expert'),
(13, 'Public Speaking', 'Beginner');

-- ==========================================================================
-- 3. CAMPAIGN CATEGORIES
-- ==========================================================================
INSERT INTO `campaign_categories` (`id`, `name`, `description`, `icon`) VALUES
(1, 'Health & Sanitation', 'Health, medicine, and sanitation initiatives.', 'fa-heartbeat'),
(2, 'Education', 'Schools, books, and education initiatives.', 'fa-book'),
(3, 'Emergency Relief', 'Disaster and emergency response.', 'fa-ambulance'),
(4, 'Community Development', 'Skill development and community building.', 'fa-users'),
(5, 'Social Welfare', 'Food, clothing, and basic needs.', 'fa-hands-helping');

-- ==========================================================================
-- 3.1 CAMPAIGNS
-- ==========================================================================
INSERT INTO `campaigns` (`id`, `category_id`, `name`, `slug`, `short_description`, `description`, `target_amount`, `collected_amount`, `goal_completed_percentage`, `start_date`, `end_date`, `status`, `created_by`, `featured`) VALUES
(1, 1, 'Clean Water Initiative', 'clean-water-initiative', 'Clean water for rural areas', 'Providing clean drinking water to rural areas to prevent waterborne diseases.', 50000.00, 15000.00, 30.00, '2026-01-01', '2026-12-31', 'active', 2, 1),
(2, 2, 'Education for All', 'education-for-all', 'Books for children', 'Supplying books and stationery for underprivileged children.', 25000.00, 25000.00, 100.00, '2026-02-15', '2026-06-30', 'completed', 2, 0),
(3, 3, 'Disaster Relief Fund', 'disaster-relief-fund', 'Flood victim relief', 'Emergency funds for flood victims.', 100000.00, 45000.50, 45.00, '2026-08-01', '2027-01-31', 'active', 3, 1),
(4, 4, 'Women Empowerment Project', 'women-empowerment-project', 'Skill development', 'Skill development workshops for women.', 30000.00, 5000.00, 16.67, '2026-09-01', '2027-03-31', 'active', 3, 0),
(5, 5, 'Winter Blanket Drive', 'winter-blanket-drive', 'Blankets for winter', 'Distributing blankets to the homeless during winter.', 10000.00, 0.00, 0.00, '2026-11-01', '2027-02-28', 'draft', 2, 0);

-- ==========================================================================
-- 3.2 CAMPAIGN IMAGES
-- ==========================================================================
INSERT INTO `campaign_images` (`campaign_id`, `image_path`, `caption`, `display_order`) VALUES
(1, 'images/campaigns/clean-water-1.jpg', 'Digging the new well', 1),
(1, 'images/campaigns/clean-water-2.jpg', 'Community gathering', 2),
(3, 'images/campaigns/relief-1.jpg', 'Rescue operations', 1);

-- ==========================================================================
-- 4. DONATIONS
-- ==========================================================================
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

-- ==========================================================================
-- 5. EVENTS
-- ==========================================================================
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `venue`, `event_date`, `event_time`, `registration_deadline`, `max_volunteers`, `expected_budget`, `coordinator_id`, `status`) VALUES
(1, 'River Cleaning Drive', 'Annual cleaning of the city river banks.', 'Environment', 'City River Bank', '2026-10-15', '08:00:00', '2026-10-10 23:59:59', 50, 500.00, 4, 'upcoming'),
(2, 'Food Distribution Camp', 'Distributing food packets in slum areas.', 'Welfare', 'Community Hall 1', '2026-11-20', '10:00:00', '2026-11-15 23:59:59', 30, 1000.00, 5, 'upcoming'),
(3, 'Blood Donation Camp', 'Organizing blood donation with local hospitals.', 'Health', 'City Hospital', '2026-12-05', '09:00:00', '2026-12-01 23:59:59', 20, 200.00, 4, 'upcoming'),
(4, 'Tree Plantation Drive', 'Planting 1000 saplings across the city.', 'Environment', 'City Park', '2026-06-05', '07:30:00', '2026-06-01 23:59:59', 100, 1500.00, 5, 'completed'),
(5, 'Orphanage Visit', 'Spending time and organizing games for orphans.', 'Community', 'Hope Orphanage', '2026-11-25', '14:00:00', '2026-11-20 23:59:59', 15, 100.00, 4, 'upcoming');

-- ==========================================================================
-- 5.1 EVENT IMAGES
-- ==========================================================================
INSERT INTO `event_images` (`event_id`, `image_path`, `caption`, `display_order`) VALUES
(4, 'images/events/plantation-1.jpg', 'Team working', 1),
(4, 'images/events/plantation-2.jpg', 'Saplings ready', 2);

-- ==========================================================================
-- 6. VOLUNTEER REGISTRATIONS
-- ==========================================================================
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

-- ==========================================================================
-- 7. NOTIFICATIONS
-- ==========================================================================
INSERT INTO `notifications` (`recipient_id`, `title`, `message`, `notification_type`) VALUES
(11, 'Registration Approved', 'Your registration for River Cleaning Drive is approved.', 'Event'),
(14, 'New Event Added', 'Food Distribution Camp needs volunteers.', 'System'),
(6, 'Donation Received', 'Thank you for your generous donation to Clean Water Initiative.', 'Donation'),
(2, 'Campaign Goal Reached', 'Education for All campaign has reached its target!', 'Campaign');

-- ==========================================================================
-- 8. ANNOUNCEMENTS
-- ==========================================================================
INSERT INTO `announcements` (`id`, `title`, `content`, `created_by`, `publish_date`, `status`) VALUES
(1, 'Welcome to the New Portal', 'We are glad to launch our upgraded NGO management system Version 2.0.', 1, '2026-07-01 10:00:00', 'published'),
(2, 'Upcoming Blood Donation Drive', 'Please register early for the upcoming blood donation camp in December.', 2, '2026-11-01 09:00:00', 'published');

-- ==========================================================================
-- 9. SETTINGS
-- ==========================================================================
INSERT INTO `settings` (`id`, `ngo_name`, `mission`, `vision`, `address`, `email`, `phone`, `website`) VALUES
(1, 'Global Hope Foundation', 'To eradicate poverty and provide equal opportunities to everyone.', 'A world where everyone has a fair chance to succeed.', '123 Hope Street, Charity City, 10001', 'contact@globalhope.org', '+1 800 123 4567', 'www.globalhope.org');

-- ==========================================================================
-- 10. TESTIMONIALS
-- ==========================================================================
INSERT INTO `testimonials` (`name`, `designation`, `message`, `rating`, `featured`, `status`) VALUES
('Sarah Jenkins', 'Top Donor', 'I love the transparency of this platform. It feels great to see exactly where my money goes.', 5, 1, 'approved'),
('Mark Thompson', 'Volunteer', 'Being a part of the tree plantation drive was amazing. The coordinators are highly professional.', 5, 1, 'approved');

-- ==========================================================================
-- 11. PARTNERS
-- ==========================================================================
INSERT INTO `partners` (`name`, `logo`, `website`, `description`, `display_order`, `status`) VALUES
('Tech for Good', 'images/partners/tech-for-good.png', 'https://techforgood.example.com', 'Technology partner providing infrastructure.', 1, 'active'),
('City Hospital', 'images/partners/city-hospital.png', 'https://cityhospital.example.com', 'Healthcare partner for blood donation camps.', 2, 'active');

-- ==========================================================================
-- 12. FAQS
-- ==========================================================================
INSERT INTO `faqs` (`question`, `answer`, `display_order`, `status`) VALUES
('How can I become a volunteer?', 'You can register on our portal and apply for upcoming events.', 1, 'active'),
('Are my donations tax deductible?', 'Yes, all donations made are eligible for tax deductions under section 80G.', 2, 'active');

-- ==========================================================================
-- 13. NEWSLETTER SUBSCRIBERS
-- ==========================================================================
INSERT INTO `newsletter_subscribers` (`email`, `subscription_status`) VALUES
('supporter1@example.com', 'subscribed'),
('supporter2@example.com', 'subscribed'),
('ex-supporter@example.com', 'unsubscribed');

COMMIT;
