-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2025 at 09:21 PM
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
-- Database: `start_education`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unread','read','replied') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `created_at`, `status`) VALUES
(1, 'sasindu induwara', 'sasinduinduwara058@gmail.com', '0713445642', 'courses', 'hi', '2025-10-16 19:10:41', 'replied');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `course_description` text NOT NULL,
  `instructor` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `duration` int(11) NOT NULL,
  `course_type` enum('paid','free') NOT NULL DEFAULT 'paid',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `start_date` date NOT NULL,
  `start_time` time NOT NULL,
  `course_image` varchar(500) DEFAULT NULL,
  `zoom_link` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_name`, `course_description`, `instructor`, `category`, `duration`, `course_type`, `price`, `start_date`, `start_time`, `course_image`, `zoom_link`, `created_at`, `updated_at`) VALUES
(1, 'cos', 'fdfd', 'fdfd', 'programming', 1, '', 0.00, '2025-10-14', '23:35:00', 'uploads/68ee8fbd1cdba_1760464829.png', 'https://meet.google.com/tir-ndgr-mxj', '2025-10-14 18:00:29', '2025-10-15 16:07:54'),
(2, 'com', 'hi', 'P.M.sasindu induwra', 'music', 1, 'free', 0.00, '2025-10-15', '21:46:00', 'uploads/68efc77811944_1760544632.jpg', 'https://meet.google.com/tir-ndgr-mxj', '2025-10-15 16:10:32', '2025-10-15 16:10:32');

-- --------------------------------------------------------

--
-- Table structure for table `course_records`
--

CREATE TABLE `course_records` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `record_date` date NOT NULL,
  `record_title` varchar(255) NOT NULL,
  `youtube_link` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_records`
--

INSERT INTO `course_records` (`id`, `course_id`, `record_date`, `record_title`, `youtube_link`, `description`, `duration`, `created_at`) VALUES
(4, 2, '2025-10-15', 'day1', 'https://youtu.be/Lv5U8fsLlMY?si=bPzRYkL3sPH76A74', 'computer', '1', '2025-10-15 16:53:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_course_type` (`course_type`),
  ADD KEY `idx_start_date` (`start_date`);

--
-- Indexes for table `course_records`
--
ALTER TABLE `course_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `course_records`
--
ALTER TABLE `course_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `course_records`
--
ALTER TABLE `course_records`
  ADD CONSTRAINT `course_records_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
