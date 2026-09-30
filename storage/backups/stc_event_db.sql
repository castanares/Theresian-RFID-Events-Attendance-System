-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 20, 2025 at 01:29 PM
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
(37, 1, 37, 'cbta samples', 123456, 'Stark, Tony', 'BSIT', '2025-10-22 00:01:20', ''),
(38, 1, 40, 'sample1234342', 4226544, 'Wayne, Damian', 'BSCRIM', '2025-11-18 14:49:56', '');

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
(2, 'College of Criminology', 'COC', 0, 'active', 'crim@gmail.com', '09123456789', 'mr. Pepeto', NULL, 'dawdwadw'),
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
(19, 2, 'crim day', '2025-10-16', 1, 2, '', '07:00', '08:30', '12:00', '12:30', 'archived', '2025-10-31 12:51:27'),
(20, 1, 'CBTA-Day', '2025-10-25', 1, 3, '', '07:00', '07:30', '12:30', '12:30', 'archived', '2025-10-24 12:52:38'),
(21, 1, 'new123', '2025-10-16', 1, 1, '', '07:00', '07:30', '08:00', '09:00', 'archived', '2025-10-19 03:10:08'),
(22, 1, 'dadawdawdaw', '2025-10-16', 1, 3, '', '07:00', '08:00', '12:00', '12:30', 'archived', '2025-10-19 02:00:13'),
(23, 1, 'crim-cbta', '2025-10-17', 1, 2, '', '07:00', '07:30', '11:00', '12:00', 'archived', '2025-10-19 02:43:17'),
(24, 1, 'sample new00001', '2025-10-17', 1, 0, '', '21:50', '22:15', '22:20', '22:30', 'active', '2025-10-24 13:20:38'),
(25, 1, 'new', '2025-10-17', 1, 3, '', '07:00', '07:30', '12:30', '16:30', 'active', '2025-10-17 05:26:59'),
(28, 1, 'newest sample', '2025-10-17', 1, 3, '', '22:30', '22:35', '22:50', '23:00', 'archived', '2025-10-18 07:43:52'),
(29, 1, 'advance79', '2025-10-21', 1, 3, '', '07:01', '08:30', '12:00', '12:30', 'active', '2025-10-19 03:33:46'),
(31, 2, 'Crim attendance', '2025-10-19', 1, 2, '', '10:30', '11:00', '12:00', '12:30', 'active', '2025-10-22 03:32:41'),
(32, 2, 'next year', '2026-01-04', 1, 3, '', '07:00', '08:00', '10:30', '11:00', 'active', '2025-10-24 13:02:05'),
(33, 2, 'next2 year', '2027-01-19', 1, 2, '', '07:00', '08:30', '12:00', '12:30', 'active', '2025-10-19 04:18:01'),
(34, 3, 'admin event', '2025-10-19', 1, 0, '', '12:00', '13:30', '15:00', '16:00', 'active', '2025-10-19 04:43:44'),
(35, 3, 'admin event 1', '2025-10-20', 1, 3, '', '07:00', '08:30', '12:00', '12:30', 'active', '2025-10-24 12:56:21'),
(36, 5, 'CASTE WEEK no.123', '2025-10-21', 1, 0, '', '20:00', '20:30', '21:00', '21:30', 'active', '2025-10-31 12:27:18'),
(37, 1, 'cbta samples123', '2025-10-22', 1, 0, '', '01:00', '01:30', '02:00', '02:30', 'active', '2025-11-17 11:58:25'),
(38, 1, 'sample1', '2025-10-24', 1, 3, '', '18:00', '18:30', '19:00', '19:30', 'active', '2025-10-24 13:00:16'),
(39, 1, 'new sample event for today123', '2025-11-01', 1, 3, '', '07:00', '07:30', '10:30', '11:00', 'active', '2025-10-31 12:23:47'),
(40, 1, 'sample1234342', '2025-11-18', 1, 3, '', '13:00', '15:00', '15:03', '16:00', 'active', '2025-11-18 06:49:49');

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
(41, 26, 3),
(42, 27, 2),
(113, 23, 3),
(114, 23, 2),
(117, 30, 3),
(118, 30, 2),
(119, 14, 3),
(120, 14, 2),
(144, 29, 2),
(146, 33, 3),
(147, 34, 3),
(148, 34, 2),
(151, 15, 4),
(152, 25, 2),
(153, 18, 2),
(154, 18, 4),
(159, 35, 2),
(160, 32, 2),
(162, 24, 3),
(163, 24, 2),
(165, 36, 1),
(168, 37, 3),
(169, 40, 2);

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
(39, 3, '978304', 'julyncastanares091219@gmail.com', '2025-10-31 21:02:22', '2025-10-31 14:17:22', 1, '2025-10-31 21:02:37'),
(41, 3, '966376', 'julyncastanares091219@gmail.com', '2025-10-31 21:08:25', '2025-10-31 14:23:25', 0, NULL);

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
(2, 4226348, 3035541086, 'Stark, Tony', 'BSIT', '3', 'active', '', ''),
(3, 4226544, 3033278222, 'Wayne, Damian', 'BSCRIM', '2', 'active', '', ''),
(4, 4226545, 3035121566, 'Zsasz, Victor', 'BSCRIM', '2', 'archived', 'Discontinued', '2025'),
(5, 4226348, 3031817438, 'Parker, Peter', 'BSIT', '3', 'active', '', ''),
(9, 4226349, 3035128254, 'Reed, Richard', 'BSIT', '3', 'active', '', ''),
(10, 4226350, 2869943342, 'Taylor, David', 'BSCRIM', '2', 'active', '', ''),
(11, 4226351, 3031822926, 'Doe, John', 'BSHM', '3', 'active', '', ''),
(12, 4226330, 3031964270, 'Kent, Clark', 'BSIT', '3', 'active', '', ''),
(14, 4227197, 4190709076, 'Arlalejo, Prince Jhoar', 'BSIT', '3', 'active', '', ''),
(15, 4226883, 4190073220, 'Ravelo, Juztice Ray', 'BSIT', '3', 'active', '', ''),
(16, 422500, 7746050, 'Jayson Mark Alvarado', 'BSCRIM', '2', 'active', '', ''),
(17, 422501, 6540066, 'Maricel Joy Reyes', 'BSCRIM', '2', 'active', '', ''),
(18, 422100, 4236340799, 'Angelica Mae Santos', 'BSIT', '3', 'active', '', ''),
(19, 422502, 7537778, 'Kenneth Paul Navarro', 'BSCRIM', '2', 'active', '', ''),
(20, 422503, 5950866, 'Trisha Mae Santiago', 'BSCRIM', '2', 'active', '', ''),
(21, 422504, 8576962, 'Carlo James Bautista', 'BSCRIM', '2', 'active', '', ''),
(22, 422101, 4236299775, 'John Carlo Dela Cruz', 'BSIT', '3', 'active', '', ''),
(23, 422505, 7587442, 'Angelica Faith Dizon', 'BSRIM', '2', 'active', '', ''),
(24, 422102, 4236113375, 'Camille Rose Tan', 'BSIT', '3', 'active', '', ''),
(25, 422506, 7216594, '5.	Bryan Cedric Ramos', 'BSRIM', '2', 'active', '', ''),
(26, 422103, 4237042191, 'Mark Joseph Villanueva', 'BSIT', '3', 'active', '', ''),
(27, 422507, 7467762, 'Shiela Mae Corpuz', 'BSRIM', '2', 'active', '', ''),
(28, 422104, 4236713567, 'Patricia Joy Mendoza', 'BSHM', '3', 'active', '', ''),
(29, 422508, 9313314, 'Aldrin Jay Morales', 'BSRIM', '2', 'active', '', ''),
(30, 422105, 4229258991, 'Joshua Paul Reyes', 'BSHM', '3', 'active', '', ''),
(31, 422509, 8575986, 'Kimberly Rose Dela Peña', 'BSRIM', '2', 'active', '', ''),
(32, 422106, 4236559183, 'Kristine Anne Navarro', 'BSHM', '3', 'active', '', ''),
(33, 422510, 4222817727, 'Dominic John Cruz', 'BSRIM', '2', 'active', '', ''),
(34, 422106, 4233661615, 'Daniel Angelo Lim', 'BSHM', '3', 'active', '', ''),
(35, 422107, 4235995935, 'Angelica Therese Garcia', 'BSHM', '3', 'active', '', ''),
(36, 422511, 4222781535, 'Erika Louise Torres', 'BSRIM', '2', 'active', '', ''),
(37, 422107, 4229232047, 'Kevin Matthew Soriano.', 'BPA', '3', 'active', '', ''),
(38, 422512, 4222488927, 'Francis Kyle Salazar', 'BSRIM', '2', 'active', '', ''),
(39, 422512, 4230409695, 'Hannah Ysabelle Ocampo', 'BSRIM', '2', 'active', '', ''),
(40, 422513, 4230126511, 'Lance Gabriel Mendoza', 'BSRIM', '2', 'active', '', ''),
(41, 422514, 4233657535, 'Patricia Jean Robles', 'BSRIM', '2', 'active', '', ''),
(42, 422515, 4235055743, 'Joshua Noel Manalo', 'BSRIM', '2', 'active', '', ''),
(43, 422516, 4230667167, 'Camille Therese Garcia', 'BSRIM', '2', 'active', '', ''),
(44, 422517, 4225018239, 'Camille Therese Garcia', 'BSRIM', '2', 'active', '', ''),
(45, 422518, 4223043071, 'Kristine Anne Tolentino', 'BSRIM', '2', 'active', '', ''),
(46, 422110, 55157923, 'Maria Camille Ramos', 'BPA', '3', 'active', '', ''),
(47, 422519, 3067426, 'Jerome Benedict Tan', 'BSRIM', '2', 'active', '', ''),
(48, 422111, 4233919855, 'Francis Noel Bautista', 'BPA', '3', 'active', '', ''),
(49, 422520, 2426546, 'Ryan Matthew De Leon', 'BSRIM', '2', 'active', '', ''),
(50, 422112, 4229807903, 'Nicole Ysabelle Aquino', 'BPA', '3', 'active', '', ''),
(51, 422521, 3460978, 'Nicole Joy Castillo', 'BSRIM', '2', 'active', '', ''),
(52, 422113, 4231350143, 'Christian James Fernandez', 'BSAIS', '3', 'active', '', ''),
(53, 422114, 4231272831, 'Hannah Faith Salazar', 'BSAIS', '3', 'active', '', ''),
(54, 422522, 2332690, 'Daniel Joseph Aquino', 'BSRIM', '2', 'active', '', ''),
(55, 422115, 4235923135, 'Jerome Luis Castillo', 'BSBA', '3', 'active', '', ''),
(56, 422523, 2742658, 'Bea Kristine Ramos', 'BSRIM', '2', 'active', '', ''),
(57, 422524, 2529538, 'Nathaniel Paul Lim', 'BSRIM', '2', 'active', '', ''),
(58, 422525, 3387778, 'Sofia Camille Villanueva', 'BSRIM', '2', 'active', '', ''),
(59, 422116, 4229029903, 'Bea Nicole Torres', 'BSBA', '3', 'active', '', ''),
(60, 422117, 54451203, 'Gabriel Dominic Chavez', 'BSTM', '3', 'active', '', ''),
(61, 4221, 4235611119, 'Trisha Jean Mercado', 'BSTM', '3', 'active', '', ''),
(62, 422119, 4228702191, 'Ryan Angelo Ocampo', 'BSTM', '3', 'active', '', ''),
(63, 422120, 3174866, 'Sofia Grace Manalo', 'BSTM', '3', 'active', '', ''),
(64, 422121, 4229287359, 'Lance Paul Pineda', 'BSTM', '3', 'active', '', ''),
(65, 422122, 4236737887, 'Erika Mae Robles', 'BSTM', '3', 'active', '', ''),
(66, 422200, 4237016079, 'Alyssa Joy Dela Cruz', 'BSN', '4', 'active', '', ''),
(67, 422201, 4222512447, 'John Paul Reyes', 'BSN', '4', 'active', '', ''),
(68, 422202, 4226737439, 'Camille Anne Santos', 'BSN', '4', 'active', '', ''),
(69, 422600, 4222196895, 'Alyssa Mae Dela Cruz', 'BA Pol Sci', '1', 'active', '', ''),
(70, 422203, 4223848975, 'Mark Angelo Tan', 'BSN', '4', 'active', '', ''),
(71, 422601, 11732770, 'Kevin Paul Soriano', 'BS Math', '1', 'active', '', ''),
(72, 422204, 4225554367, 'Patricia Mae Villanueva', 'BSN', '4', 'active', '', ''),
(73, 422602, 4226932847, 'Angelica Rose Garcia', 'BS Biology', '1', 'active', '', ''),
(74, 422205, 4224448367, 'Joshua Matthew Mendoza', 'BSN', '4', 'active', '', ''),
(75, 422206, 4227125359, 'Kristine Rose Navarro', 'BSN', '4', 'active', '', ''),
(76, 422603, 4224705359, 'Daniel Joseph Lim', 'BA Philo', '1', 'active', '', ''),
(77, 422207, 4224452751, 'Daniel Joseph Lim', 'BSN', '4', 'active', '', ''),
(78, 422208, 4224204287, 'Angelica Faith Garcia', 'BSN', '4', 'active', '', ''),
(79, 422209, 4224285887, 'Kevin Noel Soriano', 'BSN', '4', 'active', '', ''),
(80, 422210, 78858803, 'Maria Therese Ramos', 'BSN', '4', 'active', '', ''),
(81, 422604, 11608674, 'Kristine Faith Navarro', 'BEEd', '1', 'active', '', ''),
(82, 422211, 77464259, 'Francis Kyle Bautista', 'BSN', '4', 'active', '', ''),
(83, 422212, 80190851, 'Nicole Ysabelle Aquino', 'BSN', '4', 'active', '', ''),
(84, 422605, 4226536127, 'Joshua Miguel Tan', 'BSEd-Science', '1', 'active', '', ''),
(85, 422213, 79969107, 'Christian Dominic Fernandez', 'BSN', '4', 'active', '', ''),
(86, 422606, 13765714, 'Patricia Anne Mendoza', 'BSEd-English', '1', 'active', '', ''),
(87, 422214, 79753331, 'Hannah Grace Salazar', 'BSN', '4', 'active', '', ''),
(88, 422607, 14248530, 'Mark Angelo Villanueva', 'BSEd-Filipino', '1', 'active', '', ''),
(89, 422215, 4235522079, 'Jerome Paul Castillo', 'BSN', '4', 'active', '', ''),
(90, 422216, 50287795, 'Bea Camille Torres', 'BSN', '4', 'active', '', ''),
(91, 422608, 4224949359, 'Camille Joy Santos', 'BSEd-Mathematics', '1', 'active', '', ''),
(92, 422217, 50305859, 'Gabriel Matthew Chavez', 'BSN', '4', 'active', '', ''),
(93, 422218, 50094579, 'Trisha Nicole Mercado', 'BSN', '4', 'active', '', ''),
(94, 422219, 4236778511, 'Ryan Joseph Ocampo', 'BSN', '4', 'active', '', ''),
(95, 422220, 4222594655, 'Sofia Faith Manalo', 'BSN', '4', 'active', '', ''),
(96, 422609, 4224713279, '2.	John Benedict Reyes', 'BA Pol Sci', '1', 'active', '', ''),
(97, 422221, 4220903567, 'Lance Angelo Pineda', 'BSN', '4', 'active', '', ''),
(98, 422222, 4200325407, 'Erika Joy Robles', 'BSN', '4', 'active', '', ''),
(99, 422610, 4231027279, 'Maria Isabel Ramos', 'BA Philo', '1', 'active', '', ''),
(100, 422223, 4222016335, 'Nathaniel Paul De Leon', 'BSN', '4', 'active', '', ''),
(101, 422611, 4229782031, 'Ryan Matthew Ocampo', 'BSEd-Science', '1', 'active', '', ''),
(102, 422224, 4223399999, 'Clarisse Mae Pangilinan', 'BSN', '4', 'active', '', ''),
(103, 422225, 4222748431, 'Miguel Dominic Abad', 'BSN', '4', 'active', '', ''),
(104, 422612, 4196026943, 'Trisha Camille Mercado', 'BEEd', '1', 'active', '', ''),
(105, 422226, 4222650303, 'Janine Grace Guevarra', 'BSN', '4', 'active', '', ''),
(106, 422227, 4222787919, 'Paolo Noel Cruz', 'BSN', '4', 'active', '', ''),
(107, 422228, 4223227407, 'Denise Camille Fabros', 'BSN', '4', 'active', '', ''),
(108, 422613, 4234985311, 'Gabriel Enrico Chavez', 'BS Math', '1', 'active', '', ''),
(109, 422229, 4221948255, 'Andre Joseph Pascual', 'BSN', '4', 'active', '', ''),
(110, 422614, 4230238079, 'Bea Lorraine Torres', 'BSEd-English', '1', 'active', '', ''),
(111, 422615, 4230768335, 'Jerome Patrick Castillo', 'BA Philo', '1', 'active', '', ''),
(112, 422616, 4230501423, 'Hannah Louise Salazar', 'BSEd-Science', '1', 'active', '', ''),
(113, 422617, 4196633935, 'Christian Noel Fernandez', 'BA Philo', '1', 'active', '', ''),
(114, 422618, 4230080575, 'Nicole Therese Aquino', 'BA Pol Sci', '1', 'active', '', ''),
(115, 422619, 4234656303, 'Francis Adrian Bautista', 'BS Math', '1', 'active', '', ''),
(116, 422620, 8622194, 'Sofia Ysabelle Manalo', 'BEEd', '1', 'active', '', ''),
(117, 422621, 9064594, 'Lance Dominic Pineda', 'BS Biology', '1', 'active', '', ''),
(118, 422622, 5097746, 'Erika Jean Robles', 'BS Biology', '1', 'active', '', ''),
(119, 422623, 5413874, 'Nathaniel James De Leon', 'BSEd-Filipino', '1', 'active', '', ''),
(120, 422624, 4128450, 'Clarisse Joy Pangilinan', 'BSEd-English', '1', 'active', '', ''),
(121, 422625, 3553410, 'Miguel Rafael Abad', 'BS Math', '1', 'active', '', ''),
(122, 422626, 4195964223, 'Janine Marie Guevarra', 'BA Pol Sci', '1', 'active', '', ''),
(123, 422627, 4196614639, 'Paolo Emmanuel Cruz', 'BS Math', '1', 'active', '', ''),
(124, 422628, 8779458, 'Denise Claire Fabros', 'BSEd-Mathematics', '1', 'active', '', ''),
(125, 422629, 4193723743, 'Andre Luis Pascual', 'BEEd', '1', 'active', '', '');

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
(1, 'CBTA-Department', NULL, NULL, '$2y$10$8xrQwvCwY2HflybFBWJix.UT8INUE1Gm1YjoETH/9o2/VXKg4iW/a', 3, 'user', 'active', NULL, NULL, NULL, 0, 0, 'images/profiles/user_1_1761308651.png', '2025-11-20 20:16:13'),
(2, 'Crim-Department', NULL, NULL, '$2y$10$KnUuMPr/RQxyqr0qQ.N88Ox8TFbHucUm96bxJPQV7niuDZwrpcB2i', 2, 'user', 'active', NULL, NULL, NULL, 0, 0, NULL, '2025-11-18 14:48:43'),
(3, 'OSAS-Admin', 'Julian', 'Castañares', '$2y$10$H6e3DlbN6vGDbtE4mm2DMOPiVavK.hNne7NXv.UJTDPLheuqneJge', 0, 'admin', 'active', 'julyncastanares091219@gmail.com', '09077386196', 'lorem ipsum123', 0, 0, 'images/profiles/user_3_1761055299.jpg', '2025-11-20 18:47:06'),
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
  MODIFY `Attendance_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `event_other_departments`
--
ALTER TABLE `event_other_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=170;

--
-- AUTO_INCREMENT for table `otp_requests`
--
ALTER TABLE `otp_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `semester`
--
ALTER TABLE `semester`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

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
