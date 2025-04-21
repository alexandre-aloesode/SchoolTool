USE `schooltool_api`;

-- SECTION
INSERT INTO `section` (name) VALUES ('Bachelor IT');

-- PROMOTION
INSERT INTO `promotion`
(name, `year`, section_fk, is_active, formation_type, start_date, end_date, duration, certification)
VALUES (
  'Marseille - B3 IA - 2025/26', '2025', '1', 1, 1,
  '2025-09-01', '2026-08-10', 305,
  'Développeur en intelligence artificielle'
);

-- APPLICANTS
INSERT INTO `applicant`
(gender, firstname, lastname, birthdate, birthplace, email, phone, address, address_extension, postalcode, city,
 studies_level, studies, situation, beneficiary, qpv, handicap, status, source, NIR, creation_date, promotion_fk)
VALUES
('Monsieur', 'Hervé', 'Beziat', '2000-01-21', 'Marseille', 'herve@gmail.com', '0606060606', '12 rue exemple', NULL,
 '13002', 'Marseille', 7, 'marketing', 'Chomage', '1111111111', 0, 0, 9, NULL, NULL, NOW(), 1),

('Madame', 'Julie', 'Lambert', '2000-01-21', 'Marseille', 'julie@gmail.com', '0606060606', '12 rue exemple', NULL,
 '13002', 'Marseille', 7, 'marketing', 'Chomage', '1111111111', 0, 0, 9, NULL, NULL, NOW(), 1);

-- UNITS
INSERT INTO `unit`
(code, name, is_active, start_date, end_date)
VALUES ('Marseille - B2 IA 2024 - Science des données', 'Science des données', 1, '2025-09-01', '2026-01-01');

-- STUDENTS
INSERT INTO `student`
(applicant_fk, email, current_unit_fk, github, linkedin, CV, plesk, personal_website, badge)
VALUES
(1, 'herve.beziat@laplateforme.io', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'julie.lambert@laplateforme.io', 1, NULL, NULL, NULL, NULL, NULL, NULL);

-- CLASSES
INSERT INTO `class` (name) VALUES ('Architecture'), ('Intelligence Artificielle');

-- SKILLS
INSERT INTO `skill` (code, name, class_fk) VALUES
('Archi Proj', 'Architecture de Projet', 1),
('Admin Réseau', 'Administration réseau', 2);

-- UNIT VIEWER
INSERT INTO `unit_viewer` (unit_fk, student_fk) VALUES (1, 1);

-- UNIT GOALS
INSERT INTO `unit_goal` (value, skill_fk, unit_fk) VALUES
(20, 1, 1),
(30, 2, 1);

-- CALENDAR
INSERT INTO `calendar` (promotion_fk, name, status) VALUES (1, 'Marseille - B3 IA - 2025/26', 1);

-- JOBS
INSERT INTO `job`
(code, name, duration, min_students, max_students, link_subject, link_tutor_guide, description, is_visible, unit_fk)
VALUES
(
  'CS1-RT1J1', 'RunTrack 1 - Jour 1', 1, 1, 1,
  'https://drive.google.com/file/d/1mi5Du1S_fzbDuSF6EQ3Q_CVPwNmFqFZ5/view?usp=sharing',
  'https://docs.google.com/document/d/1xxnZlENd-8qu4vF9p69SuiVG_4VcuQJbtFrUX9BAfEk/edit?usp=sharing',
  'Jour 1 de la RunTrack 1', 1, 1
),
(
  'PP2-15_22', 'Boutique en ligne', 14, 3, 4,
  'https://drive.google.com/file/d/1g60XtXXQloxNuZY9AQt0X0XqT3cHz6Ac/view?usp=sharing',
  'https://drive.google.com/file/d/1g60XtXXQloxNuZY9AQt0X0XqT3cHz6Ac/view?usp=sharing',
  'Développez une boutique en ligne', 1, 1
);

-- REGISTRATION
INSERT INTO `registration`
(group_id, group_name, is_lead, is_valid, is_done, is_complete, start_date, end_date, click_date, correction_date,
 lead_fk, member_fk, job_fk, comment, corrector, is_success)
VALUES (
  1, 'Group 1', 1, 1, 1, 0,
  '2025-09-23 07:46:15', '2025-09-23 09:46:15', '2025-09-23 10:10:34', NULL,
  1, 1, 1, NULL, NULL, 0
);
