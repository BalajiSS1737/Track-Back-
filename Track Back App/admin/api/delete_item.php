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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || empty($input['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Item ID is required']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Delete item photos first (foreign key constraint)
    $photoStmt = $pdo->prepare("DELETE FROM item_photos WHERE item_id = ?");
    $photoStmt->execute([$input['id']]);
    
    // Delete the item
    $itemStmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
    $result = $itemStmt->execute([$input['id']]);
    
    if ($result && $itemStmt->rowCount() > 0) {
        $pdo->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Item deleted successfully'
        ]);
    } else {
        $pdo->rollback();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Item not found or already deleted'
        ]);
    }
    
} catch(PDOException $e) {
    $pdo->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>