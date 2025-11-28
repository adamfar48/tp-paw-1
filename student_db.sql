-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 27 nov. 2025 à 10:00
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `student_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `session_id` int NOT NULL,
  `status` enum('present','absent') COLLATE utf8mb4_general_ci NOT NULL,
  `date` date NOT NULL,
  `session` int NOT NULL DEFAULT '1',
  `participation` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `session_id` (`session_id`)
) ENGINE=MyISAM AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `session_id`, `status`, `date`, `session`, `participation`) VALUES
(70, 7, 0, 'present', '2025-11-27', 1, 1),
(69, 6, 0, 'absent', '2025-11-27', 1, 0),
(68, 5, 0, 'present', '2025-11-27', 1, 1),
(67, 4, 0, 'present', '2025-11-27', 1, 1),
(66, 3, 0, 'present', '2025-11-27', 1, 1),
(65, 2, 0, 'present', '2025-11-27', 1, 0),
(64, 7, 0, 'present', '2025-11-26', 1, 1),
(63, 6, 0, 'absent', '2025-11-26', 1, 0),
(62, 5, 0, 'present', '2025-11-26', 1, 1),
(61, 4, 0, 'present', '2025-11-26', 1, 1),
(60, 3, 0, 'present', '2025-11-26', 1, 0),
(59, 2, 0, 'absent', '2025-11-26', 1, 0);

-- --------------------------------------------------------

--
-- Structure de la table `attendance_sessions`
--

DROP TABLE IF EXISTS `attendance_sessions`;
CREATE TABLE IF NOT EXISTS `attendance_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `group_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `date` date NOT NULL,
  `opened_by` int NOT NULL,
  `status` enum('open','closed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'open',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `attendance_sessions`
--

INSERT INTO `attendance_sessions` (`id`, `course_id`, `group_id`, `date`, `opened_by`, `status`) VALUES
(1, 0, '3', '2025-11-25', 0, 'closed'),
(2, 0, '3', '2025-11-25', 0, 'open'),
(3, 0, '3', '2025-11-25', 0, 'closed'),
(4, 0, '3', '2025-11-25', 0, 'open'),
(5, 0, '3', '2025-11-25', 0, '');

-- --------------------------------------------------------

--
-- Structure de la table `students`
--

DROP TABLE IF EXISTS `students`;
CREATE TABLE IF NOT EXISTS `students` (
  `student_id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `matricule` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `group_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `matricule` (`matricule`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `students`
--

INSERT INTO `students` (`student_id`, `fullname`, `matricule`, `group_id`) VALUES
(7, 'abdelali', '25678646', '3'),
(2, 'oussama', '26587765', '3'),
(3, 'adam far', '456789765', '3'),
(4, 'sam lafoufou', '26587743', '3'),
(5, 'aimen djouahra', '2568976', '3'),
(6, 'yasser', '234566', '3');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
