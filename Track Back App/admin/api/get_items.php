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
    
    // Get all items with user information
    $stmt = $pdo->prepare("
        SELECT i.*, 
               u.first_name, 
               u.last_name,
               CONCAT(u.first_name, ' ', u.last_name) as reporter_name,
               COUNT(ip.id) as photo_count
        FROM items i 
        LEFT JOIN users u ON i.user_id = u.id 
        LEFT JOIN item_photos ip ON i.id = ip.item_id
        GROUP BY i.id
        ORDER BY i.created_at DESC
    ");
    
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>