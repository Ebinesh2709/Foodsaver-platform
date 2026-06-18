<?php
session_start();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /Foodsaver/auth/login.php");
        exit;
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['role'] !== $role) {
        die("Access denied.");
    }
}
?>