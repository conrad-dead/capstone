-- Create medicine_distribution table for tracking medicine distribution
-- This table links patients, doctors, and medicines for accountability

CREATE TABLE IF NOT EXISTS `medicine_distribution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `prescription_date` date NOT NULL,
  `distribution_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','completed','cancelled') NOT NULL DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_patient_id` (`patient_id`),
  KEY `idx_doctor_id` (`doctor_id`),
  KEY `idx_medicine_id` (`medicine_id`),
  KEY `idx_distribution_date` (`distribution_date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_distribution_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_distribution_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_distribution_medicine` FOREIGN KEY (`medicine_id`) REFERENCES `medicine` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample distribution data for testing
INSERT INTO `medicine_distribution` (`patient_id`, `doctor_id`, `medicine_id`, `quantity`, `prescription_date`, `distribution_date`, `notes`, `status`) VALUES
(1, 2, 1, 10, '2024-01-15', '2024-01-15', 'Patient has fever and cough', 'completed'),
(2, 2, 3, 5, '2024-01-16', '2024-01-16', 'For hypertension management', 'completed'),
(3, 2, 2, 15, '2024-01-17', '2024-01-17', 'Diabetes medication', 'completed'),
(1, 2, 4, 8, '2024-01-18', '2024-01-18', 'Pain relief for back pain', 'completed'),
(2, 2, 1, 12, '2024-01-19', '2024-01-19', 'Follow-up prescription', 'completed');

-- Add indexes for better performance
CREATE INDEX `idx_distribution_composite` ON `medicine_distribution` (`patient_id`, `doctor_id`, `medicine_id`);
CREATE INDEX `idx_distribution_date_range` ON `medicine_distribution` (`distribution_date`, `status`);
