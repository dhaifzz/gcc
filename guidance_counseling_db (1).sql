-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 23, 2025 at 03:40 PM
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
-- Database: `guidance_counseling_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `appointment_type` enum('counseling','assessment') NOT NULL,
  `client_category` enum('outside_client','faculty','high_school_student','college_student') NOT NULL,
  `assessment_type` enum('high_school','college') DEFAULT NULL,
  `requested_date` date NOT NULL,
  `requested_time` time NOT NULL,
  `status` enum('pending','approved','rescheduled','completed','canceled') DEFAULT 'pending',
  `staff_id` int(11) DEFAULT NULL,
  `director_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shifting_exam_registrations`
--

CREATE TABLE `shifting_exam_registrations` (
  `exam_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `school_id` varchar(9) NOT NULL,
  `sex` enum('Male','Female','Other') NOT NULL,
  `college` varchar(100) NOT NULL,
  `current_course` varchar(100) NOT NULL,
  `desired_course` varchar(100) NOT NULL,
  `reason` text NOT NULL,
  `photo` varchar(255) NOT NULL,
  `grades` varchar(255) NOT NULL,
  `cet_result` varchar(255) NOT NULL,
  `school_id_file` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `civil_status` varchar(20) NOT NULL,
  `grade_level_course` varchar(100) NOT NULL,
  `wmsu_id` varchar(50) DEFAULT NULL,
  `role` enum('admin','staff','director','faculty','college_student','high_school_student','outside_client','employee') NOT NULL,
  `address` text NOT NULL,
  `sex` enum('Male','Female','Other') NOT NULL,
  `age` int(11) NOT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `middle_name`, `last_name`, `contact_number`, `civil_status`, `grade_level_course`, `wmsu_id`, `role`, `address`, `sex`, `age`, `occupation`, `email`, `password`, `created_at`) VALUES
(8, 'dhaif', 'a', 'labang', '097745351427', 'single', 'js', '122334567', 'high_school_student', 'San Jose Cawa-cawa', 'Male', 20, 'tambay', 'we@gmail.com', '$2y$10$n/5gKi0tOANNbQtlOLt.d.OMgwihtJox7EAOT.UafQNqDooB4Yn26', '2025-03-15 14:50:33'),
(9, 'digong', 'ruwa', 'dutirte', '97745351427', 'single', 'senior high school', '123456789', 'high_school_student', 'sub saharan', 'Female', 80, 'formir presidint', 'wr@gmail.com', '$2y$10$vamfigVkfwlzrIs6w9zmnOnn1JijFE20IdAsDWjmR/YNf//Q5jVhu', '2025-03-15 15:03:52'),
(12, 'Ralph', 'monzales', 'Candido', '09774531011', 'single', 'junior high school', '1234567899', 'outside_client', 'San Jose Cawa-cawa', 'Male', 20, 'tambay', 'ralphmonzales665@gmail.com', '$2y$10$Wci3RUngJvK6POhS9NlhO.iTq8vQpjIX/JwoPZCqjIERbYCuTbxVC', '2025-03-16 05:35:55'),
(13, 'dhaif', 'a', 'labang', '097745351427', 'single', 'junior high school', '1234', 'outside_client', 'sub saharan', 'Male', 80, 'formir presidint', 'wy@gmail.com', '$2y$10$YvXNsqGj36nQDDBgRG/dfeRlKyBJ153IgEP4gA6PkHAOaxZm4zfrO', '2025-03-16 05:37:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `director_id` (`director_id`);

--
-- Indexes for table `shifting_exam_registrations`
--
ALTER TABLE `shifting_exam_registrations`
  ADD PRIMARY KEY (`exam_id`),
  ADD UNIQUE KEY `school_id` (`school_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `wmsu_id` (`wmsu_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shifting_exam_registrations`
--
ALTER TABLE `shifting_exam_registrations`
  MODIFY `exam_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`director_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `shifting_exam_registrations`
--
ALTER TABLE `shifting_exam_registrations`
  ADD CONSTRAINT `shifting_exam_registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
