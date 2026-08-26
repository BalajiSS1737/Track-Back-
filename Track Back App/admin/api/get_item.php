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

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Item ID required']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Get item details
    $stmt = $pdo->prepare("
        SELECT i.*, 
               u.first_name, 
               u.last_name,
               u.email as reporter_email
        FROM items i 
        LEFT JOIN users u ON i.user_id = u.id 
        WHERE i.id = ?
    ");
    
    $stmt->execute([$_GET['id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        exit;
    }
    
    // Get item photos
    $photoStmt = $pdo->prepare("SELECT * FROM item_photos WHERE item_id = ?");
    $photoStmt->execute([$_GET['id']]);
    $item['photos'] = $photoStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'item' => $item
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>