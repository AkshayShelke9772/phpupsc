-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2019 at 08:34 AM
-- Server version: 10.3.16-MariaDB
-- PHP Version: 7.3.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `upscmcqs`
--

-- --------------------------------------------------------

--
-- Table structure for table `exam_ratings`
--

CREATE TABLE `exam_ratings` (
  `id` int(10) UNSIGNED NOT NULL,
  `exam_id` int(10) UNSIGNED NOT NULL,
  `rating` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `exam_ratings`
--

INSERT INTO `exam_ratings` (`id`, `exam_id`, `rating`, `user_id`, `created_date`) VALUES
(1, 1, 2, 3, '2019-10-05 16:53:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `exam_ratings`
--
ALTER TABLE `exam_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_EXAM_ID_RATING` (`exam_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `exam_ratings`
--
ALTER TABLE `exam_ratings`
  ADD CONSTRAINT `FK_EXAM_ID_RATING` FOREIGN KEY (`exam_id`) REFERENCES `exam_master` (`exam_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
