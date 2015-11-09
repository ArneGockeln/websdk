-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost:8889
-- Erstellungszeit: 07. Nov 2015 um 23:13
-- Server-Version: 5.5.42
-- PHP-Version: 5.6.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `websdk`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `websdk_options`
--

CREATE TABLE `websdk_options` (
  `id` bigint(20) NOT NULL,
  `option_category` varchar(64) NOT NULL DEFAULT 'default',
  `option_key` varchar(64) NOT NULL,
  `option_value` longtext NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `websdk_options`
--

INSERT INTO `websdk_options` (`id`, `option_category`, `option_key`, `option_value`) VALUES
(10, 'default', 'core_session_time_limit', '30'),
(11, 'default', 'core_mailer_from', 'WebSDK'),
(12, 'default', 'core_mailer_from_email', 'hello@domain.de'),
(13, 'default', 'core_mailer_reply_email', 'reply@domain.de'),
(14, 'default', 'core_mailer_admin_email', 'hello@domain.de'),
(15, 'default', 'core_mailer_smtp_host', ''),
(16, 'default', 'core_mailer_smtp_user', ''),
(17, 'default', 'core_mailer_smtp_password', ''),
(18, 'api', 'core_api_allowed_origins', 'http://localhost');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `websdk_sessions`
--

CREATE TABLE `websdk_sessions` (
  `session_id` varchar(32) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `lastactivity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `websdk_users`
--

CREATE TABLE `websdk_users` (
  `id` bigint(20) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'UserTypeEnum',
  `firstname` varchar(60) DEFAULT NULL,
  `lastname` varchar(60) DEFAULT NULL,
  `username` varchar(60) NOT NULL,
  `email` varchar(255) NOT NULL,
  `locale` varchar(6) NOT NULL DEFAULT 'de_DE',
  `rights` text,
  `pwd` varchar(255) NOT NULL,
  `salt` varchar(10) DEFAULT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `lastmod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `websdk_users`
--

INSERT INTO `websdk_users` (`id`, `type`, `firstname`, `lastname`, `username`, `email`, `locale`, `rights`, `pwd`, `salt`, `locked`, `deleted`, `lastmod`) VALUES
(1, 0, 'Web', 'Chef', 'admin', 'hello@domain.de', 'de_DE', '1,2', 'e396350256d32a687a7bf456077b062d3abb368de5280593f3ebb70976d3edf1', 'jkDUiwy7lC', 0, 0, '2015-11-07 22:12:58');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `websdk_options`
--
ALTER TABLE `websdk_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `option_key` (`option_key`);

--
-- Indizes für die Tabelle `websdk_sessions`
--
ALTER TABLE `websdk_sessions`
  ADD UNIQUE KEY `uid` (`uid`);

--
-- Indizes für die Tabelle `websdk_users`
--
ALTER TABLE `websdk_users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `websdk_options`
--
ALTER TABLE `websdk_options`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT für Tabelle `websdk_users`
--
ALTER TABLE `websdk_users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
