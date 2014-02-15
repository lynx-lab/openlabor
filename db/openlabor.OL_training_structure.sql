-- phpMyAdmin SQL Dump
-- version 3.5.8.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: Feb 15, 2014 alle 23:52
-- Versione del server: 5.5.34-0ubuntu0.13.04.1
-- Versione PHP: 5.4.9-4ubuntu2.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `openlabor_provider0`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `OL_training`
--

CREATE TABLE IF NOT EXISTS `OL_training` (
  `idTraining` int(11) NOT NULL AUTO_INCREMENT,
  `IdTrainingOriginal` int(11) NOT NULL,
  `nameTraining` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trainingCode` varchar(18) COLLATE utf8_unicode_ci NOT NULL,
  `company` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trainingAddress` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CAP` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `durationHours` int(11) DEFAULT NULL,
  `trainingType` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t_userType` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t_qualificationRequired` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t_longitude` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t_latitude` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t_dateInsert` int(11) NOT NULL,
  `hash` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `t_source` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `t_nation` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t_minAge` int(11) DEFAULT NULL,
  `t_maxAge` int(11) DEFAULT NULL,
  `t_price` int(8) DEFAULT NULL,
  `t_reservedForDisabled` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t_favoredCategoryRequests` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t_notes` text COLLATE utf8_unicode_ci,
  `t_expiration` int(11) NOT NULL,
  `t_linkMoreInfo` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t_media_url` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `t_locale` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `sourceTrainingName` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `sourceTrainingSurname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `t_published` int(1) NOT NULL,
  `t_idNode` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`idTraining`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1348 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
