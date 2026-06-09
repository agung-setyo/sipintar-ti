<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) { http_response_code(403); exit('403 Forbidden'); }

const LOGIN_MAX_ATTEMPTS = 3;
const LOGIN_LOCK_SECONDS = 900; // 15 menit

if (!function_exists('login_rate_limit_ip')) {
    function login_rate_limit_ip(): string
    {
        if (function_exists('client_ip')) {
            return client_ip();
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

if (!function_exists('login_rate_limit_email')) {
    function login_rate_limit_email(string $email): string
    {
        return strtolower(trim($email));
    }
}

if (!function_exists('login_rate_limit_identifier')) {
    function login_rate_limit_identifier(string $email, string $role): string
    {
        $ip = login_rate_limit_ip();
        return hash('sha256', login_rate_limit_email($email) . '|' . $role . '|' . $ip);
    }
}

if (!function_exists('ensure_login_attempts_table')) {
    function ensure_login_attempts_table(mysqli $conn): bool
    {
        static $checked = false;
        if ($checked) {
            return true;
        }

        $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            identifier_hash char(64) NOT NULL,
            email varchar(191) NOT NULL,
            role enum('admin','peminjam') NOT NULL,
            ip_address varchar(45) NOT NULL,
            failed_count tinyint unsigned NOT NULL DEFAULT 0,
            locked_until int unsigned DEFAULT NULL,
            last_failed_at int unsigned NOT NULL,
            created_at timestamp NOT NULL DEFAULT current_timestamp(),
            updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (id),
            UNIQUE KEY uq_login_attempt_identifier (identifier_hash),
            KEY idx_login_attempt_email_role (email, role),
            KEY idx_login_attempt_locked_until (locked_until)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        if (!mysqli_query($conn, $sql)) {
            error_log('Create login_attempts table failed: ' . mysqli_error($conn));
            return false;
        }

        $checked = true;
        return true;
    }
}

if (!function_exists('get_login_attempt_record')) {
    function get_login_attempt_record(mysqli $conn, string $email, string $role): ?array
    {
        if (!ensure_login_attempts_table($conn)) {
            return null;
        }

        $identifier = login_rate_limit_identifier($email, $role);
        $stmt = mysqli_prepare($conn, "SELECT failed_count, locked_until FROM login_attempts WHERE identifier_hash = ? LIMIT 1");
        if (!$stmt) {
            error_log('Login attempts select prepare failed: ' . mysqli_error($conn));
            return null;
        }

        mysqli_stmt_bind_param($stmt, 's', $identifier);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $record = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        return $record ?: null;
    }
}

if (!function_exists('reset_login_attempts')) {
    function reset_login_attempts(mysqli $conn, string $email, string $role): bool
    {
        if (!ensure_login_attempts_table($conn)) {
            return false;
        }

        $identifier = login_rate_limit_identifier($email, $role);
        $stmt = mysqli_prepare($conn, "DELETE FROM login_attempts WHERE identifier_hash = ?");
        if (!$stmt) {
            error_log('Login attempts delete prepare failed: ' . mysqli_error($conn));
            return false;
        }

        mysqli_stmt_bind_param($stmt, 's', $identifier);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }
}

if (!function_exists('login_rate_limit_status')) {
    function login_rate_limit_status(mysqli $conn, string $email, string $role): array
    {
        $record = get_login_attempt_record($conn, $email, $role);
        if (!$record) {
            return ['locked' => false, 'remaining_attempts' => LOGIN_MAX_ATTEMPTS, 'retry_after' => 0];
        }

        $now = time();
        $lockedUntil = isset($record['locked_until']) ? (int)$record['locked_until'] : 0;
        $failedCount = (int)($record['failed_count'] ?? 0);

        if ($lockedUntil > $now) {
            return [
                'locked' => true,
                'remaining_attempts' => 0,
                'retry_after' => $lockedUntil - $now,
            ];
        }

        if ($lockedUntil > 0 && $lockedUntil <= $now) {
            reset_login_attempts($conn, $email, $role);
            return ['locked' => false, 'remaining_attempts' => LOGIN_MAX_ATTEMPTS, 'retry_after' => 0];
        }

        return [
            'locked' => false,
            'remaining_attempts' => max(0, LOGIN_MAX_ATTEMPTS - $failedCount),
            'retry_after' => 0,
        ];
    }
}

if (!function_exists('register_login_failure')) {
    function register_login_failure(mysqli $conn, string $email, string $role): array
    {
        if (!ensure_login_attempts_table($conn)) {
            return ['locked' => false, 'remaining_attempts' => 0, 'retry_after' => 0];
        }

        $now = time();
        $email = login_rate_limit_email($email);
        $ip = substr(login_rate_limit_ip(), 0, 45);
        $identifier = login_rate_limit_identifier($email, $role);
        $record = get_login_attempt_record($conn, $email, $role);

        if ($record && !empty($record['locked_until']) && (int)$record['locked_until'] > $now) {
            return login_rate_limit_status($conn, $email, $role);
        }

        $previousCount = $record ? (int)$record['failed_count'] : 0;
        if ($record && !empty($record['locked_until']) && (int)$record['locked_until'] <= $now) {
            $previousCount = 0;
        }

        $failedCount = min(LOGIN_MAX_ATTEMPTS, $previousCount + 1);
        $lockedUntil = $failedCount >= LOGIN_MAX_ATTEMPTS ? $now + LOGIN_LOCK_SECONDS : null;

        $sql = "INSERT INTO login_attempts
                (identifier_hash, email, role, ip_address, failed_count, locked_until, last_failed_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    email = VALUES(email),
                    role = VALUES(role),
                    ip_address = VALUES(ip_address),
                    failed_count = VALUES(failed_count),
                    locked_until = VALUES(locked_until),
                    last_failed_at = VALUES(last_failed_at)";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            error_log('Login attempts upsert prepare failed: ' . mysqli_error($conn));
            return ['locked' => false, 'remaining_attempts' => 0, 'retry_after' => 0];
        }

        mysqli_stmt_bind_param($stmt, 'ssssiii', $identifier, $email, $role, $ip, $failedCount, $lockedUntil, $now);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return [
            'locked' => $lockedUntil !== null,
            'remaining_attempts' => max(0, LOGIN_MAX_ATTEMPTS - $failedCount),
            'retry_after' => $lockedUntil !== null ? LOGIN_LOCK_SECONDS : 0,
        ];
    }
}

if (!function_exists('format_retry_after')) {
    function format_retry_after(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $minutes = (int)ceil($seconds / 60);
        return $minutes . ' menit';
    }
}
?>
