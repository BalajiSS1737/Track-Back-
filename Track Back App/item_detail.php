<?php
require_once 'config/database.php';
require_once 'includes/session.php';

// Handle success/error messages
$message = '';
$messageType = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'claim_submitted':
            $message = 'Your claim has been submitted successfully! The item reporter will be notified and will contact you if they verify your ownership.';
            $messageType = 'success';
            break;
    }
}
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'missing_fields':
            $message = 'Please fill in all required fields.';
            $messageType = 'error';
            break;
        case 'invalid_item':
            $message = 'Invalid item or item is not available for claiming.';
            $messageType = 'error';
            break;
        case 'claim_exists':
            $message = 'A claim has already been submitted for this item and is pending review.';
            $messageType = 'error';
            break;
        case 'database_error':
            $message = 'An error occurred. Please try again later.';
            $messageType = 'error';
            break;
    }
}

$item = null;
if (isset($_GET['id'])) {
    $itemId = $_GET['id'];
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT i.*, u.first_name, u.last_name FROM items i 
                              LEFT JOIN users u ON i.user_id = u.id 
                              WHERE i.id = ?");
        $stmt->execute([$itemId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            $photoStmt = $pdo->prepare("SELECT photo_path FROM item_photos WHERE item_id = ?");
            $photoStmt->execute([$itemId]);
            $item['photos'] = $photoStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch(PDOException $e) {
        // Log error or handle it gracefully
        $item = null;
    }
}

if (!$item) {
    // Redirect to homepage or show an error
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['title']); ?> - Item Details</title>
    <link rel="stylesheet" href="public/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Basic styles for item detail page */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .item-detail-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        .item-detail-header h1 {
            font-size: 2.5em;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .item-detail-header .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
            margin-top: 10px;
        }
        .item-detail-header .status-badge.lost {
            background: #fee2e2;
            color: #dc2626;
        }
        .item-detail-header .status-badge.found {
            background: #d1fae5;
            color: #059669;
        }
        .item-detail-content {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        .item-detail-images {
            flex: 1;
            min-width: 300px;
        }
        .item-detail-images img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .item-detail-info {
            flex: 2;
            min-width: 300px;
        }
        .item-detail-info h2 {
            font-size: 1.8em;
            color: #1f2937;
            margin-bottom: 15px;
        }
        .item-detail-info p {
            margin-bottom: 10px;
            color: #4b5563;
        }
        .item-detail-info strong {
            color: #1f2937;
        }
        .item-detail-meta {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .item-detail-meta div {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            color: #6b7280;
        }
        .item-detail-meta svg {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            color: #3b82f6;
        }
        .contact-button {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        .contact-button:hover {
            background-color: #2563eb;
        }
        @media (max-width: 768px) {
            .item-detail-content {
                flex-direction: column;
            }
        }
        /* Reset and Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f8fafc;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Header */
.header {
    background: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.header .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 70px;
}

.nav-brand {
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo-icon {
    width: 32px;
    height: 32px;
    color: #3b82f6;
}

.nav-brand h1 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
}

.nav-menu {
    display: flex;
    gap: 32px;
}

.nav-link {
    text-decoration: none;
    color: #4b5563;
    font-weight: 500;
    transition: color 0.2s ease;
}

.nav-link:hover {
    color: #3b82f6;
}

.contact-info {
    display: flex;
    gap: 20px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
    color: #6b7280;
}

.contact-item svg {
    width: 16px;
    height: 16px;
}

.mobile-menu-btn {
    display: none;
    flex-direction: column;
    gap: 4px;
    background: none;
    border: none;
    cursor: pointer;
}

.mobile-menu-btn span {
    width: 25px;
    height: 3px;
    background: #374151;
    transition: 0.3s;
}

/* Hero Section */
.hero {
    background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
    color: white;
    padding: 80px 0;
    text-align: center;
}

.hero-title {
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 700;
    margin-bottom: 24px;
    line-height: 1.2;
}

.highlight {
    color: #fbbf24;
}

.hero-subtitle {
    font-size: 20px;
    margin-bottom: 40px;
    color: #dbeafe;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.hero-buttons {
    display: flex;
    gap: 16px;
    justify-content: center;
    margin-bottom: 60px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 16px;
}

.btn svg {
    width: 20px;
    height: 20px;
}

.btn-primary {
    background: white;
    color: #3b82f6;
}

.btn-primary:hover {
    background: #f3f4f6;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover {
    background: #059669;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-outline {
    background: transparent;
    color: #3b82f6;
    border: 2px solid #3b82f6;
}

.btn-outline:hover {
    background: #3b82f6;
    color: white;
}

.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 32px;
    max-width: 800px;
    margin: 0 auto;
}

.stat-item {
    background: rgba(255, 255, 255, 0.1);
    padding: 24px;
    border-radius: 12px;
    backdrop-filter: blur(10px);
}

.stat-number {
    font-size: 36px;
    font-weight: 700;
    color: #fbbf24;
    margin-bottom: 8px;
}

.stat-label {
    color: #dbeafe;
    font-size: 16px;
}

/* Search Section */
.search-section {
    padding: 0 20px;
    margin-top: -40px;
    position: relative;
    z-index: 10;
}

.search-bar {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    align-items: center;
}

.search-input-group {
    position: relative;
    flex: 1;
    min-width: 200px;
}

.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    color: #9ca3af;
}

.search-input-group input {
    width: 100%;
    padding: 12px 12px 12px 44px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.2s ease;
}

.search-input-group input:focus {
    outline: none;
    border-color: #3b82f6;
}

.search-bar select {
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    background: white;
    cursor: pointer;
}

.filters-panel {
    background: #f9fafb;
    border-radius: 8px;
    padding: 20px;
    margin-top: 16px;
    display: none;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.filters-panel.active {
    display: grid;
}

.filter-group label {
    display: block;
    font-weight: 500;
    margin-bottom: 8px;
    color: #374151;
}

.filter-group input,
.filter-group select {
    width: 100%;
    padding: 8px 12px;
    border: 2px solid #e5e7eb;
    border-radius: 6px;
    font-size: 14px;
}

/* Items Section */
.items-section {
    padding: 80px 0;
    background: white;
}

.section-header {
    text-align: center;
    margin-bottom: 48px;
}

.section-header h2 {
    font-size: 36px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 16px;
}

.section-header p {
    font-size: 18px;
    color: #6b7280;
    max-width: 600px;
    margin: 0 auto;
}

.items-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
    flex-wrap: wrap;
    gap: 20px;
}

.filter-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    background: #f3f4f6;
    color: #4b5563;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-btn.active,
.filter-btn:hover {
    background: #3b82f6;
    color: white;
}

.filter-btn[data-filter="lost"].active {
    background: #ef4444;
}

.filter-btn[data-filter="found"].active {
    background: #10b981;
}

.view-controls {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    color: #6b7280;
}

.view-toggle {
    display: flex;
    background: #f3f4f6;
    border-radius: 8px;
    padding: 4px;
}

.view-btn {
    padding: 8px;
    border: none;
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.view-btn.active {
    background: white;
    color: #3b82f6;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.view-btn svg {
    width: 20px;
    height: 20px;
}

.items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
    margin-bottom: 48px;
}

.items-grid.list-view {
    grid-template-columns: 1fr;
}

.item-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    transition: all 0.3s ease;
}

.item-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.item-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-status {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.item-status.lost {
    background: #fee2e2;
    color: #dc2626;
}

.item-status.found {
    background: #d1fae5;
    color: #059669;
}

.item-content {
    padding: 20px;
}

.item-title {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 8px;
}

.item-description {
    color: #6b7280;
    font-size: 14px;
    margin-bottom: 16px;
    line-height: 1.5;
}

.item-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 16px;
}

.item-detail {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #6b7280;
}

.item-detail svg {
    width: 16px;
    height: 16px;
}

.item-actions {
    display: flex;
    gap: 8px;
}

.item-actions .btn {
    flex: 1;
    justify-content: center;
    font-size: 14px;
    padding: 8px 16px;
}

.load-more {
    text-align: center;
}

/* Report Section */
.report-section {
    padding: 80px 0;
    background: #f9fafb;
}

.report-form-container {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    padding: 40px;
}

.form-type-toggle {
    display: flex;
    justify-content: center;
    margin-bottom: 32px;
    background: #f3f4f6;
    border-radius: 8px;
    padding: 4px;
    width: fit-content;
    margin-left: auto;
    margin-right: auto;
}

.toggle-btn {
    padding: 12px 24px;
    border: none;
    background: transparent;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.toggle-btn.active[data-type="lost"] {
    background: #ef4444;
    color: white;
}

.toggle-btn.active[data-type="found"] {
    background: #10b981;
    color: white;
}

.report-form {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 500;
    margin-bottom: 8px;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group label svg {
    width: 16px;
    height: 16px;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
}

.upload-area {
    border: 2px dashed #d1d5db;
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    transition: border-color 0.2s ease;
    cursor: pointer;
}

.upload-area:hover {
    border-color: #3b82f6;
}

.upload-area svg {
    width: 48px;
    height: 48px;
    color: #9ca3af;
    margin-bottom: 16px;
}

.upload-area p {
    color: #6b7280;
    margin-bottom: 8px;
}

.upload-area small {
    color: #9ca3af;
    font-size: 14px;
    display: block;
    margin-bottom: 16px;
}

.contact-section {
    border-top: 2px solid #f3f4f6;
    padding-top: 24px;
}

.contact-section h3 {
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 20px;
}

.form-submit {
    text-align: center;
}

.form-submit .btn {
    padding: 16px 32px;
    font-size: 18px;
}

/* Success Section */
.success-section {
    padding: 80px 0;
    background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
}

.success-section .section-header {
    position: relative;
}

.heart-icon {
    width: 48px;
    height: 48px;
    color: #ec4899;
    margin-bottom: 16px;
}

.stories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 32px;
    margin-bottom: 48px;
}

.story-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease;
}

.story-card:hover {
    transform: translateY(-4px);
}

.story-header {
    display: flex;
    align-items: center;
    margin-bottom: 16px;
}

.story-header img {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 12px;
}

.story-info h3 {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
}

.story-info p {
    font-size: 14px;
    color: #6b7280;
}

.story-item {
    display: inline-block;
    background: #dbeafe;
    color: #1e40af;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 16px;
}

.story-content {
    position: relative;
    padding-left: 24px;
}

.quote-icon {
    position: absolute;
    left: 0;
    top: 0;
    width: 20px;
    height: 20px;
    color: #d1d5db;
}

.story-content p {
    color: #4b5563;
    font-style: italic;
    line-height: 1.6;
}

.share-story {
    text-align: center;
}

.share-story-card {
    background: white;
    border-radius: 12px;
    padding: 40px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    max-width: 600px;
    margin: 0 auto;
}

.share-story-card h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 16px;
}

.share-story-card p {
    color: #6b7280;
    margin-bottom: 24px;
    line-height: 1.6;
}

/* Footer */
.footer {
    background: #1f2937;
    color: white;
    padding: 60px 0 20px;
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
    margin-bottom: 40px;
}

.footer-section h3,
.footer-section h4 {
    margin-bottom: 20px;
    font-weight: 600;
}

.footer-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

.footer-brand .logo-icon {
    color: #60a5fa;
}

.footer-section p {
    color: #9ca3af;
    line-height: 1.6;
    margin-bottom: 20px;
}

.social-links {
    display: flex;
    gap: 16px;
}

.social-links a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: #374151;
    border-radius: 8px;
    color: #9ca3af;
    transition: all 0.2s ease;
}

.social-links a:hover {
    background: #4b5563;
    color: white;
}

.social-links svg {
    width: 20px;
    height: 20px;
}

.footer-section ul {
    list-style: none;
}

.footer-section ul li {
    margin-bottom: 8px;
}

.footer-section ul li a {
    color: #9ca3af;
    text-decoration: none;
    transition: color 0.2s ease;
}

.footer-section ul li a:hover {
    color: white;
}

.contact-details {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.contact-detail {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    color: #9ca3af;
}

.contact-detail svg {
    width: 20px;
    height: 20px;
    margin-top: 2px;
    flex-shrink: 0;
}

.footer-bottom {
    border-top: 1px solid #374151;
    padding-top: 20px;
    text-align: center;
    color: #9ca3af;
    font-size: 14px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header .container {
        height: 60px;
    }
    
    .nav-menu,
    .contact-info {
        display: none;
    }
    
    .mobile-menu-btn {
        display: flex;
    }
    
    .hero {
        padding: 60px 0;
    }
    
    .hero-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .search-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-input-group {
        min-width: auto;
    }
    
    .items-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-buttons {
        justify-content: center;
    }
    
    .view-controls {
        justify-content: center;
    }
    
    .items-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .report-form-container {
        padding: 24px;
    }
    
    .stories-grid {
        grid-template-columns: 1fr;
    }
    
    .footer-content {
        grid-template-columns: 1fr;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 0 16px;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-subtitle {
        font-size: 16px;
    }
    
    .stats {
        grid-template-columns: 1fr;
    }
    
    .section-header h2 {
        font-size: 28px;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.item-card {
    animation: fadeInUp 0.6s ease forwards;
}

.story-card {
    animation: fadeInUp 0.6s ease forwards;
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* User Dropdown Styles */
.user-dropdown {
    position: fixed;
    z-index: 10000;
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    min-width: 200px;
    animation: fadeIn 0.2s ease;
}

.user-dropdown-content {
    padding: 16px;
}

.user-dropdown .user-info {
    margin-bottom: 12px;
}

.user-dropdown .user-info strong {
    display: block;
    color: #1f2937;
    font-weight: 600;
}

.user-dropdown .user-info span {
    font-size: 14px;
    color: #6b7280;
}

.user-dropdown hr {
    border: none;
    border-top: 1px solid #e5e7eb;
    margin: 12px 0;
}

.user-dropdown a {
    display: block;
    padding: 8px 0;
    color: #4b5563;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.2s ease;
}

.user-dropdown a:hover {
    color: #3b82f6;
}

.user-dropdown .logout-link {
    color: #ef4444;
}

.user-dropdown .logout-link:hover {
    color: #dc2626;
}

/* Claim Modal Styles */
.claim-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.claim-modal-content {
    background: white;
    border-radius: 12px;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
}

.claim-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 24px 0;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 24px;
}

.claim-modal-header h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.close-modal {
    background: none;
    border: none;
    font-size: 24px;
    color: #6b7280;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.close-modal:hover {
    background: #f3f4f6;
    color: #374151;
}

.claim-modal-body {
    padding: 0 24px 24px;
}

.claim-modal-body p {
    color: #6b7280;
    margin-bottom: 24px;
    line-height: 1.6;
}

.claim-form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid #e5e7eb;
}

.item-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.item-actions .contact-button {
    flex: 1;
    text-align: center;
    min-width: 150px;
}

.claim-button {
    background-color: #10b981 !important;
}

.claim-button:hover {
    background-color: #059669 !important;
}

/* Form styles for claim modal */
.claim-modal .form-group {
    margin-bottom: 20px;
}

.claim-modal .form-group label {
    display: block;
    font-weight: 500;
    margin-bottom: 8px;
    color: #374151;
}

.claim-modal .form-group input,
.claim-modal .form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.2s ease;
}

.claim-modal .form-group input:focus,
.claim-modal .form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
}

.claim-modal .form-group label input[type="checkbox"] {
    width: auto;
    margin-right: 8px;
}

@media (max-width: 768px) {
    .claim-modal {
        padding: 10px;
    }
    
    .claim-modal-content {
        max-height: 95vh;
    }
    
    .claim-form-actions {
        flex-direction: column;
    }
    
    .item-actions {
        flex-direction: column;
    }
    
    .item-actions .contact-button {
        min-width: auto;
    }
}

/* Message Banner Styles */
.message-banner {
    padding: 16px 0;
    margin-bottom: 20px;
}

.message-banner.success {
    background-color: #d1fae5;
    border-left: 4px solid #10b981;
}

.message-banner.error {
    background-color: #fee2e2;
    border-left: 4px solid #ef4444;
}

.message-banner p {
    margin: 0;
    font-weight: 500;
}

.message-banner.success p {
    color: #065f46;
}

.message-banner.error p {
    color: #991b1b;
}
    </style>
</head>
<body>
    <?php include 'includes/header.php'; // Assuming you have a header file ?>

    <?php if ($message): ?>
    <div class="message-banner <?php echo $messageType; ?>">
        <div class="container">
            <p><?php echo htmlspecialchars($message); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="container">
        <div class="item-detail-header">
            <h1><?php echo htmlspecialchars($item['title']); ?></h1>
            <span class="status-badge <?php echo $item['status']; ?>"><?php echo ucfirst($item['status']); ?></span>
        </div>

        <div class="item-detail-content">
            <div class="item-detail-images">
                <?php if (!empty($item['photos'])): ?>
                    <?php foreach ($item['photos'] as $photo): ?>
                        <img src="<?php echo htmlspecialchars($photo['photo_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                    <?php endforeach; ?>
                <?php else: ?>
                    <img src="https://images.pexels.com/photos/1029757/pexels-photo-1029757.jpeg?auto=compress&cs=tinysrgb&w=400" alt="No image available">
                <?php endif; ?>
            </div>
            <div class="item-detail-info">
                <h2>Description</h2>
                <p><?php echo htmlspecialchars($item['description']); ?></p>

                <div class="item-detail-meta">
                    <div>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                        </svg>
                        <strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?>
                    </div>
                    <div>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?>
                    </div>
                    <div>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <strong>Date:</strong> <?php echo date('M j, Y', strtotime($item['date_lost_found'])); ?>
                    </div>
                    <div>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <strong>Reported by:</strong> <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?>
                    </div>
                </div>
                <div class="item-actions">
                    <?php if ($item['status'] === 'found'): ?>
                        <button class="contact-button claim-button" onclick="showClaimForm()">Claim This Item</button>
                    <?php endif; ?>
                    <a href="mailto:<?php echo htmlspecialchars($item['contact_email']); ?>" class="contact-button">Contact Reporter</a>
                </div>
            </div>
        </div>
        
        <!-- Claim Verification Modal -->
        <?php if ($item['status'] === 'found'): ?>
        <div id="claimModal" class="claim-modal" style="display: none;">
            <div class="claim-modal-content">
                <div class="claim-modal-header">
                    <h3>Claim Verification</h3>
                    <button class="close-modal" onclick="hideClaimForm()">&times;</button>
                </div>
                <div class="claim-modal-body">
                    <p>To claim this item, please answer the following questions to verify ownership:</p>
                    <form id="claimForm" method="POST" action="process_claim.php">
                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                        
                        <div class="form-group">
                            <label for="claimantName">Your Full Name *</label>
                            <input type="text" name="claimant_name" id="claimantName" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="claimantEmail">Your Email *</label>
                            <input type="email" name="claimant_email" id="claimantEmail" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="claimantPhone">Your Phone Number</label>
                            <input type="tel" name="claimant_phone" id="claimantPhone">
                        </div>
                        
                        <div class="form-group">
                            <label for="ownershipProof">Describe specific details about this item that only the owner would know *</label>
                            <textarea name="ownership_proof" id="ownershipProof" required rows="4" placeholder="Include details like: serial numbers, unique marks, contents, where you lost it, when you lost it, etc."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="additionalInfo">Additional Information (Optional)</label>
                            <textarea name="additional_info" id="additionalInfo" rows="3" placeholder="Any other details that might help verify your ownership"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="agree_terms" required>
                                I confirm that I am the rightful owner of this item and understand that false claims may result in legal consequences.
                            </label>
                        </div>
                        
                        <div class="claim-form-actions">
                            <button type="button" class="btn btn-outline" onclick="hideClaimForm()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit Claim</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; // Assuming you have a footer file ?>
    <script>
        // Claim functionality
        function showClaimForm() {
            const modal = document.getElementById('claimModal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function hideClaimForm() {
            const modal = document.getElementById('claimModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('claimModal');
            if (modal && e.target === modal) {
                hideClaimForm();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('claimModal');
                if (modal && modal.style.display === 'flex') {
                    hideClaimForm();
                }
            }
        });
    </script>
</body>
</html>