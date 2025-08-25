-- Updated Medistat Database with Integrated Drug Data
-- This file includes the original medistat1.sql structure plus:
-- 1. medicine_categories table
-- 2. Enhanced medicine table with category_id
-- 3. All drug data migrated from drug_categories.sql and drugs.sql

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
-- Table structure for table `medicine_categories`
--

CREATE TABLE `medicine_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_categories`
--

INSERT INTO `medicine_categories` (`id`, `name`, `created_at`) VALUES
(13, 'Mental Health Medicine', '2025-07-08 05:07:50'),
(14, 'Lifestyle related disease prevention and control', '2025-07-08 06:03:42'),
(18, 'National Family Planning', '2025-07-08 06:40:07'),
(19, 'Nutrition', '2025-07-08 06:50:07'),
(20, 'National Tuberculosis Control', '2025-07-08 06:51:21'),
(21, 'National Immunization', '2025-07-08 06:58:50'),
(22, 'Integrated Helminth Control (ihcp)', '2025-07-08 07:51:56'),
(23, 'National Leprosy Prevention And Action Control Program (nlpcp)', '2025-08-12 03:43:42'),
(24, 'Family Health Cluster', '2025-08-12 03:50:15'),
(25, 'Philippine Integrated Management Of Severe Acute Malnutrition (pimam)', '2025-08-12 06:05:26'),
(26, 'Food And Water -borne Disease Program (fawdp)', '2025-08-12 06:09:02'),
(27, 'National Hiv/aids And Sti Prevention And Control Program', '2025-08-12 06:11:38'),
(28, 'Emerging And Re-emerging Infectious Disease Program (ereid)', '2025-08-12 06:15:39'),
(29, 'Traditional Complementary And Alternative Medicine', '2025-08-12 06:29:27'),
(30, 'Environmental Occupation Health Office', '2025-08-12 06:47:35'),
(31, 'Lgu Procured Commodities', '2025-08-12 06:50:35');

-- --------------------------------------------------------

--
-- Table structure for table `medicine` (Enhanced with category_id)
--

CREATE TABLE `medicine` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine` (Enhanced with all drug data)
--

INSERT INTO `medicine` (`id`, `name`, `description`, `category`, `category_id`, `quantity`, `unit`, `expiry_date`) VALUES
(1, 'Paracetamol', NULL, 'Antipyretic', 31, 1000, 'tablets', '2026-01-01'),
(2, 'ORS', NULL, 'Hydration', 26, 500, 'sachet', '2025-08-01'),
(3, 'Biperiden HCI 2mg tab', NULL, 'Mental Health Medicine', 13, 100, 'tablets', '2025-01-08'),
(4, 'Carbamazepine 200 mg tab', NULL, 'Mental Health Medicine', 13, 100, 'tablets', '2025-01-07'),
(5, 'Diphenhydramine HCI 50mg 1ml ampule', NULL, 'Mental Health Medicine', 13, 100, 'ampules', '2025-01-08'),
(6, 'Clozapine 100 mg tab', NULL, 'Mental Health Medicine', 13, 100, 'tablets', '2025-12-07'),
(7, 'Escitalopram Oxalate 10mg tab', NULL, 'Mental Health Medicine', 13, 100, 'tablets', '2025-01-07'),
(8, 'Divalproex Sodium 500 ng ER tab', NULL, 'Mental Health Medicine', 13, 100, 'tablets', '2025-07-01'),
(9, 'Fluphenazine decanoate 25mg/mL , 1ml amp', NULL, 'Mental Health Medicine', 13, 100, 'ampules', '2025-07-01'),
(10, 'Olanzapine 10 mg tab/cap', NULL, 'Mental Health Medicine', 13, 100, 'tablets', '2025-07-01'),
(11, 'Quetiapine Fumarate 200 mg tab', NULL, 'Mental Health Medicine', 13, 100, 'tablets', '2025-07-01'),
(12, 'Sertraline HCI 50 mg tab', NULL, 'Mental Health Medicine', 13, 100, 'tablets', '2025-07-01'),
(13, 'Risperidone 2 mg tab', NULL, 'Mental Health Medicine', 13, 100, 'tablets', '2025-07-01'),
(14, 'Risperidone 2mg Oral Disintegration tablet', NULL, 'Mental Health Medicine', 13, 100, 'tablets', '2025-07-01'),
(15, 'Valproic acid 250 mg/5mL, 120 mL syrup', NULL, 'Mental Health Medicine', 13, 100, 'bottles', '2025-07-01'),
(16, 'Haloperidol 5mg tablet', NULL, 'Mental Health Medicine', 13, 100, 'tablets', '2025-07-01'),
(17, 'Amlodipine 5mg tab', NULL, 'Lifestyle related disease prevention and control', 14, 100, 'tablets', '2025-07-01'),
(18, 'Amlodipine 10mg tab', NULL, 'Lifestyle related disease prevention and control', 14, 100, 'tablets', '2025-07-01'),
(19, 'Losartan 50mg tab', NULL, 'Mental Health Medicine', 13, 100, 'tablets', '2025-07-01'),
(20, 'Human regular insulin ui/mL solution for injection 10ml', NULL, 'Lifestyle related disease prevention and control', 14, 100, 'vials', '2025-07-01'),
(21, 'Biphasic isophane human insulin 70/30 rDNA 100 UI/mL 10ml solution for injection', NULL, 'Lifestyle related disease prevention and control', 14, 100, 'vials', '2025-07-01'),
(22, 'Aspirin 80mg', NULL, 'Lifestyle related disease prevention and control', 14, 100, 'tablets', '2025-07-01'),
(23, 'Metformin 500mg Tab', NULL, 'Lifestyle related disease prevention and control', 14, 100, 'tablets', '2025-07-01'),
(24, 'Simvastatin 20mg Tab', NULL, 'Lifestyle related disease prevention and control', 14, 100, 'tablets', '2025-07-01'),
(25, 'Cotrimoxazole 400/80mg Per 5mL Suspension 60ml', NULL, 'Lifestyle related disease prevention and control', 14, 100, 'bottles', '2025-07-01'),
(26, 'Blood Glucose Test Strips 25s', NULL, 'Lifestyle related disease prevention and control', 14, 100, 'strips', '2025-07-01'),
(27, '3in1 Multifunction Monitoring System GLU CHO URIC', NULL, 'Lifestyle related disease prevention and control', 14, 100, 'units', '2025-07-01'),
(28, 'Depo Medroxyprogesterone Acetate (DMPA) Vial', NULL, 'National Family Planning', 18, 100, 'vials', '2025-07-01'),
(29, 'Ethinylestradiol + Levonorgestrel/Combines Oral Contraceptives (COC) Cycles', NULL, 'National Family Planning', 18, 100, 'cycles', '2025-07-01'),
(30, 'Lynestrenol/Progestin Only Pills (POP) Cycles', NULL, 'National Family Planning', 18, 100, 'cycles', '2025-07-01'),
(31, 'Etonogestrel 68mg/Progestin Only Pills (PSI)', NULL, 'National Family Planning', 18, 100, 'units', '2025-07-01'),
(32, 'ProgestinSubdermal  Implant (PSI) Ancillary Kit', NULL, 'National Family Planning', 18, 100, 'kits', '2025-07-01'),
(33, 'Male Condoms Pieces', NULL, 'National Family Planning', 18, 100, 'pieces', '2025-07-01'),
(34, 'Viatamin A 200,000 IU Capsule', NULL, 'Nutrition', 19, 100, 'capsules', '2025-07-01'),
(35, 'Ethambutol 400mg Tab', NULL, 'National Tuberculosis Control', 20, 100, 'tablets', '2025-07-10'),
(36, 'Isoniazid 200mg/5mL Bottle (children)', NULL, 'National Tuberculosis Control', 20, 100, 'bottles', '2025-07-01'),
(37, 'Rifampicin 200mg/5mL, 120 ML Bottle', NULL, 'National Tuberculosis Control', 20, 100, 'bottles', '2025-07-01'),
(38, 'Isoniazid 75mg + Rifampicin 150mg Tablet (FDC 2 Adult) Tablet', NULL, 'National Tuberculosis Control', 20, 100, 'tablets', '2025-07-01'),
(39, 'Rifampicin 150mg + Isoniazid 75mg + Pyrazinamide 400mg + Ethambutol 250mg (FDC 4 Adult) Tablet', NULL, 'National Tuberculosis Control', 20, 100, 'tablets', '2025-07-01'),
(40, 'Rifampentine 300mg + Isoniazid 300mg', NULL, 'National Tuberculosis Control', 20, 100, 'tablets', '2025-07-01'),
(41, 'Purified Protein Derivative (PPD 2 TU/0.1ml) Vial', NULL, 'National Tuberculosis Control', 20, 100, 'vials', '2025-07-01'),
(42, 'BCG Vaccine Vial', NULL, 'National Immunization', 21, 100, 'vials', '2025-07-01'),
(43, 'Bivalent Oral Polio Vaccine (BOPV) Vial', NULL, 'National Immunization', 21, 100, 'vials', '2025-07-01'),
(44, 'Diphtherial Tetanus Toxoids (TD) Vial', NULL, 'National Immunization', 21, 100, 'vials', '2025-07-01'),
(45, 'Hepatitis B Vial', NULL, 'National Immunization', 21, 100, 'vials', '2025-07-01'),
(46, 'Human Papillomavirus Vaccine (HPV) 2nd Dose Old', NULL, 'National Immunization', 21, 100, 'vials', '2025-07-01'),
(47, 'Human Papillomavirus Vaccine (HPV) Vial 1st Dose', NULL, 'National Immunization', 21, 100, 'vials', '2025-07-01'),
(48, 'Inactivated Polio Vaccine (IPV) Vial', NULL, 'National Immunization', 21, 100, 'vials', '2025-07-01'),
(49, 'Influenza Vaccine (FLU) Vial', NULL, 'National Immunization', 21, 100, 'vials', '2025-07-01'),
(50, 'Measles Mumps Rubella (MMR) Vial', NULL, 'National Immunization', 21, 100, 'vials', '2025-07-01'),
(51, 'Measles Rubella (MR) Vial', NULL, 'National Immunization', 21, 100, 'vials', '2025-07-01'),
(52, 'DPT-hepb-hib (pentavalent) Vaccine Vial', NULL, 'National Immunization', 21, 100, 'vials', '2025-07-01'),
(53, 'Pneumoccocal Conjugate Vaccine (PCV10) Vial', NULL, 'National Immunization', 21, 100, 'vials', '2025-07-01'),
(54, 'Pneumoccocal Polysaccharide Vaccine (PPV23)', NULL, 'National Immunization', 21, 100, 'vials', '2025-07-01'),
(55, 'Sterile Water For Injection (diluent)', NULL, 'National Immunization', 21, 100, 'vials', '2025-07-10'),
(56, 'Adverse Event Following Immunization (AEFI) Kit', NULL, 'National Immunization', 21, 100, 'kits', '2025-04-01'),
(57, 'Chlorine Tabs', NULL, 'National Immunization', 21, 100, 'tablets', '2025-07-01'),
(58, 'Adhesive Plastic Bandage Strip', NULL, 'National Immunization', 21, 100, 'strips', '2025-07-01'),
(59, 'Cotton Balls 300s Pack', NULL, 'National Immunization', 21, 100, 'packs', '2025-04-01'),
(60, 'Alchohol', NULL, 'National Immunization', 21, 100, 'bottles', '2025-07-01'),
(61, 'Albendazole 400mg Chewable Tablet', NULL, 'Integrated Helminth Control (ihcp)', 22, 100, 'tablets', '0000-00-00'),
(62, 'Mebendazole 500mg Tablet', NULL, 'Integrated Helminth Control (ihcp)', 22, 100, 'tablets', '2025-01-08'),
(63, 'Ketoconazole 20 Mg/g 15g CREAM', NULL, 'National Leprosy Prevention And Action Control Program (nlpcp)', 23, 100, 'tubes', '2025-08-25'),
(64, 'Fusidate Na/Fusidic Acid 2% 10g Cream', NULL, 'National Leprosy Prevention And Action Control Program (nlpcp)', 23, 100, 'tubes', '2025-08-25'),
(65, 'Povidone Lodine 10% Topical SoIn 20ml', NULL, 'National Leprosy Prevention And Action Control Program (nlpcp)', 23, 100, 'bottles', '2025-08-25'),
(66, 'Permethrin 50mg 60ml Lotion', NULL, 'National Leprosy Prevention And Action Control Program (nlpcp)', 23, 100, 'bottles', '2025-08-25'),
(67, 'Petroleum Jelly', NULL, 'National Leprosy Prevention And Action Control Program (nlpcp)', 23, 100, 'jars', '2025-08-25'),
(68, 'Ferrous Sulfate (30mg/ 5ml) 60mL Syrup', NULL, 'Family Health Cluster', 24, 100, 'bottles', '2025-08-25'),
(69, 'Hot Compress Bag', NULL, 'Family Health Cluster', 24, 100, 'bags', '2025-08-25'),
(70, 'Zinc Sulfate 20mg/5ml Syrup 60ml', NULL, 'Family Health Cluster', 24, 100, 'bottles', '2025-08-25'),
(71, 'Ciprofloxacin 500mg Tab', NULL, 'Family Health Cluster', 24, 100, 'tablets', '2025-08-25'),
(72, 'Metoprolol 100mg Tab', NULL, 'Family Health Cluster', 24, 100, 'tablets', '2025-08-25'),
(73, 'Azithromycin 200mg/ 15ml Oral Suspension', NULL, 'Family Health Cluster', 24, 100, 'bottles', '2025-08-25'),
(74, 'Ready To Use Supplement Food (RUSF) Children', NULL, 'Philippine Integrated Management Of Severe Acute Malnutrition (pimam)', 25, 100, 'packs', '2025-08-25'),
(75, 'Ready To Use Supplementary Food (RUSF) Moms', NULL, 'Philippine Integrated Management Of Severe Acute Malnutrition (pimam)', 25, 100, 'packs', '2025-08-25'),
(76, 'Oral Rehydration Salts Sachet', NULL, 'Food And Water -borne Disease Program (fawdp)', 26, 100, 'sachets', '2025-08-25'),
(77, 'Troclosene Sodium 67mg', NULL, 'Food And Water -borne Disease Program (fawdp)', 26, 100, 'tablets', '2025-08-25'),
(78, 'Lubricant 5ml', NULL, 'National Hiv/aids And Sti Prevention And Control Program', 27, 100, 'tubes', '2025-08-25'),
(79, 'Penicillin G Benzathine Benzylpenicillin', NULL, 'National Hiv/aids And Sti Prevention And Control Program', 27, 100, 'vials', '2025-08-25'),
(80, 'Cotrimoxazole 960mg Tab', NULL, 'National Hiv/aids And Sti Prevention And Control Program', 27, 100, 'tablets', '2025-08-25'),
(81, 'Oseltamivir Phosphate 75mg', NULL, 'Emerging And Re-emerging Infectious Disease Program (ereid)', 28, 100, 'capsules', '2025-08-25'),
(82, 'Alcohol 70% 1 Gallon', NULL, 'Emerging And Re-emerging Infectious Disease Program (ereid)', 28, 99, 'gallons', '2025-08-25'),
(83, 'Ethyl Alcohol 70% 1 Gallon', NULL, 'Emerging And Re-emerging Infectious Disease Program (ereid)', 28, 100, 'gallons', '2025-08-25'),
(84, 'Diphenhydramine Hydrochloride 12.5g/5ml Syrup 60ml', NULL, 'Emerging And Re-emerging Infectious Disease Program (ereid)', 28, 98, 'bottles', '2025-08-25'),
(85, 'Diphenhydramine HCI 50mg Cap', NULL, 'Emerging And Re-emerging Infectious Disease Program (ereid)', 28, 100, 'capsules', '2025-08-25'),
(86, 'N95 Mask', NULL, 'Emerging And Re-emerging Infectious Disease Program (ereid)', 28, 100, 'masks', '2025-08-25'),
(87, 'Vitex Negundo Lagundi 300mg Tab', NULL, 'Traditional Complementary And Alternative Medicine', 29, 100, 'tablets', '2025-08-25'),
(88, 'Blumeas Balsamifera Sambong 250mg Tab', NULL, 'Traditional Complementary And Alternative Medicine', 29, 100, 'tablets', '2025-08-25'),
(89, 'Troclosene Sodium 67mg', NULL, 'Environmental Occupation Health Office', 30, 100, 'tablets', '2025-08-25'),
(90, 'Sodium Dichloroisocyanurate 2500mg Tab', NULL, 'Environmental Occupation Health Office', 30, 100, 'tablets', '2025-08-25'),
(91, 'Amoxicillin 250mg/5ml 60ml Susp', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(92, 'Amoxicillin 100mg/ml 10ml Drops', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(93, 'Ambroxol HCL 30mg/5ml 60ml Drops', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(94, 'Ambroxol HCI 6mg/ml 15ml Drops', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(95, 'Ascorbic Acid 100mg/5ml 60ml Syrup', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(96, 'Ascorbic Acid 100mg/ml 60ml Syrup', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-26'),
(97, 'Paracetamol 250mg/5ml 60ml Syrup', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(98, 'Paracetamol 100mg/ml 15ml Syrup', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(99, 'Cetirizine HCI 5mg/5ml 60ml Syrup', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(100, 'Cetirizine Dihydrochloride 2.5mg/ml 10mg Drops', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(101, 'Lagundi 300mg/ml 60ml Syrup', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(102, 'Guaifenesin Phenylpropanolamine Hydrochloride Chlorphenamine Maleate 100mg/6.5mg/2mg/5ml 60 Ml Syrup', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(103, 'Phenylpropanolamine Hydrochloride Chlorphenamine Maleate Paracetamol 6.25mg/500mg/100mg/ml 15ml Drops (SYMDEXD)', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(104, 'Multivitamins 15ml Drops', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(105, 'Cefuroxime Axetil 250mg/5ml', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2525-08-25'),
(106, 'Dicycloverine HCI 10mg/5ml 60ml Syrup', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(107, 'HNBB 1mg/ml 5mg/5ml 60ml Syrup', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(108, 'Metoclopramide HCI 5mg/5ml 60ml Syrup', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(109, 'Mefenamic Acid 50mg/5ml 60ml Syrup', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(110, 'Cloxacillin Sodium 250mg/5ml 60ml Susp', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(111, 'Metronidazole 125mg/5ml 60ml Syrup', NULL, 'Lgu Procured Commodities', 31, 100, 'bottles', '2025-08-25'),
(112, 'Amoxicillin 500mg Cap', NULL, 'Lgu Procured Commodities', 31, 100, 'capsules', '2025-08-25'),
(113, 'Azithromycin 500mg Tab', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(114, 'Cefixime 200mg Cap', NULL, 'Lgu Procured Commodities', 31, 100, 'capsules', '2025-08-25'),
(115, 'Metronidazole 500mg Tab', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(116, 'Metoclopramide 10mg', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(117, 'Tramadol HCI 50mg Cap', NULL, 'Lgu Procured Commodities', 31, 100, 'capsules', '2025-08-25'),
(118, 'AImgoH 200mg/200mg Tab', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(119, 'Loperamide 2mg Cap', NULL, 'Lgu Procured Commodities', 31, 100, 'capsules', '2025-08-25'),
(120, 'HNBB 10mg Tab', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(121, 'ISDN 10 10mg', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(122, 'Cloxacillin 500mg', NULL, 'Lgu Procured Commodities', 31, 100, 'capsules', '2025-08-25'),
(123, 'Salbutamol 2mg Tab', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(124, 'Clonidine 75mcg Tab', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(125, 'Lasortan 100mg', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(126, 'ISDN 5 5mg', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(127, 'Montelukast 10mg', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(128, 'Chlorphenamine Maleate 4mg', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(129, 'Salbutamol Nebule', NULL, 'Lgu Procured Commodities', 31, 100, 'nebules', '2025-08-25'),
(130, 'Cetirizine 10mg Tab', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(131, 'Amlodipine 10mg', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(132, 'Erythromycin 500mg Tab', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(133, 'Lagundi 600mg Tab', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(134, 'Omeprazole 40mg Tab', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(135, 'Prednisone 20mg', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(136, 'Dicycloverine10mg', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(137, 'Disclofenac Sodium', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(138, 'Cinnarizine 25mg', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(139, 'Fe+FA Tab', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(140, 'Prednisone 10mg', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(141, 'Cefuroxime', NULL, 'Lgu Procured Commodities', 31, 100, 'capsules', '2025-08-25'),
(142, 'Clindamycin 300mg Cap', NULL, 'Lgu Procured Commodities', 31, 100, 'capsules', '2025-08-25'),
(143, 'Carbamazepine 200mg Tab', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25'),
(144, 'Chlorpromazine HCI 200mg Tab', NULL, 'Lgu Procured Commodities', 31, 100, 'tablets', '2025-08-25');

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table `medicine_categories`
--
ALTER TABLE `medicine_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `medicine`
--
ALTER TABLE `medicine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `medicine_categories`
--
ALTER TABLE `medicine_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `medicine`
--
ALTER TABLE `medicine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `medicine`
--
ALTER TABLE `medicine`
  ADD CONSTRAINT `medicine_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `medicine_categories` (`id`) ON DELETE SET NULL;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

