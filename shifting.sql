-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2025 at 03:18 AM
-- Server version: 11.4.5-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gcc-2`
--

-- --------------------------------------------------------

--
-- Table structure for table `shifting`
--

CREATE TABLE `shifting` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `current_course` varchar(255) NOT NULL DEFAULT 'None',
  `wmsu_id` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_to_shift` varchar(100) NOT NULL,
  `reason_to_shift` longtext NOT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `grades` varchar(255) DEFAULT NULL,
  `cor` varchar(255) DEFAULT NULL,
  `cet_result` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rescheduled') DEFAULT 'pending',
  `submitted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shifting`
--

INSERT INTO `shifting` (`id`, `first_name`, `middle_name`, `last_name`, `current_course`, `wmsu_id`, `user_id`, `course_to_shift`, `reason_to_shift`, `picture`, `grades`, `cor`, `cet_result`, `status`, `submitted_at`) VALUES
(19, 'Hudhaifah', 'Abdul', 'Labang', 'Computer Science', '123456789', 13, 'Nursing', 'kapagod', 'uploads/IMG_20240909_125612_848.jpg', 'uploads/dapResume.pdf', 'uploads/COR-202420252.pdf', 'uploads/Chex_resume.pdf', 'pending', '2025-04-29 02:40:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `shifting`
--
ALTER TABLE `shifting`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shifting_ibfk_1` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `shifting`
--
ALTER TABLE `shifting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `shifting`
--
ALTER TABLE `shifting`
  ADD CONSTRAINT `shifting_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
