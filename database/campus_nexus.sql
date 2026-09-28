-- Campus Nexus Database Schema
-- Student Collaboration Hub

CREATE DATABASE IF NOT EXISTS `campus_nexus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `campus_nexus`;

-- Users / Students Table
CREATE TABLE IF NOT EXISTS `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `department` VARCHAR(100) DEFAULT NULL,
    `year_of_study` INT DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `points` INT DEFAULT 0,
    `badge` VARCHAR(50) DEFAULT 'Novice',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Skills Table
CREATE TABLE IF NOT EXISTS `skills` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `category` VARCHAR(50) DEFAULT 'General'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Student Skills Pivot Table
CREATE TABLE IF NOT EXISTS `student_skills` (
    `student_id` INT NOT NULL,
    `skill_id` INT NOT NULL,
    `proficiency_level` ENUM('Beginner', 'Intermediate', 'Advanced', 'Expert') DEFAULT 'Intermediate',
    PRIMARY KEY (`student_id`, `skill_id`),
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`skill_id`) REFERENCES `skills`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Projects Table
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NOT NULL,
    `creator_id` INT NOT NULL,
    `status` ENUM('Open', 'In Progress', 'Completed') DEFAULT 'Open',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`creator_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Events Table
CREATE TABLE IF NOT EXISTS `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NOT NULL,
    `event_date` DATETIME NOT NULL,
    `location` VARCHAR(150) NOT NULL,
    `organizer_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`organizer_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ideas Table
CREATE TABLE IF NOT EXISTS `ideas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NOT NULL,
    `student_id` INT NOT NULL,
    `upvotes` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
