-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2024 at 01:06 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wellbee`
--

-- --------------------------------------------------------

--
-- Table structure for table `quests`
--

CREATE TABLE `quests` (
  `quest_id` int(11) NOT NULL,
  `descript` text NOT NULL,
  `coordinates` text NOT NULL,
  `points_reward` text NOT NULL,
  `name` text NOT NULL,
  `badge_reward_id` int(11) NOT NULL,
  `completed` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `quests`
--

INSERT INTO `quests` (`quest_id`, `descript`, `coordinates`, `points_reward`, `name`, `badge_reward_id`, `completed`) VALUES
(1, 'Go to Tesco and buy some Fridge Raiders', '55.763948, -4.153014', '100pts + 1 Fridge Raiders', 'Raiders of The Lost Fridge', 1, 0),
(2, 'Commit arson where the marker is (/srs)', '55.75923589020611, -4.150356716941361', '50pts + 1 Criminal record', 'Arson', 2, 0),
(3, 'Get buff at the gym', '55.76662743036916, -4.161172622363664', 'Swole', 'Exercise You Twat', 9, 0),
(4, 'Let\'s go have ourselves a jolly old time', '55.783083472636775, -4.161505428435749', '210pts', 'It\'s Golfin\' Time', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `uinfo`
--

CREATE TABLE `uinfo` (
  `steps_daily` int(11) NOT NULL,
  `miles_daily` double NOT NULL,
  `calories_daily` int(11) NOT NULL,
  `badge_acquired_ids` text NOT NULL,
  `steps_target` int(11) NOT NULL,
  `miles_target` int(11) NOT NULL,
  `calories_target` int(11) NOT NULL,
  `curent_quest_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `uinfo`
--

INSERT INTO `uinfo` (`steps_daily`, `miles_daily`, `calories_daily`, `badge_acquired_ids`, `steps_target`, `miles_target`, `calories_target`, `curent_quest_id`) VALUES
(123, 1.98, 444, '1,3,5', 1000, 2, 890, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `quests`
--
ALTER TABLE `quests`
  ADD PRIMARY KEY (`quest_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `quests`
--
ALTER TABLE `quests`
  MODIFY `quest_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
