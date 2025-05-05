-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for myhmsdb
CREATE DATABASE IF NOT EXISTS `myhmsdb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `myhmsdb`;

-- Dumping structure for table myhmsdb.admintb
CREATE TABLE IF NOT EXISTS `admintb` (
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table myhmsdb.appointmenttb
CREATE TABLE IF NOT EXISTS `appointmenttb` (
  `pid` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `fname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) DEFAULT NULL,
  `gender` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `doctor` varchar(100) DEFAULT NULL,
  `docFees` varchar(100) DEFAULT NULL,
  `appdate` varchar(100) DEFAULT NULL,
  `apptime` varchar(100) DEFAULT NULL,
  `userStatus` varchar(100) DEFAULT NULL,
  `doctorStatus` varchar(100) DEFAULT NULL,
  `reason` varchar(1000) DEFAULT NULL,
  `provider_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table myhmsdb.contact
CREATE TABLE IF NOT EXISTS `contact` (
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `message` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table myhmsdb.doctb
CREATE TABLE IF NOT EXISTS `doctb` (
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `spec` varchar(100) DEFAULT NULL,
  `docFees` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phoneno` varchar(100) DEFAULT NULL,
  `provider_type` enum('obstetrician_gynaecologist','midwife','general_practitioner','nurse','lab_technician') NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'Doctor',
  `qualification` varchar(100) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table myhmsdb.doctb_backup
CREATE TABLE IF NOT EXISTS `doctb_backup` (
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `spec` varchar(100) DEFAULT NULL,
  `docFees` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phoneno` varchar(100) DEFAULT NULL,
  `provider_type` enum('obstetrician_gynaecologist','midwife','general_practitioner','nurse','lab_technician') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table myhmsdb.health_education
CREATE TABLE IF NOT EXISTS `health_education` (
  `id` int NOT NULL AUTO_INCREMENT,
  `week` int NOT NULL COMMENT 'Pregnancy week this applies to (0-40)',
  `title` varchar(255) NOT NULL COMMENT 'Short title for the content',
  `content` text NOT NULL COMMENT 'Detailed education content',
  `category` enum('development','nutrition','exercise','warnings','general') NOT NULL DEFAULT 'general',
  `media_type` enum('none','image','video','document') DEFAULT 'none',
  `media_url` varchar(255) DEFAULT NULL COMMENT 'URL to associated media',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Featured content for quick access',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `week` (`week`),
  KEY `category` (`category`),
  KEY `is_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table myhmsdb.inventory
CREATE TABLE IF NOT EXISTS `inventory` (
  `mname` varchar(100) DEFAULT NULL,
  `spec` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `quantity` varchar(100) DEFAULT NULL,
  `mdate` varchar(100) DEFAULT NULL,
  `edate` varchar(100) DEFAULT NULL,
  `des` varchar(100) DEFAULT NULL,
  `id` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table myhmsdb.messages
CREATE TABLE IF NOT EXISTS `messages` (
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `doctor` varchar(100) DEFAULT NULL,
  `message` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table myhmsdb.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  `recipient_type` enum('all','patient','provider') NOT NULL,
  `recipient_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `expiration_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_recipient` (`recipient_type`,`recipient_id`),
  KEY `idx_notifications_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table myhmsdb.patient_assessments
CREATE TABLE IF NOT EXISTS `patient_assessments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pid` varchar(20) NOT NULL,
  `assessment_date` datetime NOT NULL,
  `blood_pressure_systolic` int DEFAULT NULL,
  `blood_pressure_diastolic` int DEFAULT NULL,
  `pulse` int DEFAULT NULL,
  `temperature` float DEFAULT NULL,
  `weight` float DEFAULT NULL,
  `height` float DEFAULT NULL,
  `bmi` float DEFAULT NULL,
  `blood_sugar` float DEFAULT NULL,
  `urine_protein` varchar(20) DEFAULT NULL,
  `urine_glucose` varchar(20) DEFAULT NULL,
  `haemoglobin` float DEFAULT NULL,
  `fetal_heart_rate` int DEFAULT NULL,
  `ultrasound_details` text,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table myhmsdb.patreg
CREATE TABLE IF NOT EXISTS `patreg` (
  `fname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) DEFAULT NULL,
  `gender` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `cpassword` varchar(100) DEFAULT NULL,
  `marital_status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `DOB` varchar(100) DEFAULT NULL,
  `national_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `emergency_contact` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `age` varchar(100) DEFAULT NULL,
  `medicalhis` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table myhmsdb.pregnancy_milestones
CREATE TABLE IF NOT EXISTS `pregnancy_milestones` (
  `milestone_id` int NOT NULL AUTO_INCREMENT,
  `week_range` varchar(10) NOT NULL,
  `baby_development` text NOT NULL,
  `moms_health` text NOT NULL,
  `education_tips` text NOT NULL,
  PRIMARY KEY (`milestone_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table myhmsdb.prenatal_reg
CREATE TABLE IF NOT EXISTS `prenatal_reg` (
  `reg_id` int NOT NULL AUTO_INCREMENT,
  `pid` int DEFAULT NULL,
  `lmp` date NOT NULL,
  `edc` date NOT NULL,
  `gravida` int NOT NULL,
  `parity` int NOT NULL,
  `blood_group` varchar(3) NOT NULL,
  `rh_factor` varchar(2) NOT NULL,
  `pregnancy_week` int DEFAULT NULL,
  `reg_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `national_id` int DEFAULT NULL,
  `Column 12` int DEFAULT NULL,
  PRIMARY KEY (`reg_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table myhmsdb.prestb
CREATE TABLE IF NOT EXISTS `prestb` (
  `doctor` varchar(100) DEFAULT NULL,
  `pid` varchar(100) DEFAULT NULL,
  `fname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `lname` varchar(100) DEFAULT NULL,
  `appdate` varchar(100) DEFAULT NULL,
  `apptime` varchar(100) DEFAULT NULL,
  `disease` varchar(100) DEFAULT NULL,
  `allergy` varchar(100) DEFAULT NULL,
  `prescription` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table myhmsdb.provider_types
CREATE TABLE IF NOT EXISTS `provider_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) NOT NULL,
  `description` text,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_name` (`type_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table myhmsdb.reminders
CREATE TABLE IF NOT EXISTS `reminders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pid` varchar(50) NOT NULL COMMENT 'Patient ID',
  `reminder_date` date NOT NULL COMMENT 'Date for the reminder',
  `reminder_time` time NOT NULL COMMENT 'Time for the reminder',
  `message` text NOT NULL COMMENT 'Reminder content',
  `status` enum('pending','completed','dismissed') NOT NULL DEFAULT 'pending',
  `category` enum('appointment','medication','test','general') NOT NULL DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `reminder_date` (`reminder_date`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
