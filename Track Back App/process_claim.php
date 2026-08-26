<?php
require_once 'config/database.php';
require_once 'includes/session.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $itemId = $_POST['item_id'] ?? '';
    $claimantName = $_POST['claimant_name'] ?? '';
    $claimantEmail = $_POST['claimant_email'] ?? '';
    $claimantPhone = $_POST['claimant_phone'] ?? '';
    $ownershipProof = $_POST['ownership_proof'] ?? '';
    $additionalInfo = $_POST['additional_info'] ?? '';
    $agreeTerms = isset($_POST['agree_terms']);
    
    // Validate required fields
    if (empty($itemId) || empty($claimantName) || empty($claimantEmail) || empty($ownershipProof) || !$agreeTerms) {
        header('Location: item_detail.php?id=' . $itemId . '&error=missing_fields');
        exit();
    }
    
    try {
        $pdo = getConnection();
        
        // Check if item exists and is found
        $itemStmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND status = 'found'");
        $itemStmt->execute([$itemId]);
        $item = $itemStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$item) {
            header('Location: item_detail.php?id=' . $itemId . '&error=invalid_item');
            exit();
        }
        
        // Check if there's already a pending claim for this item
        $existingClaimStmt = $pdo->prepare("SELECT id FROM item_claims WHERE item_id = ? AND status = 'pending'");
        $existingClaimStmt->execute([$itemId]);
        $existingClaim = $existingClaimStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingClaim) {
            header('Location: item_detail.php?id=' . $itemId . '&error=claim_exists');
            exit();
        }
        
        // Insert the claim
        $claimStmt = $pdo->prepare("INSERT INTO item_claims (item_id, claimant_name, claimant_email, claimant_phone, ownership_proof, additional_info, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $claimStmt->execute([$itemId, $claimantName, $claimantEmail, $claimantPhone, $ownershipProof, $additionalInfo]);
        
        // Send notification email to the item reporter
        $subject = "Someone has claimed your found item: " . $item['title'];
        $message = "Hello,\n\n";
        $message .= "Someone has submitted a claim for the item you reported as found: " . $item['title'] . "\n\n";
        $message .= "Claimant Details:\n";
        $message .= "Name: " . $claimantName . "\n";
        $message .= "Email: " . $claimantEmail . "\n";
        $message .= "Phone: " . $claimantPhone . "\n\n";
        $message .= "Ownership Verification:\n" . $ownershipProof . "\n\n";
        if (!empty($additionalInfo)) {
            $message .= "Additional Information:\n" . $additionalInfo . "\n\n";
        }
        $message .= "Please review this claim and contact the claimant if you believe they are the rightful owner.\n\n";
        $message .= "You can view the full details by visiting: " . $_SERVER['HTTP_HOST'] . "/item_detail.php?id=" . $itemId . "\n\n";
        $message .= "Best regards,\nTrack Back App Team";
        
        $headers = "From: noreply@trackbackapp.com\r\n";
        $headers .= "Reply-To: " . $claimantEmail . "\r\n";
        
        mail($item['contact_email'], $subject, $message, $headers);
        
        header('Location: item_detail.php?id=' . $itemId . '&success=claim_submitted');
        exit();
        
    } catch(PDOException $e) {
        error_log("Database error in process_claim.php: " . $e->getMessage());
        header('Location: item_detail.php?id=' . $itemId . '&error=database_error');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>