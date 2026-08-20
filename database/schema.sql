SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `surveykiwi`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `surveykiwi`;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------

CREATE TABLE `users` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: surveys
-- --------------------------------------------------------

CREATE TABLE `surveys` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `description` text COLLATE utf8mb4_unicode_ci,
    `status` enum('draft','published','closed')
        COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: survey_questions
-- --------------------------------------------------------

CREATE TABLE `survey_questions` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    `survey_id` bigint UNSIGNED NOT NULL,
    `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
    `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `options` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `settings` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `sort_order` int NOT NULL DEFAULT '0',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `survey_questions_survey_id_foreign` (`survey_id`),
    CONSTRAINT `survey_questions_survey_id_foreign`
        FOREIGN KEY (`survey_id`)
        REFERENCES `surveys` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: survey_participants
-- --------------------------------------------------------

CREATE TABLE `survey_participants` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    `survey_id` bigint UNSIGNED DEFAULT NULL,
    `first_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `survey_participants_survey_id_foreign` (`survey_id`),
    CONSTRAINT `survey_participants_survey_id_foreign`
        FOREIGN KEY (`survey_id`)
        REFERENCES `surveys` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: survey_schedules
-- --------------------------------------------------------

CREATE TABLE `survey_schedules` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    `survey_id` bigint UNSIGNED NOT NULL,
    `frequency` enum(
        'once',
        'daily',
        'weekly',
        'monthly',
        'quarterly'
    ) COLLATE utf8mb4_unicode_ci NOT NULL,
    `start_date` timestamp NOT NULL,
    `end_date` timestamp NULL DEFAULT NULL,
    `reminders_enabled` tinyint(1) NOT NULL DEFAULT '0',
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `survey_schedules_survey_id_foreign` (`survey_id`),
    CONSTRAINT `survey_schedules_survey_id_foreign`
        FOREIGN KEY (`survey_id`)
        REFERENCES `surveys` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: survey_schedule_entries
-- --------------------------------------------------------

CREATE TABLE `survey_schedule_entries` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    `survey_schedule_id` bigint UNSIGNED NOT NULL,
    `start_date` timestamp NOT NULL,
    `end_date` timestamp NOT NULL,
    `status` enum('past','future') NOT NULL DEFAULT 'future',
    `creator_email_sent` tinyint(1) NOT NULL DEFAULT '0',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `survey_schedule_entries_schedule_id_index`
        (`survey_schedule_id`),
    CONSTRAINT `survey_schedule_entries_schedule_id_foreign`
        FOREIGN KEY (`survey_schedule_id`)
        REFERENCES `survey_schedules` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: survey_invitations
-- --------------------------------------------------------

CREATE TABLE `survey_invitations` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    `survey_schedule_entry_id` bigint UNSIGNED NOT NULL,
    `survey_participant_id` bigint UNSIGNED NOT NULL,
    `status` enum('sent','fail')
        COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fail',
    `sent_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,

    PRIMARY KEY (`id`),

    KEY `survey_invitations_schedule_entry_id_index`
        (`survey_schedule_entry_id`),

    KEY `survey_invitations_survey_participant_id_index`
        (`survey_participant_id`),

    UNIQUE KEY `survey_invitations_entry_participant_unique`
        (`survey_schedule_entry_id`, `survey_participant_id`),

    CONSTRAINT `survey_invitations_schedule_entry_id_foreign`
        FOREIGN KEY (`survey_schedule_entry_id`)
        REFERENCES `survey_schedule_entries` (`id`)
        ON DELETE CASCADE,

    CONSTRAINT `survey_invitations_survey_participant_id_foreign`
        FOREIGN KEY (`survey_participant_id`)
        REFERENCES `survey_participants` (`id`)
        ON DELETE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

COMMIT;