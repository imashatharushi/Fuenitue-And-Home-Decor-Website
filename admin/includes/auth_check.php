<?php
require_once dirname(__DIR__, 2) . '/includes/auth_handler.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = "You must be logged in as an administrator to access this page.";
    header('Location: ../login.php');
    exit();
}
