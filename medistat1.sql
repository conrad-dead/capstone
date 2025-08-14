-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 06, 2025 at 02:45 PM
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
-- Database: `medistat`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `clinician_id` int(11) NOT NULL,
  `action` text NOT NULL,
  `table_affected` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `record_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `clinician_id`, `action`, `table_affected`, `timestamp`, `record_id`) VALUES
(1, 12, 'login', 'clinicians_table', '2025-05-27 17:42:40', NULL),
(2, 12, 'login', 'clinicians_table', '2025-05-27 17:42:50', NULL),
(3, 12, 'login', 'clinicians_table', '2025-05-27 17:44:00', NULL),
(4, 12, 'login', 'clinicians_table', '2025-05-27 17:44:11', NULL),
(5, 12, 'login', 'clinicians_table', '2025-05-27 17:46:37', NULL),
(6, 12, 'login', 'clinicians_table', '2025-05-27 17:46:54', NULL),
(7, 12, 'login', 'clinicians_table', '2025-05-27 17:48:00', NULL),
(8, 12, 'login', 'clinicians_table', '2025-05-27 17:48:15', NULL),
(9, 12, 'login', 'clinicians_table', '2025-05-27 17:49:42', NULL),
(10, 12, 'login', 'clinicians_table', '2025-05-27 17:49:50', NULL),
(11, 12, 'login', 'clinicians_table', '2025-05-27 17:50:28', NULL),
(12, 12, 'login', 'clinicians_table', '2025-05-27 17:53:05', NULL),
(13, 13, 'login', 'clinicians_table', '2025-05-27 17:58:49', NULL),
(14, 12, 'login', 'clinicians_table', '2025-05-27 18:19:41', NULL),
(15, 12, 'login', 'clinicians_table', '2025-05-28 08:24:47', NULL),
(16, 13, 'login', 'clinicians_table', '2025-05-28 08:34:27', NULL),
(17, 12, 'login', 'clinicians_table', '2025-05-28 08:34:45', NULL),
(18, 14, 'login', 'clinicians_table', '2025-05-28 08:35:44', NULL),
(19, 12, 'login', 'clinicians_table', '2025-05-28 08:44:06', NULL),
(20, 13, 'login', 'clinicians_table', '2025-05-28 08:49:21', NULL),
(21, 14, 'login', 'clinicians_table', '2025-05-28 08:49:49', NULL),
(22, 13, 'User logged in', '', '2025-05-28 08:54:22', NULL),
(23, 12, 'User logged in', '', '2025-05-28 08:54:43', NULL),
(24, 12, 'User logged in', '', '2025-05-28 08:54:55', NULL),
(25, 14, 'User logged in', '', '2025-05-28 08:55:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `barangay_table`
--

CREATE TABLE `barangay_table` (
  `id` int(11) NOT NULL,
  `barangay_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay_table`
--

INSERT INTO `barangay_table` (`id`, `barangay_name`) VALUES
(1, 'Barangay A'),
(2, 'Barangay B'),
(3, 'Barangay C');

-- --------------------------------------------------------

--
-- Table structure for table `clinicians_table`
--

CREATE TABLE `clinicians_table` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `role` enum('Doctor','Nurse','MidWife','Other') NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clinicians_table`
--

INSERT INTO `clinicians_table` (`id`, `first_name`, `middle_name`, `last_name`, `role`, `contact_number`, `address`, `username`, `password`, `role_id`) VALUES
(12, 'System', NULL, 'Administrator', 'Doctor', '123456789', NULL, 'admin', '$2y$10$x3DHHQeZGiJVrsGAplK40u26acjoXnDsgrUwZ553WYyNSe/HW6HGu', 1),
(13, 'Francis', NULL, 'Aguinaldo', 'Doctor', '09706318004', 'upi gamu isabela ', 'doctor1', '$2y$10$QFWfGPlWmGvLDnBsUnDhVejIugBMVxFrz.VWZGJsojEj/JSYsDCv.', 38),
(14, 'alex', NULL, 'g', 'Doctor', '', 'upi gamu isabela ', 'alex', '$2y$10$OKzib.Rp807iNiZaR6tYVOeOWZHLhq1cke.V0W83vjdQsPBbx6Ciq', 3);

-- --------------------------------------------------------

--
-- Table structure for table `disease`
--

CREATE TABLE `disease` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disease`
--

INSERT INTO `disease` (`id`, `name`, `description`) VALUES
(1, 'Dengue', NULL),
(2, 'Influenza', NULL),
(3, 'COVID-19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `disease_trends`
--

CREATE TABLE `disease_trends` (
  `id` int(11) NOT NULL,
  `barangay_id` int(11) NOT NULL,
  `disease_id` int(11) NOT NULL,
  `reported_case` text DEFAULT NULL,
  `reported_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `family_members`
--

CREATE TABLE `family_members` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `relationship` enum('Father','Mother','Spouse','Child','Sibling','Grandparent','Other') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_record`
--

CREATE TABLE `medical_record` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `clinician_id` int(11) NOT NULL,
  `disease_id` int(11) NOT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `visit_date` date NOT NULL,
  `barangay_id` int(11) NOT NULL,
  `consultation_time` time DEFAULT NULL,
  `visit_mode` enum('Walk-in','Visited','Referral') DEFAULT NULL,
  `referred_from` varchar(255) DEFAULT NULL,
  `referred_to` varchar(255) DEFAULT NULL,
  `chief_complaints` text DEFAULT NULL,
  `bp` varchar(10) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medicine`
--

CREATE TABLE `medicine` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine`
--

INSERT INTO `medicine` (`id`, `name`, `description`, `category`, `quantity`, `unit`, `expiry_date`) VALUES
(1, 'Paracetamol', NULL, 'Antipyretic', 1000, 'tablets', '2026-01-01'),
(2, 'ORS', NULL, 'Hydration', 500, 'sachet', '2025-08-01');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_distribution`
--

CREATE TABLE `medicine_distribution` (
  `id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `quantity_given` int(11) NOT NULL,
  `date_issued` date NOT NULL,
  `clinician_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medicine_inventory`
--

CREATE TABLE `medicine_inventory` (
  `id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `stock_level` int(11) NOT NULL DEFAULT 0,
  `date_updated` date NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients_table`
--

CREATE TABLE `patients_table` (
  `id` int(11) NOT NULL,
  `patient_code` varchar(50) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `marital_status` enum('Single','Married','Divorced','Widowed') DEFAULT NULL,
  `spouse_id` int(11) DEFAULT NULL,
  `mother_id` int(11) DEFAULT NULL,
  `father_id` int(11) DEFAULT NULL,
  `blood_type` enum('A','B','AB','O') DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `barangay_id` int(11) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_number` varchar(20) DEFAULT NULL,
  `insurance_number` varchar(50) DEFAULT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `income_level` enum('Low','Medium','High') DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `registration_date` date NOT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `maiden_name` varchar(255) DEFAULT NULL,
  `birth_place` varchar(255) DEFAULT NULL,
  `dswd_nhts` tinyint(1) DEFAULT 0,
  `is_4ps_member` tinyint(1) DEFAULT 0,
  `education_level` enum('None','Elementary','High School','Vocational','College','Post Graduate') DEFAULT NULL,
  `employment_status` enum('Student','Employed','Retired','None','Unknown') DEFAULT NULL,
  `family_role` enum('Father','Mother','Son','Daughter','Other') DEFAULT NULL,
  `is_philhealth_member` tinyint(1) DEFAULT 0,
  `philhealth_type` enum('FE – Private','FE – Government','IE','Other') DEFAULT NULL,
  `philhealth_number` varchar(50) DEFAULT NULL,
  `pcb_member` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients_table`
--

INSERT INTO `patients_table` (`id`, `patient_code`, `first_name`, `middle_name`, `last_name`, `birth_date`, `gender`, `marital_status`, `spouse_id`, `mother_id`, `father_id`, `blood_type`, `address`, `barangay_id`, `contact_number`, `emergency_contact_name`, `emergency_contact_number`, `insurance_number`, `occupation`, `income_level`, `medical_history`, `registration_date`, `suffix`, `maiden_name`, `birth_place`, `dswd_nhts`, `is_4ps_member`, `education_level`, `employment_status`, `family_role`, `is_philhealth_member`, `philhealth_type`, `philhealth_number`, `pcb_member`) VALUES
(1, 'P001', 'Mark', NULL, 'Santos', '1990-05-12', 'Male', NULL, NULL, NULL, NULL, NULL, 'Zone 1', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-28', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, 0),
(2, 'P002', 'Ana', NULL, 'Garcia', '1985-07-22', 'Female', NULL, NULL, NULL, NULL, NULL, 'Street 5', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-28', NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`) VALUES
(1, 'manage_users', 'Can create, edit, and delete users'),
(2, 'view_patients', 'Can view patient list and details'),
(3, 'manage_patients', 'Can create and edit patients'),
(4, 'view_medical_records', 'Can view medical records'),
(5, 'manage_medical_records', 'Can create and edit medical records'),
(6, 'view_inventory', 'Can view medicine inventory'),
(7, 'manage_inventory', 'Can manage medicine inventory'),
(8, 'distribute_medicine', 'Can distribute medicine to patients'),
(9, 'view_reports', 'Can view system reports'),
(10, 'export_data', 'Can export data from the system');

-- --------------------------------------------------------

--
-- Table structure for table `report_alerts`
--

CREATE TABLE `report_alerts` (
  `id` int(11) NOT NULL,
  `type` enum('Warning','Critical','Info') NOT NULL,
  `message` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'Admin', 'Full system access'),
(2, 'Clinician', 'Manage patients and medical records'),
(3, 'Pharmacy', 'Manage medicine inventory and distribution'),
(38, 'Doctor', 'Medical professional'),
(39, 'Nurse', 'Nursing staff'),
(40, 'MidWife', 'Midwifery services'),
(41, 'Other', 'Other healthcare staff');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 8),
(2, 9),
(2, 10),
(3, 2),
(3, 4),
(3, 6),
(3, 7),
(3, 8),
(3, 9),
(38, 2),
(38, 3),
(38, 4),
(38, 5),
(38, 6),
(38, 8),
(38, 9),
(38, 10),
(39, 2),
(39, 4),
(39, 6),
(39, 7),
(39, 8),
(39, 9),
(40, 2),
(40, 4),
(40, 6),
(40, 7),
(40, 8),
(40, 9),
(41, 2),
(41, 4),
(41, 6),
(41, 7),
(41, 8),
(41, 9);

-- --------------------------------------------------------

--
-- Table structure for table `vitals_record`
--

CREATE TABLE `vitals_record` (
  `id` int(11) NOT NULL,
  `medical_record_id` int(11) NOT NULL,
  `bp` varchar(10) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `respiratory_rate` int(11) DEFAULT NULL,
  `pulse_rate` int(11) DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `clinician_id` (`clinician_id`);

--
-- Indexes for table `barangay_table`
--
ALTER TABLE `barangay_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clinicians_table`
--
ALTER TABLE `clinicians_table`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `disease`
--
ALTER TABLE `disease`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `disease_trends`
--
ALTER TABLE `disease_trends`
  ADD PRIMARY KEY (`id`),
  ADD KEY `disease_id` (`disease_id`),
  ADD KEY `barangay_id` (`barangay_id`);

--
-- Indexes for table `family_members`
--
ALTER TABLE `family_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `medical_record`
--
ALTER TABLE `medical_record`
  ADD PRIMARY KEY (`id`),
  ADD KEY `barangay_id` (`barangay_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `clinician_id` (`clinician_id`),
  ADD KEY `disease_id` (`disease_id`);

--
-- Indexes for table `medicine`
--
ALTER TABLE `medicine`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medicine_distribution`
--
ALTER TABLE `medicine_distribution`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicine_id` (`medicine_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `clinician_id` (`clinician_id`);

--
-- Indexes for table `medicine_inventory`
--
ALTER TABLE `medicine_inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicine_id` (`medicine_id`);

--
-- Indexes for table `patients_table`
--
ALTER TABLE `patients_table`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `patient_code` (`patient_code`),
  ADD KEY `barangay_id` (`barangay_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `report_alerts`
--
ALTER TABLE `report_alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `vitals_record`
--
ALTER TABLE `vitals_record`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medical_record_id` (`medical_record_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `barangay_table`
--
ALTER TABLE `barangay_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `clinicians_table`
--
ALTER TABLE `clinicians_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `disease`
--
ALTER TABLE `disease`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `disease_trends`
--
ALTER TABLE `disease_trends`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `family_members`
--
ALTER TABLE `family_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_record`
--
ALTER TABLE `medical_record`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `medicine`
--
ALTER TABLE `medicine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `medicine_distribution`
--
ALTER TABLE `medicine_distribution`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medicine_inventory`
--
ALTER TABLE `medicine_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients_table`
--
ALTER TABLE `patients_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5721;

--
-- AUTO_INCREMENT for table `report_alerts`
--
ALTER TABLE `report_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2825;

--
-- AUTO_INCREMENT for table `vitals_record`
--
ALTER TABLE `vitals_record`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`clinician_id`) REFERENCES `clinicians_table` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `clinicians_table`
--
ALTER TABLE `clinicians_table`
  ADD CONSTRAINT `clinicians_table_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_10` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_100` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_101` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_102` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_103` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_104` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_105` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_106` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_107` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_108` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_109` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_11` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_110` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_111` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_112` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_113` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_114` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_115` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_116` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_117` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_118` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_119` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_12` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_120` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_121` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_122` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_123` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_124` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_125` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_126` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_127` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_128` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_129` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_13` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_130` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_131` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_132` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_133` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_134` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_135` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_136` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_137` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_138` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_139` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_14` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_140` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_141` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_142` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_143` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_144` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_145` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_146` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_147` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_148` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_149` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_15` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_150` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_151` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_152` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_153` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_154` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_155` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_156` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_157` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_158` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_159` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_16` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_160` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_161` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_162` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_163` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_164` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_165` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_166` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_167` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_168` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_169` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_17` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_170` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_171` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_172` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_173` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_174` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_175` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_176` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_177` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_178` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_179` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_18` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_180` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_181` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_182` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_183` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_184` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_185` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_186` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_187` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_188` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_189` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_19` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_190` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_191` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_192` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_193` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_194` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_195` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_196` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_197` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_198` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_199` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_20` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_200` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_201` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_202` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_203` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_204` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_205` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_206` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_207` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_208` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_209` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_21` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_210` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_211` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_212` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_213` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_214` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_215` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_216` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_217` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_218` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_219` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_22` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_220` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_221` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_222` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_223` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_224` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_225` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_226` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_227` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_228` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_229` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_23` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_230` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_231` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_232` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_233` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_234` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_235` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_236` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_237` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_238` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_239` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_24` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_240` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_241` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_242` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_243` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_244` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_245` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_246` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_247` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_248` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_249` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_25` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_250` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_251` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_252` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_253` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_254` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_255` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_256` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_257` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_258` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_259` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_26` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_260` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_261` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_262` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_263` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_264` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_265` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_266` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_267` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_268` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_269` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_27` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_270` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_271` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_272` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_273` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_274` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_275` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_276` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_277` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_278` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_279` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_28` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_280` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_281` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_282` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_283` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_284` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_285` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_286` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_287` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_288` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_289` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_29` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_290` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_291` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_292` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_293` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_294` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_295` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_296` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_297` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_298` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_299` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_3` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_30` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_300` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_301` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_302` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_303` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_304` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_305` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_306` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_307` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_308` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_309` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_31` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_310` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_311` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_312` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_313` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_314` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_315` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_316` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_317` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_318` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_319` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_32` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_320` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_321` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_322` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_323` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_324` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_325` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_326` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_327` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_328` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_329` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_33` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_330` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_331` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_332` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_333` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_334` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_335` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_336` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_337` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_338` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_339` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_34` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_340` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_341` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_342` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_343` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_344` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_345` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_346` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_347` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_348` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_349` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_35` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_350` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_351` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_352` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_353` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_354` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_355` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_356` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_357` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_358` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_359` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_36` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_360` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_361` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_362` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_363` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_364` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_365` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_366` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_367` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_368` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_369` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_37` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_370` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_371` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_372` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_373` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_374` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_375` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_376` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_377` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_378` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_379` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_38` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_380` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_381` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_382` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_383` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_384` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_385` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_386` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_387` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_388` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_389` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_39` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_390` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_391` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_392` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_393` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_394` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_395` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_396` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_397` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_398` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_399` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_4` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_40` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_400` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_401` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_402` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_403` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_404` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_405` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_406` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_407` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_408` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_409` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_41` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_410` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_411` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_412` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_413` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_414` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_415` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_416` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_417` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_418` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_419` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_42` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_420` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_421` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_422` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_423` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_424` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_425` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_426` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_427` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_428` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_429` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_43` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_430` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_431` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_432` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_433` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_434` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_435` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_436` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_437` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_438` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_439` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_44` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_440` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_441` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_442` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_443` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_444` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_445` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_446` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_447` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_448` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_449` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_45` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_450` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_451` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_452` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_453` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_454` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_455` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_456` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_457` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_458` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_459` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_46` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_460` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_461` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_462` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_463` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_464` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_465` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_466` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_467` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_468` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_469` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_47` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_470` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_471` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_472` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_473` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_474` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_475` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_476` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_477` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_478` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_479` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_48` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_480` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_481` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_482` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_483` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_484` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_485` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_486` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_487` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_488` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_489` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_49` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_490` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_491` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_492` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_493` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_494` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_495` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_496` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_497` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_498` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_499` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_5` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_50` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_500` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_501` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_502` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_503` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_504` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_505` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_506` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_507` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_508` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_509` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_51` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_510` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_511` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_512` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_513` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_514` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_515` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_516` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_517` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_518` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_519` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_52` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_520` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_521` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_522` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_523` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_524` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_525` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_526` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_527` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_528` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_529` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_53` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_530` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_531` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_532` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_533` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_534` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_535` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_536` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_537` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_538` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_539` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_54` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_540` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_541` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_542` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_543` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_544` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_545` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_546` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_547` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_548` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_549` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_55` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_550` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_551` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_552` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_553` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_554` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_555` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_556` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_557` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_558` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_559` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_56` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_560` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_561` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_562` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_563` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_564` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_565` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_566` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_567` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_568` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_569` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_57` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_58` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_59` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_6` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_60` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_61` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_62` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_63` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_64` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_65` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_66` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_67` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_68` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_69` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_7` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_70` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_71` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_72` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_73` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_74` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_75` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_76` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_77` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_78` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_79` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_8` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_80` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_81` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_82` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_83` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_84` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_85` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_86` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_87` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_88` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_89` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_9` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_90` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_91` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_92` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_93` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_94` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_95` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_96` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_97` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_98` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `clinicians_table_ibfk_99` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `disease_trends`
--
ALTER TABLE `disease_trends`
  ADD CONSTRAINT `disease_trends_ibfk_1` FOREIGN KEY (`disease_id`) REFERENCES `disease` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `disease_trends_ibfk_2` FOREIGN KEY (`barangay_id`) REFERENCES `barangay_table` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `family_members`
--
ALTER TABLE `family_members`
  ADD CONSTRAINT `family_members_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients_table` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_record`
--
ALTER TABLE `medical_record`
  ADD CONSTRAINT `medical_record_ibfk_1` FOREIGN KEY (`barangay_id`) REFERENCES `barangay_table` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_record_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients_table` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_record_ibfk_3` FOREIGN KEY (`clinician_id`) REFERENCES `clinicians_table` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_record_ibfk_4` FOREIGN KEY (`disease_id`) REFERENCES `disease` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medicine_distribution`
--
ALTER TABLE `medicine_distribution`
  ADD CONSTRAINT `medicine_distribution_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicine` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medicine_distribution_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients_table` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medicine_distribution_ibfk_3` FOREIGN KEY (`clinician_id`) REFERENCES `clinicians_table` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medicine_inventory`
--
ALTER TABLE `medicine_inventory`
  ADD CONSTRAINT `medicine_inventory_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicine` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patients_table`
--
ALTER TABLE `patients_table`
  ADD CONSTRAINT `patients_table_ibfk_1` FOREIGN KEY (`barangay_id`) REFERENCES `barangay_table` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vitals_record`
--
ALTER TABLE `vitals_record`
  ADD CONSTRAINT `vitals_record_ibfk_1` FOREIGN KEY (`medical_record_id`) REFERENCES `medical_record` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
