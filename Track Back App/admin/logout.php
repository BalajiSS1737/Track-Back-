<?php
session_start();

// Destroy admin session
unset($_SESSION['is_admin']);
unset($_SESSION['admin_name']);

// Redirect to main site
header('Location: ../index.php');
exit;
?>