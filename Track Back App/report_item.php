<?php
require_once 'config/database.php';
require_once 'includes/session.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $location = $_POST['location'] ?? '';
    $dateLostFound = $_POST['date_lost_found'] ?? '';
    $itemType = $_POST['item_type'] ?? 'lost';
    $contactName = $_POST['contact_name'] ?? '';
    $contactEmail = $_POST['contact_email'] ?? '';
    $contactPhone = $_POST['contact_phone'] ?? '';
    
    if (empty($title) || empty($category) || empty($description) || empty($location) || empty($dateLostFound) || empty($contactName) || empty($contactEmail)) {
        header('Location: index.php?error=missing_fields');
        exit();
    }
    
    try {
        $pdo = getConnection();
        
        // Insert item
        $stmt = $pdo->prepare("INSERT INTO items (user_id, title, description, category, location, date_lost_found, status, contact_name, contact_email, contact_phone, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
        $stmt->execute([$userId, $title, $description, $category, $location, $dateLostFound, $itemType, $contactName, $contactEmail, $contactPhone]);
        
        $itemId = $pdo->lastInsertId();
        
        // Handle file uploads
        if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
            $uploadDir = 'uploads/items/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            foreach ($_FILES['photos']['name'] as $key => $filename) {
                if ($_FILES['photos']['error'][$key] == 0) {
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                    $newFilename = $itemId . '_' . time() . '_' . $key . '.' . $extension;
                    $uploadPath = $uploadDir . $newFilename;
                    
                    if (move_uploaded_file($_FILES['photos']['tmp_name'][$key], $uploadPath)) {
                        $photoStmt = $pdo->prepare("INSERT INTO item_photos (item_id, photo_path, created_at) VALUES (?, ?, NOW())");
                        $photoStmt->execute([$itemId, $uploadPath]);
                    }
                }
            }
        }
        
        header('Location: index.php?success=item_reported');
        exit();
        
    } catch(PDOException $e) {
        header('Location: index.php?error=database_error');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>