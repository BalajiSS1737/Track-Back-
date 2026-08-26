<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../includes/session.php';

// Check admin authentication
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Get all users with item count
    $stmt = $pdo->prepare("
        SELECT u.*, 
               COUNT(i.id) as items_count
        FROM users u 
        LEFT JOIN items i ON u.id = i.user_id
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>