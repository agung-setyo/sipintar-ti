-- SIPINTAR-TI clean database import for Hostinger/phpMyAdmin
-- Pilih database Hostinger terlebih dahulu, lalu import file ini.
-- Tidak menggunakan CREATE DATABASE agar tidak memicu error #1044.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `security_events`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `borrow_details`;
DROP TABLE IF EXISTS `borrow_requests`;
DROP TABLE IF EXISTS `items`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','peminjam') NOT NULL DEFAULT 'peminjam',
  `identity_type` enum('dosen','mahasiswa','petugas') NOT NULL,
  `identity_number` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `categories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `items` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) NOT NULL,
  `item_code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `item_condition` enum('baik','rusak_ringan','rusak') NOT NULL DEFAULT 'baik',
  `location` varchar(100) DEFAULT NULL,
  `status` enum('available','unavailable') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_code` (`item_code`),
  KEY `idx_items_category` (`category_id`),
  KEY `idx_items_status` (`status`),
  CONSTRAINT `fk_items_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `borrow_requests` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `request_code` varchar(50) NOT NULL,
  `borrow_date` date NOT NULL,
  `return_date` date NOT NULL,
  `purpose` text NOT NULL,
  `status` enum('pending','approved','rejected','borrowed','returned','cancelled') NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_code` (`request_code`),
  KEY `fk_borrow_user` (`user_id`),
  KEY `fk_borrow_approved_by` (`approved_by`),
  KEY `idx_borrow_status` (`status`),
  CONSTRAINT `fk_borrow_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_borrow_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `borrow_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `borrow_request_id` bigint(20) NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_detail_borrow` (`borrow_request_id`),
  KEY `fk_detail_item` (`item_id`),
  CONSTRAINT `fk_detail_borrow` FOREIGN KEY (`borrow_request_id`) REFERENCES `borrow_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detail_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity` varchar(100) NOT NULL,
  `entity_id` bigint(20) DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_action` (`action`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `security_events` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(100) NOT NULL,
  `severity` enum('low','medium','high','critical') NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_security_user` (`user_id`),
  KEY `idx_security_type` (`event_type`),
  CONSTRAINT `fk_security_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `sessions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `session_token_hash` varchar(255) NOT NULL,
  `expired_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_session_user` (`user_id`),
  CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `identity_type`, `identity_number`, `is_active`) VALUES
(1, 'Administrator', 'admin@sipintar.com', '$2b$12$m71EuQSuzcxwfMMR3mppjO9Kg6kThbhBzjS87H5uaG/SUfStAB7ye', 'admin', 'petugas', 'ADM001', 1),
(2, 'Peminjam Demo', 'peminjam@sipintar.com', '$2b$12$Mh7AEw.j4oKbC0ypRj3rNOoDWUYiXoEoKKp2NUVU95PEYFUOizK8a', 'peminjam', 'mahasiswa', 'MHS001', 1);

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Presentasi', 'Peralatan presentasi'),
(2, 'Praktikum Jaringan', 'Peralatan jaringan komputer'),
(3, 'Praktikum IoT', 'Peralatan IoT dan embedded'),
(4, 'Dokumentasi', 'Peralatan dokumentasi'),
(5, 'Umum', 'Peralatan umum');

INSERT INTO `items` (`id`, `category_id`, `item_code`, `name`, `description`, `stock`, `item_condition`, `location`, `status`) VALUES
(1, 1, 'PRJ-001', 'Proyektor Epson', 'Proyektor ruang seminar', 5, 'baik', 'Lab Multimedia', 'available'),
(2, 1, 'HDMI-001', 'Kabel HDMI', 'Kabel HDMI 5 meter', 10, 'baik', 'Gudang Inventaris', 'available'),
(3, 2, 'RTR-001', 'Router Mikrotik', 'Router praktikum jaringan', 7, 'baik', 'Lab Jaringan', 'available'),
(4, 3, 'ARD-001', 'Arduino Uno', 'Board Arduino Uno R3', 15, 'baik', 'Lab IoT', 'available'),
(5, 4, 'CAM-001', 'Kamera Canon', 'Kamera dokumentasi kegiatan', 2, 'baik', 'Ruang Multimedia', 'available');

INSERT INTO `audit_logs` (`user_id`, `action`, `entity`, `entity_id`, `ip_address`, `user_agent`, `description`) VALUES
(1, 'INIT_DB', 'system', NULL, '127.0.0.1', 'database-import', 'Database bersih berhasil diinisialisasi');
