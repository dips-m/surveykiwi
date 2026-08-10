SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `surveykiwi` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `surveykiwi`;

-- --------------------------------------------------------

--
-- Table structure for table `surveys`
--
CREATE TABLE `surveys` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `public_token` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `end_date` timestamp NULL DEFAULT NULL,
  `status` enum('draft','published','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surveys_public_token_unique` (`public_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `survey_questions`
--
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
  CONSTRAINT `survey_questions_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `survey_schedule_logs`
--
CREATE TABLE `survey_schedule_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `survey_id` bigint UNSIGNED NOT NULL,
  `executed_at` timestamp NULL DEFAULT NULL,
  `status` enum('success','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'success',
  PRIMARY KEY (`id`),
  KEY `survey_schedule_logs_survey_id_foreign` (`survey_id`),
  CONSTRAINT `survey_schedule_logs_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `survey_participants`
--
CREATE TABLE `survey_participants` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `survey_id` bigint UNSIGNED DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_saved_for_future` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `survey_participants_survey_id_foreign` (`survey_id`),
  CONSTRAINT `survey_participants_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `survey_invitations`
--
CREATE TABLE `survey_invitations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `survey_schedule_log_id` bigint UNSIGNED NOT NULL,
  `survey_participant_id` bigint UNSIGNED NOT NULL,
  `status` enum('sent','bounced') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sent',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `survey_invitations_survey_schedule_log_id_foreign` (`survey_schedule_log_id`),
  KEY `survey_invitations_survey_participant_id_foreign` (`survey_participant_id`),
  CONSTRAINT `survey_invitations_survey_participant_id_foreign` FOREIGN KEY (`survey_participant_id`) REFERENCES `survey_participants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `survey_invitations_survey_schedule_log_id_foreign` FOREIGN KEY (`survey_schedule_log_id`) REFERENCES `survey_schedule_logs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `survey_schedules`
--
CREATE TABLE `survey_schedules` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `survey_id` bigint UNSIGNED NOT NULL,
  `frequency` enum('once','daily','weekly','monthly','quarterly') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` timestamp NOT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `next_run_at` timestamp NOT NULL,
  `last_run_at` timestamp NULL DEFAULT NULL,
  `reminders_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `reminder_interval_days` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `survey_schedules_survey_id_foreign` (`survey_id`),
  CONSTRAINT `survey_schedules_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--
CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `survey_id` bigint UNSIGNED DEFAULT NULL,
  `status` enum('sent','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sent',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

COMMIT;