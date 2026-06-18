<?php
require '../includes/auth_check.php';
require_role('business');
include '../includes/header.php';
echo "<h2>Welcome, " . htmlspecialchars($_SESSION['name']) . " (Business Dashboard — built on Day 3)</h2>";
include '../includes/footer.php';
?>