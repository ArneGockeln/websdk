-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 09. Apr 2014 um 19:39
-- Server Version: 5.5.25
-- PHP-Version: 5.4.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `webchef_webarbyte`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `webchef_users`
--

CREATE TABLE `webchef_users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(60) DEFAULT NULL,
  `lastname` varchar(60) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `pwd` varchar(255) NOT NULL,
  `locale` varchar(6) NOT NULL DEFAULT 'de_DE',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `roles` tinytext,
  `lastmod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `webchef_users`
--

INSERT INTO `webchef_users` (`id`, `firstname`, `lastname`, `email`, `pwd`, `locale`, `locked`, `roles`, `lastmod`) VALUES
(1, 'Ad', 'Min', 'you@yourdomain.com', '*4ACFE3202A5FF5CF467898FC58AAB1D615029441', 'en_EN', 0, '1', '2014-04-09 17:38:31');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `webchef_user_roles`
--

CREATE TABLE `webchef_user_roles` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `rights` tinytext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `webchef_user_roles`
--

INSERT INTO `webchef_user_roles` (`id`, `name`, `rights`) VALUES
(1, 'Admin', '0,1,2,3');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `webchef_user_sessions`
--

CREATE TABLE `webchef_user_sessions` (
  `session_id` varchar(32) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `user_locale` varchar(6) DEFAULT 'de_DE',
  `lastactivity` int(11) NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
