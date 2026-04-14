SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE IF NOT EXISTS `test` CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci;
USE `test`;

CREATE TABLE IF NOT EXISTS `zoo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `motto` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

TRUNCATE `zoo`;

CREATE TABLE IF NOT EXISTS `animal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `weight` int(5) NOT NULL,
  `birth` datetime NOT NULL,
  `parameters` varchar(150) NOT NULL,
  `death` datetime DEFAULT NULL,
  `height` int(5) NOT NULL,
  `vaccinated` tinyint(1) NOT NULL,
  `price` float NOT NULL DEFAULT '0',
  `type` varchar(10) DEFAULT NULL,
  `zoo_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `animal_zoo_id` FOREIGN KEY (`zoo_id`) REFERENCES `zoo` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

TRUNCATE `animal`;
