CREATE DATABASE IF NOT EXISTS `schooltool_auth` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `schooltool_auth`;

-- log table
CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `role` int(11) DEFAULT NULL,
  `scope` int(11) DEFAULT NULL,
  `params` varchar(255) DEFAULT NULL,
  `method` varchar(100) NOT NULL,
  `date` datetime NOT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- role table
CREATE TABLE `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);

-- user table
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_scope_fkey` (`role_id`),
  CONSTRAINT `user_scope_fkey` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`)
);

-- scope table
CREATE TABLE `scope` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `scope_value` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_unit` (`user_id`,`scope_value`),
  UNIQUE KEY `user_id` (`user_id`,`scope_value`),
  CONSTRAINT `scope_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
);

-- secret table
CREATE TABLE `secret` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `secret` varchar(255) NOT NULL,
  `issue_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `user_id_fk` (`user_id`) USING BTREE,
  CONSTRAINT `secret_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
);
