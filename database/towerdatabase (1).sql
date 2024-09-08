-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 08, 2024 at 07:53 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `towerdatabase`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pump`
--

CREATE TABLE `pump` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `towerid` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sensor`
--

CREATE TABLE `sensor` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `towerid` bigint(20) UNSIGNED NOT NULL,
  `pH` varchar(255) NOT NULL,
  `temperature` varchar(255) NOT NULL,
  `nutrientlevel` varchar(255) NOT NULL,
  `light` varchar(255) NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `iv` text NOT NULL,
  `k` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sensor_data_history`
--

CREATE TABLE `sensor_data_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `towerid` bigint(20) UNSIGNED NOT NULL,
  `OwnerID` tinyint(3) UNSIGNED NOT NULL,
  `sensor_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`sensor_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_adminaccounts`
--

CREATE TABLE `tbl_adminaccounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_adminaccounts`
--

INSERT INTO `tbl_adminaccounts` (`id`, `name`, `username`, `email`, `password`, `status`, `created_at`, `updated_at`) VALUES
(1, 'eyJpdiI6IkltanMyNnZsZWFOWWJ5QnE2RE1LUWc9PSIsInZhbHVlIjoidEdUYk5xVzE1Z3JsRmJVakVqeFdlZz09IiwibWFjIjoiMTNmZTdkMDZjZDIwM2FhZDVkOTczNzk3NDE4MGZmOTQ5NjQ0ZjhiNjIyMjJkOWZhMGNiZGQzNDAwZTI5ODA3YyIsInRhZyI6IiJ9', 'eyJpdiI6InBRRUFuZnI4OTNkLy9iVWRzSWJ5Zmc9PSIsInZhbHVlIjoiNXdtV202WGFWNHA5R0o1R0lYcFhzUT09IiwibWFjIjoiZWIzMTNlMjE3MmRiZGI4NTY1ZTI0YzdjOTkwZjk0OWM1YzA3MjgxNmYyMGQ0MzkwZmIwYzE3NjdjNTFmNTVhMCIsInRhZyI6IiJ9', 'eyJpdiI6IjVJVHdERlBrUHBYcUt3SExLaW5VbFE9PSIsInZhbHVlIjoiV2U2cCtyTXJJWmhmYmZXZWxaZFBJOXVRRkRZd2FzaHJVWVc4MHJaOFpzbz0iLCJtYWMiOiJlMjA2NzBjYTA0YTkzYzA0NmFkY2UyOGQ4MTU0YWQ3OWI0OTI5ZmE3NTE5M2VhODcwYTY4ODk4NjNkMTRiMTgzIiwidGFnIjoiIn0=', '$2y$12$vd62GLucriI28.1YINbTTuDuhjSkRKJRBSgVjBd6MMwTn2H6iCFwG', 'eyJpdiI6Im5SaHI3WTJEVElTekZpWG1QYm1yelE9PSIsInZhbHVlIjoiaE4zNkVIblBFTmZzZ1gyaXZqMmYrUT09IiwibWFjIjoiY2I2M2U4ZDZiM2U2MDhlMWE4NTA3N2FmZWM5MGQzNzY5ZmFkMTFhMzJjOWY0MDBmMThhODllZGZhNWI0OTgwNiIsInRhZyI6IiJ9', '2024-09-08 05:52:46', '2024-09-08 05:52:46');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_alert`
--

CREATE TABLE `tbl_alert` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ID_tower` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_tower`
--

CREATE TABLE `tbl_tower` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `towercode` varchar(255) NOT NULL,
  `OwnerID` bigint(20) UNSIGNED NOT NULL,
  `ipAdd` varchar(255) DEFAULT NULL,
  `macAdd` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `mode` varchar(255) DEFAULT NULL,
  `startdate` timestamp NULL DEFAULT NULL,
  `enddate` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_towerlogs`
--

CREATE TABLE `tbl_towerlogs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ID_tower` bigint(20) UNSIGNED NOT NULL,
  `activity` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_useraccounts`
--

CREATE TABLE `tbl_useraccounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_useraccounts`
--

INSERT INTO `tbl_useraccounts` (`id`, `name`, `username`, `email`, `password`, `status`, `created_at`, `updated_at`) VALUES
(1, 'eyJpdiI6ImNVVGdEeHR2d0VKcE5UY04zT1dZanc9PSIsInZhbHVlIjoiYTN3WUF1eERCUHFya0J5UHJtRmhvT2JwSGdtdHBXN1dWcHhvYUZnZTBwVT0iLCJtYWMiOiJkMjQ4ZGE5YTY5NjI3MDc2Mjg3M2MyMTdiMTUzM2VhNjYzYmZkMzVhYTVmMGI0Y2ExN2YwZWIxZGJjNmI1ZTQxIiwidGFnIjoiIn0=', 'eyJpdiI6Im9uWU5pTjVUOFRvcGtkRnk4c2JYSHc9PSIsInZhbHVlIjoiUnNNSVNIZHFKMzlRMkI5SklhK2w4dz09IiwibWFjIjoiZTNlMWYyMmJkYjJlOGI4MjNiOTc1NTA1MWJiZWU3MmYwZDA2NzhjYzUwNTlhOWU5YWZhYTNhMDQyZGFmNDU3YyIsInRhZyI6IiJ9', 'eyJpdiI6InlOM285NEQ3VDJJbzFZWEZ2aTF6UFE9PSIsInZhbHVlIjoiUjZzVjI1SWh4eC9zQVZ6a0ZuYUhGSHNpNFJDVW9VVS9ZUitxMmdySzF5bz0iLCJtYWMiOiJjNjQ2YTVlNTA0ODBkNDc3ODA4OTliYzg4NDA3MTVkYmNkOTc3ZDYwZTVhYTAxNDk2OTgwYmQ3MGRiY2Q5YTQzIiwidGFnIjoiIn0=', '$2y$12$DYZ.3ul1qPmWjf9uMYrFDOEpmmLQXN2JIDjY87w1fEGGhS8up..vS', 'eyJpdiI6IjBrTitjaldNQVRSTnhoUTVETkIrc3c9PSIsInZhbHVlIjoiUFByMnNNTnRzcUxRTW9ZRS9LUWFzUT09IiwibWFjIjoiZTc3NmRlNDA4ZTFjYzVmMWU2YTYzN2NkMmVhZmI1ZmQ5YmQ1NDEzZGZlNWRjNjUzMzJkZmFlODgxY2NlZWM1ZCIsInRhZyI6IiJ9', '2024-09-08 05:52:47', '2024-09-08 05:52:47');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_workeraccounts`
--

CREATE TABLE `tbl_workeraccounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `OwnerID` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pump`
--
ALTER TABLE `pump`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pump_towerid_foreign` (`towerid`);

--
-- Indexes for table `sensor`
--
ALTER TABLE `sensor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sensor_towerid_foreign` (`towerid`);

--
-- Indexes for table `sensor_data_history`
--
ALTER TABLE `sensor_data_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `towerid` (`towerid`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `tbl_adminaccounts`
--
ALTER TABLE `tbl_adminaccounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tbl_adminaccounts_username_unique` (`username`),
  ADD UNIQUE KEY `tbl_adminaccounts_email_unique` (`email`);

--
-- Indexes for table `tbl_alert`
--
ALTER TABLE `tbl_alert`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tbl_alert_id_tower_foreign` (`ID_tower`);

--
-- Indexes for table `tbl_tower`
--
ALTER TABLE `tbl_tower`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_towerlogs`
--
ALTER TABLE `tbl_towerlogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tbl_towerlogs_id_tower_foreign` (`ID_tower`);

--
-- Indexes for table `tbl_useraccounts`
--
ALTER TABLE `tbl_useraccounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tbl_useraccounts_username_unique` (`username`),
  ADD UNIQUE KEY `tbl_useraccounts_email_unique` (`email`);

--
-- Indexes for table `tbl_workeraccounts`
--
ALTER TABLE `tbl_workeraccounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tbl_workeraccounts_username_unique` (`username`),
  ADD KEY `tbl_workeraccounts_ownerid_foreign` (`OwnerID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pump`
--
ALTER TABLE `pump`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sensor`
--
ALTER TABLE `sensor`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sensor_data_history`
--
ALTER TABLE `sensor_data_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_adminaccounts`
--
ALTER TABLE `tbl_adminaccounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_alert`
--
ALTER TABLE `tbl_alert`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_tower`
--
ALTER TABLE `tbl_tower`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_towerlogs`
--
ALTER TABLE `tbl_towerlogs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_useraccounts`
--
ALTER TABLE `tbl_useraccounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_workeraccounts`
--
ALTER TABLE `tbl_workeraccounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pump`
--
ALTER TABLE `pump`
  ADD CONSTRAINT `pump_towerid_foreign` FOREIGN KEY (`towerid`) REFERENCES `tbl_tower` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sensor`
--
ALTER TABLE `sensor`
  ADD CONSTRAINT `sensor_towerid_foreign` FOREIGN KEY (`towerid`) REFERENCES `tbl_tower` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sensor_data_history`
--
ALTER TABLE `sensor_data_history`
  ADD CONSTRAINT `sensor_data_history_ibfk_1` FOREIGN KEY (`towerid`) REFERENCES `tbl_tower` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_alert`
--
ALTER TABLE `tbl_alert`
  ADD CONSTRAINT `tbl_alert_id_tower_foreign` FOREIGN KEY (`ID_tower`) REFERENCES `tbl_tower` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_towerlogs`
--
ALTER TABLE `tbl_towerlogs`
  ADD CONSTRAINT `tbl_towerlogs_id_tower_foreign` FOREIGN KEY (`ID_tower`) REFERENCES `tbl_tower` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_workeraccounts`
--
ALTER TABLE `tbl_workeraccounts`
  ADD CONSTRAINT `tbl_workeraccounts_ownerid_foreign` FOREIGN KEY (`OwnerID`) REFERENCES `tbl_useraccounts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
