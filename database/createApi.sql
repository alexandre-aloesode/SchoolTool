CREATE DATABASE IF NOT EXISTS `schooltool_api`;
USE `schooltool_api`;
-- `schooltool-api`.absence definition

CREATE TABLE IF NOT EXISTS `absence` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_fk` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `validator` varchar(100) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `link` text DEFAULT NULL,
  `reason` varchar(100) DEFAULT NULL,
  `comment` varchar(500) DEFAULT NULL,
  `creation_date` datetime NOT NULL DEFAULT current_timestamp(),
  `update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
);


-- `schooltool-api`.alert definition

CREATE TABLE IF NOT EXISTS `alert` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_fk` int(11) NOT NULL,
  `followup_fk` int(11) NOT NULL,
  `date` date NOT NULL,
  `level` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);


-- `schooltool-api`.calendar definition

CREATE TABLE IF NOT EXISTS `calendar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promotion_fk` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `calendar_unique` (`promotion_fk`)
);


-- `schooltool-api`.calendar_history definition

CREATE TABLE IF NOT EXISTS `calendar_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `applicant_fk` int(11) NOT NULL,
  `calendar_fk` int(11) NOT NULL,
  `date` date NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promotion_history_applicant_fk` (`applicant_fk`),
  KEY `promotion_history_promotion_fk` (`calendar_fk`)
);


CREATE TABLE IF NOT EXISTS `class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`(191))
);



-- `schooltool-api`.log definition

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `role` int(11) DEFAULT NULL,
  `scope` int(11) DEFAULT NULL,
  `params` text NOT NULL,
  `method` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
);


-- `schooltool-api`.logtime_event definition

CREATE TABLE IF NOT EXISTS `logtime_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_fk` int(11) NOT NULL,
  `adm_email` varchar(100) NOT NULL,
  `creation_date` date NOT NULL,
  `duration` int(11) NOT NULL,
  `logtime_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  PRIMARY KEY (`id`)
);


-- `schooltool-api`.`section` definition

CREATE TABLE IF NOT EXISTS `section` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`(191))
);


-- `schooltool-api`.unit definition

CREATE TABLE IF NOT EXISTS `unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`(191))
);


-- `schooltool-api`.activity definition

CREATE TABLE IF NOT EXISTS `activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('Consultation technique','How to','Kick-off','Soutenance','Suivi de projet','Coaching','Anglais','Relation\nEntreprises','Autre') NOT NULL,
  `is_mandatory` tinyint(1) NOT NULL,
  `author` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `unit_fk` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_unit_FK` (`unit_fk`),
  CONSTRAINT `activity_unit_FK` FOREIGN KEY (`unit_fk`) REFERENCES `unit` (`id`) ON DELETE CASCADE
);


-- `schooltool-api`.calendar_day definition

CREATE TABLE IF NOT EXISTS `calendar_day` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `calendar_fk` int(11) NOT NULL,
  `day` date NOT NULL,
  `type` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `calendar_day_UN` (`day`,`calendar_fk`),
  KEY `calendar_day_FK` (`calendar_fk`),
  CONSTRAINT `calendar_day_FK` FOREIGN KEY (`calendar_fk`) REFERENCES `calendar` (`id`) ON DELETE CASCADE
);


-- `schooltool-api`.job definition

CREATE TABLE IF NOT EXISTS `job` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `duration` int(11) NOT NULL,
  `min_students` int(11) NOT NULL DEFAULT 1,
  `max_students` int(11) NOT NULL DEFAULT 1,
  `link_subject` text DEFAULT NULL,
  `link_tutor_guide` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `unit_fk` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`(191)),
  KEY `job_unit_key` (`unit_fk`),
  CONSTRAINT `job_unit_fk` FOREIGN KEY (`unit_fk`) REFERENCES `unit` (`id`) ON DELETE CASCADE
);


-- `schooltool-api`.promotion definition

CREATE TABLE IF NOT EXISTS `promotion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `year` varchar(255) NOT NULL,
  `section_fk` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `formation_type` enum('Alternance','Initiale','Continue','PRF','Online') DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `certification` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`(191)),
  KEY `promotion_section_fk` (`section_fk`),
  CONSTRAINT `promotion_section_fk` FOREIGN KEY (`section_fk`) REFERENCES `section` (`id`)
);


-- `schooltool-api`.promotion_unit definition

CREATE TABLE IF NOT EXISTS `promotion_unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promotion_fk` int(11) NOT NULL,
  `unit_fk` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `promotion_unit_promotion_fk` (`promotion_fk`),
  KEY `promotion_unit_unit_fk` (`unit_fk`),
  CONSTRAINT `promotion_unit_promotion_fk` FOREIGN KEY (`promotion_fk`) REFERENCES `promotion` (`id`) ON DELETE CASCADE,
  CONSTRAINT `promotion_unit_unit_fk` FOREIGN KEY (`unit_fk`) REFERENCES `unit` (`id`)
);


-- `schooltool-api`.skill definition

CREATE TABLE IF NOT EXISTS `skill` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `class_fk` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`(191)),
  UNIQUE KEY `name`(`name`(191)),
  KEY `skill_class_key` (`class_fk`),
  CONSTRAINT `skill_class_fk` FOREIGN KEY (`class_fk`) REFERENCES `class` (`id`) ON DELETE CASCADE
);


-- `schooltool-api`.unit_goal definition

CREATE TABLE IF NOT EXISTS `unit_goal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` int(11) NOT NULL,
  `skill_fk` int(11) NOT NULL,
  `unit_fk` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `unit_goal_skill_fk` (`skill_fk`),
  KEY `unit_goal_unit_fk` (`unit_fk`),
  CONSTRAINT `unit_goal_skill_fk` FOREIGN KEY (`skill_fk`) REFERENCES `skill` (`id`) ON DELETE CASCADE,
  CONSTRAINT `unit_goal_unit_fk` FOREIGN KEY (`unit_fk`) REFERENCES `unit` (`id`) ON DELETE CASCADE
);


-- `schooltool-api`.applicant definition

CREATE TABLE IF NOT EXISTS `applicant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gender` enum('Madame','Monsieur') DEFAULT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `birthplace` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `address_extension` varchar(255) DEFAULT NULL,
  `postalcode` int(11) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `studies_level` enum('Infra Bac','Bac','Bac+1','Bac+2','Bac+3','Bac+4','Bac+5','Bac+6','Bac+7','Bac+8','> Bac+8') DEFAULT NULL,
  `studies` varchar(255) DEFAULT NULL,
  `situation` enum('Aucune','Chomage','Auto-entrepreneur','Etudiant','Salarie','Interim') DEFAULT NULL,
  `beneficiary` varchar(255) DEFAULT NULL,
  `qpv` tinyint(4) NOT NULL DEFAULT 0,
  `handicap` tinyint(4) NOT NULL DEFAULT 0,
  `status` enum('Prospect','Prospect KO','Entretien','Entretien - Abandon','Entretien - Refus','Hackathon','Hackathon - Abandon','Hackathon - Refus','Inscription','Inscription - KO','Inscrit','Demission','Reussi','Hors-Parcours','Liste d''attente') NOT NULL,
  `source` varchar(255) DEFAULT NULL,
  `NIR` varchar(255) DEFAULT NULL,
  `creation_date` date NOT NULL,
  `promotion_fk` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_promotion_fk` (`promotion_fk`),
  CONSTRAINT `applicant_promotion_fk` FOREIGN KEY (`promotion_fk`) REFERENCES `promotion` (`id`)
);


-- `schooltool-api`.followup definition

CREATE TABLE IF NOT EXISTS `followup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creation_date` datetime NOT NULL,
  `applicant_fk` int(11) NOT NULL,
  `author` varchar(255) NOT NULL,
  `comment` varchar(2048) NOT NULL,
  `type` enum('ADM','PEDA','RE','ALERTE') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_followup_fk` (`applicant_fk`),
  CONSTRAINT `applicant_followup_fk` FOREIGN KEY (`applicant_fk`) REFERENCES `applicant` (`id`) ON DELETE CASCADE
);


-- `schooltool-api`.job_skill definition

CREATE TABLE IF NOT EXISTS `job_skill` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `needed` float NOT NULL,
  `earned` float NOT NULL,
  `skill_fk` int(11) NOT NULL,
  `job_fk` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `skill_fk` (`skill_fk`,`job_fk`),
  KEY `job_skill_job_key` (`job_fk`),
  KEY `job_skill_skill_key` (`skill_fk`),
  CONSTRAINT `job_skill_job_fk` FOREIGN KEY (`job_fk`) REFERENCES `job` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_skill_skill_fk` FOREIGN KEY (`skill_fk`) REFERENCES `skill` (`id`) ON DELETE CASCADE
);


-- `schooltool-api`.promotion_history definition

CREATE TABLE IF NOT EXISTS `promotion_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `applicant_fk` int(11) NOT NULL,
  `promotion_fk` int(11) NOT NULL,
  `date` date NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promotion_history_applicant_fk` (`applicant_fk`),
  KEY `promotion_history_promotion_fk` (`promotion_fk`),
  CONSTRAINT `promotion_history_applicant_fk` FOREIGN KEY (`applicant_fk`) REFERENCES `applicant` (`id`) ON DELETE CASCADE,
  CONSTRAINT `promotion_history_promotion_fk` FOREIGN KEY (`promotion_fk`) REFERENCES `promotion` (`id`) ON DELETE CASCADE
);


-- `schooltool-api`.student definition

CREATE TABLE IF NOT EXISTS `student` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `applicant_fk` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `current_unit_fk` int(11) NOT NULL,
  `github` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `CV` varchar(255) DEFAULT NULL,
  `plesk` varchar(255) DEFAULT NULL,
  `personal_website` varchar(255) DEFAULT NULL,
  `badge` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_login` (`email`(191)),
  KEY `student_current_unit_key` (`current_unit_fk`),
  KEY `student_applicant_fk` (`applicant_fk`),
  CONSTRAINT `student_applicant_fk` FOREIGN KEY (`applicant_fk`) REFERENCES `applicant` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_current_unit_fk` FOREIGN KEY (`current_unit_fk`) REFERENCES `unit` (`id`)
);


-- `schooltool-api`.unit_completed definition

CREATE TABLE IF NOT EXISTS `unit_completed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `completion_date` datetime NOT NULL,
  `student_fk` int(11) NOT NULL,
  `unit_fk` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `unit_completed_student_fk` (`student_fk`),
  KEY `unit_completed_unit_fk` (`unit_fk`),
  CONSTRAINT `unit_completed_student_fk` FOREIGN KEY (`student_fk`) REFERENCES `student` (`id`) ON DELETE CASCADE,
  CONSTRAINT `unit_completed_unit_fk` FOREIGN KEY (`unit_fk`) REFERENCES `unit` (`id`) ON DELETE CASCADE
);


-- `schooltool-api`.unit_history definition

CREATE TABLE IF NOT EXISTS `unit_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_fk` int(11) NOT NULL,
  `unit_fk` int(11) NOT NULL,
  `date` date NOT NULL,
  `author` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `unit_history_student_fk` (`student_fk`),
  KEY `unit_history_unit_fk` (`unit_fk`),
  CONSTRAINT `unit_history_student_fk` FOREIGN KEY (`student_fk`) REFERENCES `student` (`id`) ON DELETE CASCADE,
  CONSTRAINT `unit_history_unit_fk` FOREIGN KEY (`unit_fk`) REFERENCES `unit` (`id`) ON DELETE CASCADE
);


-- `schooltool-api`.unit_viewer definition

CREATE TABLE IF NOT EXISTS `unit_viewer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_fk` int(11) NOT NULL,
  `student_fk` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unit_fk` (`unit_fk`,`student_fk`),
  KEY `unit_viewer_student_key` (`student_fk`),
  KEY `unit_viewer_unit_key` (`unit_fk`),
  CONSTRAINT `unit_viewer_student_fk` FOREIGN KEY (`student_fk`) REFERENCES `student` (`id`) ON DELETE CASCADE,
  CONSTRAINT `unit_viewer_unit_fk` FOREIGN KEY (`unit_fk`) REFERENCES `unit` (`id`) ON DELETE CASCADE
);


-- `schooltool-api`.activity_attendance definition

CREATE TABLE IF NOT EXISTS `activity_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_fk` int(11) NOT NULL,
  `activity_fk` int(11) NOT NULL,
  `is_present` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_attendance_activity_FK` (`activity_fk`),
  KEY `activity_attendance_student_FK` (`student_fk`),
  CONSTRAINT `activity_attendance_activity_FK` FOREIGN KEY (`activity_fk`) REFERENCES `activity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activity_attendance_student_FK` FOREIGN KEY (`student_fk`) REFERENCES `student` (`id`)
);


-- `schooltool-api`.alternance definition

CREATE TABLE IF NOT EXISTS `alternance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_type` enum('Apprentissage','Professionnalisation') DEFAULT NULL,
  `OPCO` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `payment_for` varchar(100) DEFAULT NULL,
  `cost_per_hour` float DEFAULT NULL,
  `OPCO_number` varchar(100) DEFAULT NULL,
  `DRETS_number` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `tutor_firstname` varchar(100) DEFAULT NULL,
  `tutor_lastname` varchar(100) DEFAULT NULL,
  `tutor_email` varchar(100) DEFAULT NULL,
  `student_fk` int(11) NOT NULL,
  `filiz_folder_id` varchar(10) DEFAULT NULL,
  `status` enum('Actif','Inactif') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alternance_FK` (`student_fk`),
  CONSTRAINT `alternance_student_FK` FOREIGN KEY (`student_fk`) REFERENCES `student` (`id`) ON DELETE CASCADE
);


-- `schooltool-api`.invoice definition

CREATE TABLE IF NOT EXISTS `invoice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `reference` varchar(100) NOT NULL,
  `link` text NOT NULL,
  `author` varchar(100) NOT NULL,
  `creation_date` date NOT NULL,
  `amount` int(11) NOT NULL,
  `hours` int(11) NOT NULL,
  `alternance_fk` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_alternance_FK` (`alternance_fk`),
  CONSTRAINT `invoice_alternance_FK` FOREIGN KEY (`alternance_fk`) REFERENCES `alternance` (`id`)
);


-- `schooltool-api`.logtime definition

CREATE TABLE IF NOT EXISTS `logtime` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `student_fk` int(11) NOT NULL,
  `day` date NOT NULL,
  `algo1` int(11) NOT NULL,
  `algo2` int(11) NOT NULL,
  `algo3` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `logtime_student_fk` (`student_fk`),
  CONSTRAINT `logtime_student_fk` FOREIGN KEY (`student_fk`) REFERENCES `student` (`id`) ON DELETE CASCADE
);


-- `schooltool-api`.registration definition

CREATE TABLE IF NOT EXISTS `registration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `group_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `is_lead` tinyint(1) NOT NULL,
  `is_valid` tinyint(1) NOT NULL DEFAULT 0,
  `is_done` tinyint(1) NOT NULL DEFAULT 0,
  `is_complete` tinyint(1) NOT NULL DEFAULT 0,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `click_date` datetime DEFAULT NULL,
  `correction_date` datetime DEFAULT NULL,
  `lead_fk` int(11) DEFAULT NULL,
  `member_fk` int(11) NOT NULL,
  `job_fk` int(11) NOT NULL,
  `comment` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `corrector` varchar(255) DEFAULT 'deepthought@laplateforme.io',
  `is_success` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_fk` (`member_fk`,`job_fk`),
  KEY `registration_lead_key` (`lead_fk`),
  KEY `registration_member_key` (`member_fk`),
  KEY `registration_job_key` (`job_fk`),
  CONSTRAINT `registration_job_fk` FOREIGN KEY (`job_fk`) REFERENCES `job` (`id`) ON DELETE CASCADE,
  CONSTRAINT `registration_lead_fk` FOREIGN KEY (`lead_fk`) REFERENCES `student` (`id`) ON DELETE SET NULL,
  CONSTRAINT `registration_member_fk` FOREIGN KEY (`member_fk`) REFERENCES `student` (`id`) ON DELETE CASCADE
);


-- `schooltool-api`.waiting_list definition

CREATE TABLE IF NOT EXISTS `waiting_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_fk` int(11) NOT NULL,
  `registration_fk` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_fk` (`student_fk`,`registration_fk`),
  KEY `waiter` (`student_fk`),
  KEY `group` (`registration_fk`),
  CONSTRAINT `waiting_list_registration_fk` FOREIGN KEY (`registration_fk`) REFERENCES `registration` (`id`) ON DELETE CASCADE,
  CONSTRAINT `waiting_list_student_fk` FOREIGN KEY (`student_fk`) REFERENCES `student` (`id`) ON DELETE CASCADE
);


-- `schooltool-api`.acquiered_skill definition

CREATE TABLE IF NOT EXISTS `acquiered_skill` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(255) NOT NULL DEFAULT 'En cours',
  `job_skill_fk` int(11) NOT NULL,
  `registration_fk` int(11) NOT NULL,
  `student_fk` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `job_skill_fk` (`job_skill_fk`,`student_fk`),
  KEY `acquiered_skill_job_skill_key` (`job_skill_fk`),
  KEY `acquiered_skill_registration_key` (`registration_fk`),
  KEY `acquiered_skill_student_key` (`student_fk`),
  CONSTRAINT `acquiered_skill_job_skill_fk` FOREIGN KEY (`job_skill_fk`) REFERENCES `job_skill` (`id`) ON DELETE CASCADE,
  CONSTRAINT `acquiered_skill_registration_fk` FOREIGN KEY (`registration_fk`) REFERENCES `registration` (`id`) ON DELETE CASCADE,
  CONSTRAINT `acquiered_skill_student_fk` FOREIGN KEY (`student_fk`) REFERENCES `student` (`id`) ON DELETE CASCADE
);