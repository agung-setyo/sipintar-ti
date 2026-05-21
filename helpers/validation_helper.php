<?php

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
    return strlen($password) >= 6;
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