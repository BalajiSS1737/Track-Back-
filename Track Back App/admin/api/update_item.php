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

// Validate required fields
$required_fields = ['id', 'title', 'status', 'category', 'description', 'location'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
        exit;
    }
}

try {
    $pdo = getConnection();
    
    // Update item
    $stmt = $pdo->prepare("
        UPDATE items 
        SET title = ?, 
            status = ?, 
            category = ?, 
            description = ?, 
            location = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $result = $stmt->execute([
        trim($_POST['title']),
        trim($_POST['status']),
        trim($_POST['category']),
        trim($_POST['description']),
        trim($_POST['location']),
        $_POST['id']
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Item updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update item'
        ]);
    }
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>