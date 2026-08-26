<?php
require_once 'config/database.php';
require_once 'includes/session.php';

if (isLoggedIn()) {
    try {
        $pdo = getConnection();
        // Update active_users table
        $stmt = $pdo->prepare("UPDATE active_users SET logout_time = NOW() WHERE user_id = ? AND logout_time IS NULL");
        $stmt->execute([$_SESSION['user_id']]);
    } catch(PDOException $e) {
        // Continue with logout even if database update fails
    }
}

session_destroy();
header('Location: index.php');
exit();
?>