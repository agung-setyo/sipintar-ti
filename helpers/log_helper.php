<?php

function save_log(
    $conn,
    $user_id,
    $action,
    $entity,
    $entity_id,
    $description
) {

    $ip = $_SERVER['REMOTE_ADDR'];

    $agent = $_SERVER['HTTP_USER_AGENT'];

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO audit_logs
        (
            user_id,
            action,
            entity,
            entity_id,
            ip_address,
            user_agent,
            description
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ississs",
        $user_id,
        $action,
        $entity,
        $entity_id,
        $ip,
        $agent,
        $description
    );

    mysqli_stmt_execute($stmt);
}
?>