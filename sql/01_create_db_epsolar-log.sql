-- --------------------------------------------------------
-- Host:                         192.168.50.210
-- Server Version:               10.0.28-MariaDB-2+b1 - Raspbian testing-staging
-- Server Betriebssystem:        debian-linux-gnueabihf
-- HeidiSQL Version:             10.2.0.5599
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Exportiere Datenbank Struktur für epsolar_log
DROP DATABASE IF EXISTS `epsolar_log`;
CREATE DATABASE IF NOT EXISTS `epsolar_log` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `epsolar_log`;

-- Exportiere Struktur von Tabelle epsolar_log.tbl_reading
DROP TABLE IF EXISTS `tbl_reading`;
CREATE TABLE IF NOT EXISTS `tbl_reading` (
  `pk_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `con-temp-main` float NOT NULL COMMENT 'Main Temp of the controller in °C',
  `con-temp-heatsink` float NOT NULL COMMENT 'Temp of the controllers heatsink in °C',
  `pv-voltage` float NOT NULL COMMENT 'Voltage of the PV-Generator in V',
  `pv-current` float NOT NULL COMMENT 'Current of the PV-Generator in A',
  `pv-power` float NOT NULL COMMENT 'Power of the PV-Generator in W',
  `bat-voltage` float NOT NULL COMMENT 'Voltage of the Battery in V',
  `bat-current` float NOT NULL COMMENT 'Current of the Battery in A',
  `bat-power` float NOT NULL COMMENT 'Power of the Battery in W',
  `bat-temp` float NOT NULL COMMENT 'Temprature of the Battery in °C',
  `bat-perc` float NOT NULL COMMENT 'Charging-state of the Battery in %',
  `load-voltage` float NOT NULL COMMENT 'Voltage at the LOAD-Output in V',
  `load-current` float NOT NULL COMMENT 'Current at the LOAD-Output in A',
  `load-power` float NOT NULL COMMENT 'Power at the LOAD-Output in W',
  PRIMARY KEY (`pk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Exportiere Daten aus Tabelle epsolar_log.tbl_reading: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `tbl_reading` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_reading` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
