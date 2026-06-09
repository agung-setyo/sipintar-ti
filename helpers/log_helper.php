<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) { http_response_code(403); exit('403 Forbidden'); }
function client_ip()
{
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function client_user_agent()
{
    return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
}

function save_log($conn, $user_id, $action, $entity, $entity_id, $description)
{
    $ip = client_ip();
    $agent = client_user_agent();

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO audit_logs
        (user_id, action, entity, entity_id, ip_address, user_agent, description)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        error_log('Audit log prepare failed: ' . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        'ississs',
        $user_id,
        $action,
        $entity,
        $entity_id,
        $ip,
        $agent,
        $description
    );

    return mysqli_stmt_execute($stmt);
}

function save_security_event($conn, $event_type, $severity, $user_id, $description)
{
    $allowed = ['low', 'medium', 'high', 'critical'];
    if (!in_array($severity, $allowed, true)) {
        $severity = 'medium';
    }

    $ip = client_ip();
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO security_events (event_type, severity, user_id, ip_address, description)
         VALUES (?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        error_log('Security event prepare failed: ' . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'ssiss', $event_type, $severity, $user_id, $ip, $description);
    return mysqli_stmt_execute($stmt);
}
?>
