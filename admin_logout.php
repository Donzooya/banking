<?php
session_start();

// Check if the admin is logged in
if (isset($_SESSION['id'])) {
    // Destroy the session
    session_unset();
    session_destroy();
    
    // Redirect to admin login page
    header('Location: admin_login.php');
    exit();
} else {
    // If no session, redirect to login page
    header('Location: admin_login.php');
    exit();
}
?>
