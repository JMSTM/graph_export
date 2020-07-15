-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 15, 2020 at 08:02 AM
-- Server version: 10.4.10-MariaDB
-- PHP Version: 7.3.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `stm`
--

-- --------------------------------------------------------

--
-- Table structure for table `metrics`
--

DROP TABLE IF EXISTS `metrics`;
CREATE TABLE IF NOT EXISTS `metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_metric` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `ts` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=40922 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `metrics_name`
--

DROP TABLE IF EXISTS `metrics_name`;
CREATE TABLE IF NOT EXISTS `metrics_name` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `metrics_name`
--

INSERT INTO `metrics_name` (`id`, `name`, `description`) VALUES
(1, 'jobs_number', 'Number of jobs in prod wiki mpu');

-- --------------------------------------------------------

--
-- Table structure for table `site_url`
--

DROP TABLE IF EXISTS `site_url`;
CREATE TABLE IF NOT EXISTS `site_url` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `site_url`
--

INSERT INTO `site_url` (`id`, `url`, `enabled`) VALUES
(1, 'https://qa-wiki.st.com/stm32mpu-New-reference', 1),
(2, 'https://wiki.st.com/stm32mpu', 1),
(7, 'https://wiki.st.com/stm32mcu', 1),
(8, 'https://qa-wiki.st.com/stm32mpu/api.php?format=json&context=%7B%22wgAction%22%3A%22view%22%2C%22wgArticleId%22%3A0%2C%22wgCanonicalNamespace%22%3A%22Special%22%2C%22wgCanonicalSpecialPageName%22%3A%22QMOverview%22%2C%22wgCurRevisionId%22%3A0%2C%22wgNamespaceNumber%22%3A-1%2C%22wgPageName%22%3A%22Special%3AQM_overview%22%2C%22wgRedirectedFrom%22%3Anull%2C%22wgRelevantPageName%22%3A%22Special%3AQM_overview%22%2C%22wgTitle%22%3A%22QM%20overview%22%7D&limit=25&action=bs-flaggedpages-store&page=1&start=0&sort=%5B%7B%22property%22%3A%22page_title%22%2C%22direction%22%3A%22ASC%22%7D%5D', 1);

-- --------------------------------------------------------

--
-- Table structure for table `speed`
--

DROP TABLE IF EXISTS `speed`;
CREATE TABLE IF NOT EXISTS `speed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL,
  `delay` float NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `ts` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1485804 DEFAULT CHARSET=latin1;
COMMIT;
