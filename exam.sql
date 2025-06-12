-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2024 at 02:02 PM
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
-- Database: `exam`
--

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `captured_image` varchar(255) NOT NULL,
  `certificate` varchar(255) NOT NULL,
  `score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `exam_given` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `email`, `phone_number`, `password`, `timestamp`, `captured_image`, `certificate`, `score`, `exam_given`) VALUES
(154, 'b', 'b@com', '111', '$2y$10$4zYs7fMlaHsw94zFpOr8LOVl3bExCJJbJzb4X5LrXiJUdpcgqdaQ.', '2024-09-25 21:32:56', 'uploads/img_66f43430d770e0.96576868.jpg', 'certificates/Certificate_b.pdf', 80.00, 'DIGITAL MARKETING'),
(257, 'suryadipta', 'sd@gmail.com', '8250419219', '$2y$10$JS820uzXXmxopuHv2wD67edQJj47JL.csQQ4t0GklFUYn0RdnqUFK', '2024-09-24 10:01:31', 'uploads/img_66f240a387da70.42682654.jpg', '', 12.00, NULL),
(422, 'v', 'v@com', '111', '$2y$10$XwGGNi7cuxzqcyPXIcB9KODo8DuDw2IOp/zAokMrLGzRailuM7cyy', '2024-09-26 21:56:25', 'uploads/img_66f58b31a10f41.25218473.jpg', '', 0.00, NULL),
(423, 'k', 'k@com', '111', '$2y$10$VxxxvoyeDYrOC.aZ8tcnde1KZtIzwa92kl2HVjbbGdP4Prp5ZYa56', '2024-09-27 10:35:02', 'uploads/img_66f63cfe73ad32.18685768.jpg', '', 0.00, NULL),
(424, 'a', 'a@com', '111', '$2y$10$k4GbDF0CS75Z2CMMVM6U2.nobuwCeNuLUVh6e5pdrhMwB51kHPEdC', '2024-09-27 10:35:38', 'uploads/img_66f63d22c3a898.58525849.jpg', '', 0.00, NULL),
(425, 'ddd', 'Palash@com', '0250419219', '$2y$10$NwqAzWVGkV0Pj1maFKmefuuESLcEJpcqeIPcWh9BbfbeLqjBjOnX.', '2024-10-01 15:04:48', 'uploads/img_66fbc238e3ff91.85095695.jpg', '', 0.00, NULL),
(426, 'Palash Kundu', 'palashyt99@gmail.com', '8250419219', '$2y$10$ST97PGxxfw1hCOuJxNGyVerEYFbABkmDALGJUQYPAskVmId2wnvdS', '2024-10-01 15:18:15', 'uploads/img_66fbc55fabc5e5.78595857.jpg', '', 0.00, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=427;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
