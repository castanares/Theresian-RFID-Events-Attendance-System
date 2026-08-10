-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2025 at 04:43 PM
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
-- Database: `stc_event_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance_tbl`
--

CREATE TABLE `attendance_tbl` (
  `Attendance_ID` int(11) NOT NULL,
  `user_id` int(50) NOT NULL,
  `Event_ID` int(255) NOT NULL,
  `Event_name` varchar(255) NOT NULL,
  `Student_ID` int(50) NOT NULL,
  `Student_name` varchar(255) NOT NULL,
  `Course` varchar(255) NOT NULL,
  `Time_in` varchar(50) NOT NULL,
  `Time_out` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_tbl`
--

INSERT INTO `attendance_tbl` (`Attendance_ID`, `user_id`, `Event_ID`, `Event_name`, `Student_ID`, `Student_name`, `Course`, `Time_in`, `Time_out`) VALUES
(1, 1, 24, 'sample new123', 4226347, 'Castañares, Julian Falcon', 'BSIT', '2025-10-17 21:57:47', '2025-10-17 22:21:29'),
(2, 1, 24, 'sample new123', 4226351, 'Doe, John', 'BSHM', '2025-10-17 22:02:00', '2025-10-17 22:21:32'),
(3, 1, 24, 'sample new123', 4226348, 'Parker, Peter', 'BSIT', '2025-10-17 22:05:59', '2025-10-17 22:21:49'),
(4, 1, 24, 'sample new123', 123456, 'Stark, Tony', 'BSIT', '2025-10-17 22:06:03', '2025-10-17 22:21:46'),
(5, 1, 24, 'sample new123', 4226350, 'Taylor, David', 'BSCRIM', '2025-10-17 22:07:12', '2025-10-17 22:21:00'),
(6, 1, 24, 'sample new123', 4226544, 'Wayne, Damian', 'BSCRIM', '2025-10-17 22:07:14', '2025-10-17 22:21:14'),
(7, 1, 24, 'sample new123', 4226545, 'Zsasz, Victor', 'BSCRIM', '2025-10-17 22:07:17', '2025-10-17 22:21:12'),
(8, 1, 24, 'sample new001', 4226330, 'Kent, Clark', 'BSIT', '2025-10-17 22:17:27', '2025-10-17 22:21:21'),
(9, 1, 28, 'newest sample', 4226545, 'Zsasz, Victor', 'BSCRIM', '2025-10-17 22:32:15', '2025-10-17 22:54:19'),
(10, 1, 28, 'newest sample', 4226349, 'Reed, Richard', 'BSIT', '2025-10-17 22:33:27', ''),
(11, 1, 28, 'newest sample', 4226348, 'Parker, Peter', 'BSIT', '2025-10-17 22:33:31', ''),
(12, 1, 28, 'newest sample', 4226350, 'Taylor, David', 'BSCRIM', '2025-10-17 22:33:48', '2025-10-17 22:54:36'),
(13, 1, 28, 'newest sample', 4226347, 'Castañares, Julian Falcon', 'BSIT', '2025-10-17 22:33:58', '2025-10-17 22:54:32'),
(14, 1, 28, 'newest sample', 4226330, 'Kent, Clark', 'BSIT', '2025-10-17 22:34:05', ''),
(15, 1, 28, 'newest sample', 4226351, 'Doe, John', 'BSHM', '2025-10-17 22:34:10', ''),
(16, 1, 28, 'newest sample', 4226544, 'Wayne, Damian', 'BSCRIM', '2025-10-17 22:36:31', '2025-10-17 22:54:54'),
(17, 1, 15, 'panaghiusa 2025', 123456, 'Stark, Tony', 'BSIT', '2025-10-18 11:06:33', ''),
(18, 1, 15, 'panaghiusa 2025', 4226330, 'Kent, Clark', 'BSIT', '2025-10-18 11:06:47', ''),
(19, 1, 15, 'panaghiusa 2025', 4226347, 'Castañares, Julian Falcon', 'BSIT', '2025-10-18 11:06:51', ''),
(21, 1, 16, 'Intramurals', 4226348, 'Parker, Peter', 'BSIT', '2025-10-18 15:15:14', ''),
(22, 1, 16, 'Intramurals', 4226349, 'Reed, Richard', 'BSIT', '2025-10-18 15:15:49', ''),
(23, 1, 16, 'Intramurals', 4226330, 'Kent, Clark', 'BSIT', '2025-10-18 15:15:51', ''),
(24, 1, 16, 'Intramurals', 123456, 'Stark, Tony', 'BSIT', '2025-10-18 15:15:55', ''),
(25, 1, 16, 'Intramurals', 4226351, 'Doe, John', 'BSHM', '2025-10-18 15:15:58', ''),
(26, 1, 16, 'Intramurals', 4226347, 'Castañares, Julian Falcon', 'BSIT', '2025-10-18 15:16:00', ''),
(27, 1, 16, 'Intramurals', 4226350, 'Taylor, David', 'BSCRIM', '2025-10-18 15:16:38', ''),
(28, 1, 16, 'Intramurals', 4226544, 'Wayne, Damian', 'BSCRIM', '2025-10-18 15:16:40', ''),
(29, 1, 14, 'dadadadaw234', 4226347, 'Castañares, Julian Falcon', 'BSIT', '2025-10-19 10:22:41', ''),
(30, 1, 14, 'dadadadaw234', 4226351, 'Doe, John', 'BSHM', '2025-10-19 10:24:49', '2025-10-19 12:30:57'),
(31, 1, 14, 'dadadadaw234', 123456, 'Stark, Tony', 'BSIT', '2025-10-19 10:45:14', '2025-10-19 12:30:53'),
(32, 1, 14, 'dadadadaw234', 4226330, 'Kent, Clark', 'BSIT', '2025-10-19 10:45:17', '2025-10-19 12:30:51'),
(33, 1, 14, 'dadadadaw234', 4226348, 'Parker, Peter', 'BSIT', '2025-10-19 10:45:19', '2025-10-19 12:30:55'),
(34, 1, 35, 'admin event 2', 4226350, 'Taylor, David', 'BSCRIM', '2025-10-20 11:21:20', ''),
(35, 1, 35, 'admin event 2', 4226347, 'Castañares, Julian Falcon', 'BSIT', '2025-10-20 12:00:09', ''),
(36, 1, 37, 'cbta samples', 4226347, 'Castañares, Julian Falcon', 'BSIT', '2025-10-21 23:50:06', ''),
(37, 1, 37, 'cbta samples', 123456, 'Stark, Tony', 'BSIT', '2025-10-22 00:01:20', '');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `Dept_name` varchar(255) NOT NULL,
  `dept_Acronym` varchar(50) NOT NULL,
  `archived` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','archived','','') NOT NULL DEFAULT 'active',
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `department_head` varchar(255) DEFAULT NULL,
  `office_location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `Dept_name`, `dept_Acronym`, `archived`, `status`, `contact_email`, `contact_phone`, `department_head`, `office_location`, `description`) VALUES
(1, 'Colege of Arts, and Sciences, and Teacher Education', 'CASTE', 0, 'active', NULL, NULL, NULL, NULL, NULL),
(2, 'College of Criminology', 'COC', 0, 'active', NULL, NULL, NULL, NULL, NULL),
(3, 'College of Business, Technology and Accountancy', 'CBTA', 0, 'active', 'cbta@gmail.com', '09123456789', 'ma\'am Sample', '', 'lorem123'),
(4, 'College of Nursing', 'CON', 0, 'active', NULL, NULL, NULL, NULL, NULL),
(5, 'Senior High School', 'Senior High School', 0, 'active', NULL, NULL, NULL, NULL, NULL),
(6, 'Junior High School', 'Junior High School', 0, 'active', NULL, NULL, NULL, NULL, NULL),
(7, 'Elementary Department', 'Elementary', 0, 'active', NULL, NULL, NULL, NULL, NULL),
(8, 'new sample dept', 'dept1123', 1, 'active', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `date` varchar(50) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `departments_id` int(11) NOT NULL,
  `other_dept` varchar(50) NOT NULL,
  `TimeIn_start` varchar(50) NOT NULL,
  `TimeIn_end` varchar(50) NOT NULL,
  `Time_out` varchar(50) NOT NULL,
  `Timeout_out` varchar(50) NOT NULL,
  `status` enum('active','archived','','') NOT NULL DEFAULT 'active',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `user_id`, `event_name`, `date`, `semester_id`, `departments_id`, `other_dept`, `TimeIn_start`, `TimeIn_end`, `Time_out`, `Timeout_out`, `status`, `timestamp`) VALUES
(14, 1, 'dadadadaw234', '2026-10-19', 1, 3, '', '07:30', '08:00', '12:02', '12:32', 'archived', '2025-10-19 07:07:30'),
(15, 1, 'panaghiusa 2025', '2025-10-18', 1, 3, '', '07:00', '08:30', '12:00', '12:30', 'active', '2025-10-18 03:05:58'),
(16, 1, 'Intramurals123', '2025-10-18', 3, 3, '', '15:00', '15:20', '15:30', '15:35', 'active', '2025-10-19 03:06:27'),
(17, 1, 'dfda', '2025-10-16', 2, 1, '', '19:00', '20:00', '22:00', '22:30', 'archived', '2025-10-19 06:23:17'),
(18, 1, 'pojdpwd', '2025-10-17', 1, 3, '', '07:00', '07:30', '12:00', '12:30', 'active', '2025-10-21 11:59:24'),
(19, 2, 'crim day', '2025-10-16', 1, 2, '', '07:00', '08:30', '12:00', '12:30', 'active', '2025-10-19 03:08:56'),
(20, 1, 'new', '2025-10-17', 1, 4, '', '07:00', '07:30', '12:30', '12:30', 'active', '2025-10-21 11:59:16'),
(21, 1, 'new123', '2025-10-16', 1, 1, '', '07:00', '07:30', '08:00', '09:00', 'archived', '2025-10-19 03:10:08'),
(22, 1, 'dadawdawdaw', '2025-10-16', 1, 3, '', '07:00', '08:00', '12:00', '12:30', 'archived', '2025-10-19 02:00:13'),
(23, 1, 'crim-cbta', '2025-10-17', 1, 2, '', '07:00', '07:30', '11:00', '12:00', 'archived', '2025-10-19 02:43:17'),
(24, 1, 'sample new001', '2025-10-17', 1, 3, '', '21:50', '22:15', '22:20', '22:30', 'active', '2025-10-17 14:13:03'),
(25, 1, 'new', '2025-10-17', 1, 3, '', '07:00', '07:30', '12:30', '16:30', 'active', '2025-10-17 05:26:59'),
(28, 1, 'newest sample', '2025-10-17', 1, 3, '', '22:30', '22:35', '22:50', '23:00', 'archived', '2025-10-18 07:43:52'),
(29, 1, 'advance79', '2025-10-21', 1, 3, '', '07:01', '08:30', '12:00', '12:30', 'active', '2025-10-19 03:33:46'),
(31, 2, 'Crim attendance', '2025-10-19', 1, 2, '', '10:30', '11:00', '12:00', '12:30', 'active', '2025-10-22 03:32:41'),
(32, 2, 'next year', '2026-01-04', 1, 2, '', '07:00', '08:00', '10:30', '11:00', 'active', '2025-10-19 04:16:35'),
(33, 2, 'next2 year', '2027-01-19', 1, 2, '', '07:00', '08:30', '12:00', '12:30', 'active', '2025-10-19 04:18:01'),
(34, 3, 'admin event', '2025-10-19', 1, 0, '', '12:00', '13:30', '15:00', '16:00', 'active', '2025-10-19 04:43:44'),
(35, 3, 'admin event 2', '2025-10-20', 1, 0, '', '07:00', '08:30', '12:00', '12:30', 'active', '2025-10-19 04:49:29'),
(36, 5, 'CASTE WEEK no.1', '2025-10-21', 1, 1, '', '20:00', '20:30', '21:00', '21:30', 'active', '2025-10-21 12:06:55'),
(37, 1, 'cbta samples', '2025-10-22', 1, 3, '', '00:00', '00:30', '01:00', '01:30', 'active', '2025-10-21 16:01:04');

-- --------------------------------------------------------

--
-- Table structure for table `event_other_departments`
--

CREATE TABLE `event_other_departments` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_other_departments`
--

INSERT INTO `event_other_departments` (`id`, `event_id`, `department_id`) VALUES
(39, 24, 4),
(40, 24, 2),
(41, 26, 3),
(42, 27, 2),
(113, 23, 3),
(114, 23, 2),
(117, 30, 3),
(118, 30, 2),
(119, 14, 3),
(120, 14, 2),
(144, 29, 2),
(145, 32, 3),
(146, 33, 3),
(147, 34, 3),
(148, 34, 2),
(149, 35, 3),
(150, 35, 2),
(151, 15, 4),
(152, 25, 2),
(153, 18, 2),
(154, 18, 4);

-- --------------------------------------------------------

--
-- Table structure for table `otp_requests`
--

CREATE TABLE `otp_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `used` tinyint(4) DEFAULT 0,
  `verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_requests`
--

INSERT INTO `otp_requests` (`id`, `user_id`, `otp`, `email`, `created_at`, `expires_at`, `used`, `verified_at`) VALUES
(30, 3, '687634', 'juliancastanares1@gmail.com', '2025-10-22 21:42:08', '2025-10-22 15:57:08', 1, '2025-10-22 21:45:39'),
(31, 3, '462161', 'juliancastanares1@gmail.com', '2025-10-22 21:45:56', '2025-10-22 16:00:56', 1, '2025-10-22 21:46:08'),
(35, 3, '004492', 'juliancastanares1@gmail.com', '2025-10-22 21:50:26', '2025-10-22 16:05:26', 1, '2025-10-22 21:50:34'),
(36, 3, '717093', 'juliancastanares1@gmail.com', '2025-10-22 21:52:57', '2025-10-22 16:07:57', 1, '2025-10-22 21:53:05'),
(37, 3, '015001', 'juliancastanares1@gmail.com', '2025-10-22 22:04:19', '2025-10-22 16:19:19', 1, '2025-10-22 22:04:26'),
(38, 3, '205121', 'juliancastanares1@gmail.com', '2025-10-22 22:39:55', '2025-10-22 16:54:55', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `semester`
--

CREATE TABLE `semester` (
  `id` int(11) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `start` varchar(50) NOT NULL,
  `end` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semester`
--

INSERT INTO `semester` (`id`, `semester`, `start`, `end`) VALUES
(1, '1st Semester', '2000-08-01', '2000-12-31'),
(2, '2nd Semester', '2000-01-01', '2000-05-31'),
(3, 'Summer', '2000-06-01', '2000-07-31');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `ID_Number` int(10) NOT NULL,
  `RFID` bigint(20) NOT NULL,
  `Student_name` varchar(50) NOT NULL,
  `Course` varchar(50) NOT NULL,
  `departments_id` varchar(50) NOT NULL,
  `status` enum('active','archived','','') NOT NULL DEFAULT 'active',
  `cause` varchar(50) NOT NULL,
  `archived_stamp` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `ID_Number`, `RFID`, `Student_name`, `Course`, `departments_id`, `status`, `cause`, `archived_stamp`) VALUES
(1, 4226347, 2920516186, 'Castañares, Julian Falcon', 'BSIT', '3', 'active', '', ''),
(2, 123456, 3035541086, 'Stark, Tony', 'BSIT', '3', 'active', '', ''),
(3, 4226544, 3033278222, 'Wayne, Damian', 'BSCRIM', '2', 'active', '', ''),
(4, 4226545, 3035121566, 'Zsasz, Victor', 'BSCRIM', '2', 'archived', 'Discontinued', '2025'),
(5, 4226348, 3031817438, 'Parker, Peter', 'BSIT', '3', 'active', '', ''),
(9, 4226349, 3035128254, 'Reed, Richard', 'BSIT', '3', 'active', '', ''),
(10, 4226350, 2869943342, 'Taylor, David', 'BSCRIM', '2', 'active', '', ''),
(11, 4226351, 3031822926, 'Doe, John', 'BSHM', '3', 'active', '', ''),
(12, 4226330, 3031964270, 'Kent, Clark', 'BSIT', '3', 'active', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `departments_id` int(11) NOT NULL,
  `account_type` enum('user','admin','','') NOT NULL DEFAULT 'user',
  `status` enum('active','archived','','') NOT NULL DEFAULT 'active',
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `auto_checkin` tinyint(1) NOT NULL DEFAULT 0,
  `daily_summary` tinyint(1) NOT NULL DEFAULT 0,
  `profile_pic` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `first_name`, `last_name`, `password`, `departments_id`, `account_type`, `status`, `email`, `phone`, `description`, `auto_checkin`, `daily_summary`, `profile_pic`, `last_login`) VALUES
(1, 'CBTA-Department', NULL, NULL, '$2y$10$8xrQwvCwY2HflybFBWJix.UT8INUE1Gm1YjoETH/9o2/VXKg4iW/a', 3, 'user', 'archived', NULL, NULL, NULL, 0, 0, 'images/profiles/user_1_1761059112.png', '2025-10-22 11:48:53'),
(2, 'Crim-Department', NULL, NULL, '$2y$10$KnUuMPr/RQxyqr0qQ.N88Ox8TFbHucUm96bxJPQV7niuDZwrpcB2i', 2, 'user', 'active', NULL, NULL, NULL, 0, 0, NULL, '2025-10-22 17:11:13'),
(3, 'OSAS-Admin', 'Julian', 'Castañares', '$2y$10$H6e3DlbN6vGDbtE4mm2DMOPiVavK.hNne7NXv.UJTDPLheuqneJge', 0, 'admin', 'active', 'juliancastanares1@gmail.com', '09077386196', 'lorem ipsum123', 0, 0, 'images/profiles/user_3_1761055299.jpg', '2025-10-22 22:04:50'),
(5, 'CASTE-Department', NULL, NULL, '$2y$10$DafUF9lOfij4AOCenSQPAeWgcMBknbjg5Ev2tSbI9Cv/7alwyQ2oG', 1, 'user', 'active', NULL, NULL, NULL, 0, 0, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance_tbl`
--
ALTER TABLE `attendance_tbl`
  ADD PRIMARY KEY (`Attendance_ID`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_other_departments`
--
ALTER TABLE `event_other_departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `otp_requests`
--
ALTER TABLE `otp_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `semester`
--
ALTER TABLE `semester`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance_tbl`
--
ALTER TABLE `attendance_tbl`
  MODIFY `Attendance_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `event_other_departments`
--
ALTER TABLE `event_other_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- AUTO_INCREMENT for table `otp_requests`
--
ALTER TABLE `otp_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `semester`
--
ALTER TABLE `semester`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `otp_requests`
--
ALTER TABLE `otp_requests`
  ADD CONSTRAINT `otp_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
