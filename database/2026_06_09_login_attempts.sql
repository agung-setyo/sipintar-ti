-- Migration tambahan untuk database yang sudah berjalan.
-- Jalankan file ini sekali melalui phpMyAdmin jika tabel login_attempts belum otomatis dibuat.

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `identifier_hash` char(64) NOT NULL,
  `email` varchar(191) NOT NULL,
  `role` enum('admin','peminjam') NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `failed_count` tinyint unsigned NOT NULL DEFAULT 0,
  `locked_until` int unsigned DEFAULT NULL,
  `last_failed_at` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_login_attempt_identifier` (`identifier_hash`),
  KEY `idx_login_attempt_email_role` (`email`, `role`),
  KEY `idx_login_attempt_locked_until` (`locked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
