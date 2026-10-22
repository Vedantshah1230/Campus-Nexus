-- =====================================================================
-- Campus Nexus - Complete Database Schema & Seed Data
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `campus_nexus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `campus_nexus`;

-- 1. Users Table (Core Auth)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'admin') DEFAULT 'student',
    `last_login_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Student Profiles Table
CREATE TABLE IF NOT EXISTS `student_profiles` (
    `user_id` INT PRIMARY KEY,
    `department` VARCHAR(100) DEFAULT NULL,
    `semester` INT DEFAULT NULL,
    `roll_number` VARCHAR(50) DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `profile_picture` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Skills Catalog
CREATE TABLE IF NOT EXISTS `skills` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `category` VARCHAR(50) DEFAULT 'General'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. User Skills Pivot Table
CREATE TABLE IF NOT EXISTS `user_skills` (
    `user_id` INT NOT NULL,
    `skill_id` INT NOT NULL,
    `proficiency` ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'intermediate',
    PRIMARY KEY (`user_id`, `skill_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`skill_id`) REFERENCES `skills`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Interests Catalog
CREATE TABLE IF NOT EXISTS `interests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. User Interests Pivot Table
CREATE TABLE IF NOT EXISTS `user_interests` (
    `user_id` INT NOT NULL,
    `interest_id` INT NOT NULL,
    PRIMARY KEY (`user_id`, `interest_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`interest_id`) REFERENCES `interests`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Projects Table
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `creator_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NOT NULL,
    `status` ENUM('open', 'in_progress', 'completed') DEFAULT 'open',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`creator_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Project Required Skills Pivot Table
CREATE TABLE IF NOT EXISTS `project_required_skills` (
    `project_id` INT NOT NULL,
    `skill_id` INT NOT NULL,
    `importance` VARCHAR(50) DEFAULT 'required',
    PRIMARY KEY (`project_id`, `skill_id`),
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`skill_id`) REFERENCES `skills`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Project Members / Join Requests Table
CREATE TABLE IF NOT EXISTS `project_members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `role` VARCHAR(50) DEFAULT 'member',
    `status` ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_project_member` (`project_id`, `user_id`),
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Skill Exchanges Table
CREATE TABLE IF NOT EXISTS `skill_exchanges` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `skill_id` INT NOT NULL,
    `description` TEXT NOT NULL,
    `status` ENUM('open', 'closed') DEFAULT 'open',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`skill_id`) REFERENCES `skills`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Skill Exchange Requests Table
CREATE TABLE IF NOT EXISTS `skill_exchange_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `exchange_id` INT NOT NULL,
    `requester_id` INT NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`exchange_id`) REFERENCES `skill_exchanges`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`requester_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Ideas Table
CREATE TABLE IF NOT EXISTS `ideas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `status` ENUM('active', 'archived') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Idea Votes Table
CREATE TABLE IF NOT EXISTS `idea_votes` (
    `idea_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`idea_id`, `user_id`),
    FOREIGN KEY (`idea_id`) REFERENCES `ideas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Events Table
CREATE TABLE IF NOT EXISTS `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `creator_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `event_date` DATETIME NOT NULL,
    `location` VARCHAR(200) NOT NULL,
    `max_participants` INT NOT NULL DEFAULT 50,
    `status` ENUM('active', 'cancelled') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`creator_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Event Registrations Table
CREATE TABLE IF NOT EXISTS `event_registrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `status` ENUM('registered', 'cancelled') DEFAULT 'registered',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_event_registration` (`event_id`, `user_id`),
    FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Gamification Badges Catalog
CREATE TABLE IF NOT EXISTS `badges` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `points` INT NOT NULL DEFAULT 0,
    `icon` VARCHAR(50) DEFAULT 'award'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. User Badges Earned
CREATE TABLE IF NOT EXISTS `user_badges` (
    `user_id` INT NOT NULL,
    `badge_id` INT NOT NULL,
    `earned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `badge_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`badge_id`) REFERENCES `badges`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. User Total Points
CREATE TABLE IF NOT EXISTS `user_points` (
    `user_id` INT PRIMARY KEY,
    `points` INT DEFAULT 0,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Institutional Authentication & Security Audit Logs
CREATE TABLE IF NOT EXISTS `auth_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `email` VARCHAR(150) NOT NULL,
    `action` ENUM('LOGIN_SUCCESS', 'LOGIN_FAILED', 'REGISTRATION', 'LOGOUT') NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- Seed Data: System Badges (IDs match gamification helpers)
-- =====================================================================
INSERT INTO `badges` (`id`, `name`, `description`, `points`, `icon`) VALUES
(1, 'First Project', 'Created their first collaboration project on Campus Nexus', 50, 'rocket'),
(2, 'Team Builder', 'Accepted a member into a project team', 30, 'users'),
(3, 'Skill Sharer', 'Accepted a skill exchange request to teach someone', 40, 'handshake'),
(4, 'Idea Contributor', 'Posted their first proposal to the Idea Board', 20, 'lightbulb'),
(5, 'Event Explorer', 'Registered for their first campus workshop or event', 20, 'calendar')
ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `points` = VALUES(`points`);

-- =====================================================================
-- Seed Data: Common Skills Catalog
-- =====================================================================
INSERT INTO `skills` (`name`, `category`) VALUES
('PHP', 'Web Development'),
('JavaScript', 'Web Development'),
('Python', 'Programming'),
('MySQL', 'Database'),
('React', 'Frontend'),
('UI/UX Design', 'Design'),
('Machine Learning', 'Data Science'),
('Cybersecurity', 'Security')
ON DUPLICATE KEY UPDATE `category` = VALUES(`category`);

-- =====================================================================
-- Seed Data: Common Interests Catalog
-- =====================================================================
INSERT INTO `interests` (`name`) VALUES
('Web & Mobile Apps'),
('Artificial Intelligence'),
('Competitive Programming'),
('Open Source Software'),
('Robotics & IoT'),
('Graphic Design & Branding'),
('Game Development'),
('Hackathons')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
