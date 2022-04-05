-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 01-Mar-2022 às 12:48
-- Versão do servidor: 5.7.17
-- PHP Version: 7.1.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `contacts`
--

CREATE TABLE `contacts` (
  `Id` int(10) UNSIGNED NOT NULL,
  `AreaCode` int(11) NOT NULL,
  `PhoneNumber` int(11) NOT NULL,
  `IdStudents` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Extraindo dados da tabela `contacts`
--

INSERT INTO `contacts` (`Id`, `AreaCode`, `PhoneNumber`, `IdStudents`) VALUES
(1, 11, 34415450, 1),
(2, 12, 34313880, 2),
(3, 47, 999427251, 1),
(4, 48, 997639352, 2);

-- --------------------------------------------------------

--
-- Estrutura da tabela `students`
--

CREATE TABLE `students` (
  `Id` int(10) UNSIGNED NOT NULL,
  `Name` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `Identification` bigint(20) NOT NULL,
  `Email` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `RecordDate` datetime NOT NULL,
  `ZipCode` int(11) DEFAULT NULL,
  `ChangeDate` datetime DEFAULT NULL,
  `State` enum('AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO') COLLATE latin1_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Extraindo dados da tabela `students`
--

INSERT INTO `students` (`Id`, `Name`, `Identification`, `Email`, `RecordDate`, `ZipCode`, `ChangeDate`, `State`) VALUES
(1, 'Paulo Ponick', 76659036067, 'papo@serv.org.br', '2021-09-06 15:00:00', 89237276, NULL, 'SC'),
(2, 'Rodrigo Soares', 97909417063, 'roso@serv.org.br', '2021-09-06 15:15:00', 89227067, NULL, 'SP'),
(3, 'Daniel Costa', 13404565088, 'daco@serv.org.br', '2021-09-06 15:30:00', 89220230, NULL, 'PR'),
(4, 'Jorge Souza', 79547219065, 'joso@serv.org.br', '2021-09-06 16:00:00', 89219168, NULL, 'AC');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `AreaCode` (`AreaCode`),
  ADD KEY `PhoneNumber` (`PhoneNumber`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Identification` (`Identification`),
  ADD KEY `ZipCode` (`ZipCode`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `Id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `Id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
