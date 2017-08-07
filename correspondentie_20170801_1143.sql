-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 01, 2017 at 11:43 AM
-- Server version: 10.1.13-MariaDB
-- PHP Version: 5.6.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `correspondentie`
--

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

CREATE TABLE `post` (
  `ID` bigint(20) NOT NULL,
  `in_out` varchar(5) DEFAULT NULL,
  `kenmerk` varchar(10) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `their_name` varchar(255) DEFAULT NULL,
  `their_organisation` varchar(255) DEFAULT NULL,
  `our_name` varchar(255) DEFAULT NULL,
  `our_institute` varchar(255) DEFAULT NULL,
  `our_department` varchar(255) DEFAULT NULL,
  `type_of_document` int(11) DEFAULT NULL,
  `subject` text,
  `remarks` text,
  `registered_by` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `ID` bigint(20) NOT NULL,
  `property` varchar(50) NOT NULL,
  `value` text,
  `comment` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`ID`, `property`, `value`, `comment`) VALUES
(1, 'admin_email', 'gcu@iisg.nl', 'comma separated'),
(2, 'functional_maintainer_name', 'Nicole Christophe', NULL),
(3, 'functional_maintainer_email', 'nicole.christophe@bb.huc.knaw.nl', NULL),
(4, 'website_email', 'nicole.christophe@bb.huc.knaw.nl', NULL),
(5, 'website_email_sendername', 'Correspondentie Registratie Systeem', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `translations`
--

CREATE TABLE `translations` (
  `ID` int(11) NOT NULL,
  `property` varchar(50) NOT NULL,
  `lang_nl` text,
  `lang_en` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `translations`
--

INSERT INTO `translations` (`ID`, `property`, `lang_nl`, `lang_en`) VALUES
(1, 'website_name', 'Correspondentie Registratie Systeem', 'Correspondence Registration System'),
(2, 'contact', 'Contact & Vragen', 'Contact & Questions'),
(3, 'menu_postin', 'Post IN', 'Mail IN'),
(4, 'menu_postuit', 'Post UIT', 'Mail OUT'),
(5, 'menu_overzicht', 'Overzicht', 'List'),
(6, 'menu_zoeken', 'Zoeken', 'Search'),
(7, 'login_pagina', 'Login pagina', 'Login page'),
(8, 'please_log_in', 'Aub log in', 'Please log in'),
(9, 'your_login_credentials_are', '<br>\nJe kunt inloggen met je KNAW credentials.<br>', '<br>\r\nYou can log in with your KNAW credentials.<br>'),
(10, 'btn_login', 'Log in', 'Log in'),
(11, 'loginname_placeholder', 'KNAW loginnaam', 'KNAW loginname'),
(12, 'loginname_help', NULL, NULL),
(13, 'loginname', 'Loginnaam', 'Login name'),
(14, 'password', 'Wachtwoord', 'Password'),
(15, 'password_placeholder', 'KNAW wachtwoord', 'KNAW password'),
(16, 'go_to', 'Ga naar', 'Go to'),
(17, 'btn_clear', 'Wis', 'Clear'),
(18, 'confirm', 'Aub bevestig uitloggen', 'Please confirm logout'),
(19, 'go_back', 'Ga terug', 'Go back'),
(20, 'in', 'In', 'In'),
(21, 'last_modified', 'Gewijzigd op', 'Last modified'),
(22, 'lbl_date', 'Datum', 'Date'),
(23, 'lbl_department', 'Afdeling', 'Department'),
(24, 'lbl_description', 'Omschrijving', 'Description'),
(25, 'lbl_email', 'E-mail', 'E-mail'),
(26, 'lbl_name', 'Naam', 'Name'),
(27, 'next', 'Volgende', 'Next'),
(28, 'no', 'Nee', 'No'),
(29, 'nothing_found', 'Niks gevonden.<br>Pas uw zoekopdracht aan.', 'Nothing found.<br>Please change your search criterium.'),
(30, 'or_go_to', 'of ga naar', 'or go to'),
(31, 'prev', 'Vorige', 'Prev'),
(32, 'questions_bugs_comments', 'Vragen, fouten, opmerkingen, ideeen, ... neem contact op met de functionele beheerder van deze applicatie ::NAME::.<br>\n', 'Questions, bugs, comments, ideas, ... please contact the functional maintainer of this application ::NAME::.<br>\n'),
(33, 'sorted_on', 'gesorteerd op', 'sorted on'),
(34, 'welcome', 'Hallo', 'Hallo'),
(35, 'yes', 'ja', 'yes'),
(36, 'login', 'inloggen', 'please log in');

-- --------------------------------------------------------

--
-- Table structure for table `type_of_document`
--

CREATE TABLE `type_of_document` (
  `ID` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '999',
  `is_disabled` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `type_of_document`
--

INSERT INTO `type_of_document` (`ID`, `type`, `sort_order`, `is_disabled`) VALUES
(1, 'Beleidsdocument', 10, 0),
(2, 'Vergaderstuk', 20, 0),
(3, 'Besluit', 30, 0),
(4, 'Overeenkomst', 40, 0),
(5, 'Verslag', 50, 0),
(6, 'Overige correspondentie', 60, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `loginname` varchar(50) NOT NULL,
  `authentication_server` varchar(50) NOT NULL,
  `password_hash` varchar(50) NOT NULL,
  `language` varchar(10) DEFAULT NULL,
  `is_disabled` tinyint(4) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `loginname`, `authentication_server`, `password_hash`, `language`, `is_disabled`, `is_deleted`) VALUES
(1, 'admin', 'local', '$2a$10$Qar3PPPujbxBAI1A1oaUueRT42u69VEkrwigatw1pTd', 'nl', 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `type_of_document`
--
ALTER TABLE `type_of_document`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `post`
--
ALTER TABLE `post`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `translations`
--
ALTER TABLE `translations`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
--
-- AUTO_INCREMENT for table `type_of_document`
--
ALTER TABLE `type_of_document`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
