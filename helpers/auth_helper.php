<?php

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function user_id()
{
    return $_SESSION['user_id'];
}

function user_name()
{
    return $_SESSION['name'];
}

function user_role()
{
    return $_SESSION['role'];
}

function require_login()
{
    if (!is_logged_in()) {

        header("Location: ../auth/login.php");
        exit;
    }
}

function require_admin()
{
    if ($_SESSION['role'] != 'admin') {

        header("Location: ../index.php");
        exit;
    }
}

function require_peminjam()
{
    if ($_SESSION['role'] != 'peminjam') {

        header("Location: ../index.php");
        exit;
    }
}
?>