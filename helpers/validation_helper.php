<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) { http_response_code(403); exit('403 Forbidden'); }

function clean_input($data)
{
    return htmlspecialchars(
        trim($data)
    );
}

function validate_email($email)
{
    return filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    );
}

function validate_password($password)
{
    // Enforce stronger password minimum length
    return is_string($password) && strlen($password) >= 8;
}

function sanitize_array(array $input): array
{
    $out = [];
    foreach ($input as $k => $v) {
        if (is_string($v)) {
            $out[$k] = clean_input($v);
        } else {
            $out[$k] = $v;
        }
    }
    return $out;
}

function sanitize_post(): array
{
    return sanitize_array($_POST);
}

function validate_required($data)
{
    return !empty($data);
}

function validate_number($number)
{
    return is_numeric($number);
}
?>