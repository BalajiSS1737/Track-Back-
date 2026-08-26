<?php
require_once 'config/database.php';
require_once 'includes/session.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        try {
            $pdo = getConnection();
            
            // Check in users table first
            $stmt = $pdo->prepare("SELECT id, email, password, first_name, last_name, 'user' as role FROM users WHERE email = ? AND status = 'active'
                                  UNION
                                  SELECT id, email, password, username as first_name, '' as last_name, 'admin' as role FROM admins WHERE email = ? AND status = 'active'");
            $stmt->execute([$email, $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Update active_users table
                $activeStmt = $pdo->prepare("INSERT INTO active_users (user_id, user_type, login_time) VALUES (?, ?, NOW()) 
                                           ON DUPLICATE KEY UPDATE login_time = NOW(), logout_time = NULL");
                $activeStmt->execute([$user['id'], $user['role']]);
                
                if ($user['role'] === 'admin') {
                    header('Location: admin.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $error = 'Invalid email or password';
            }
        } catch(PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    } else {
        $error = 'Please fill in all fields';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Track Back App</title>
    <link rel="stylesheet" href="public/styles.css">
    <link rel="stylesheet" href="public/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
     <style>
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
/* Authentication Styles */
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
}

.auth-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    padding: 40px;
    width: 100%;
    max-width: 480px;
    animation: slideUp 0.6s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.auth-header {
    text-align: center;
    margin-bottom: 32px;
}

.auth-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 24px;
}

.auth-logo .logo-icon {
    width: 40px;
    height: 40px;
    color: #3b82f6;
}

.auth-logo h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
}

.auth-header h2 {
    font-size: 24px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 8px;
}

.auth-header p {
    color: #6b7280;
    font-size: 16px;
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 500;
    margin-bottom: 8px;
    color: #374151;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.2s ease;
    background: white;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.password-input {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #9ca3af;
    padding: 4px;
    border-radius: 4px;
    transition: color 0.2s ease;
}

.password-toggle:hover {
    color: #6b7280;
}

.password-toggle svg {
    width: 20px;
    height: 20px;
}

.password-strength {
    margin-top: 8px;
    height: 4px;
    background: #f3f4f6;
    border-radius: 2px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.password-strength.weak {
    background: linear-gradient(to right, #ef4444 30%, #f3f4f6 30%);
}

.password-strength.medium {
    background: linear-gradient(to right, #f59e0b 60%, #f3f4f6 60%);
}

.password-strength.strong {
    background: linear-gradient(to right, #10b981 100%, #f3f4f6 100%);
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 14px;
    color: #4b5563;
}

.checkbox-label input[type="checkbox"] {
    display: none;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid #d1d5db;
    border-radius: 4px;
    position: relative;
    transition: all 0.2s ease;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark {
    background: #3b82f6;
    border-color: #3b82f6;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark::after {
    content: '';
    position: absolute;
    left: 5px;
    top: 2px;
    width: 4px;
    height: 8px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.forgot-password {
    color: #3b82f6;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: color 0.2s ease;
}

.forgot-password:hover {
    color: #2563eb;
}

.auth-submit {
    position: relative;
    overflow: hidden;
    margin-top: 8px;
}

.btn-loader {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.auth-divider {
    text-align: center;
    margin: 24px 0;
    position: relative;
}

.auth-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e5e7eb;
}

.auth-divider span {
    background: white;
    padding: 0 16px;
    color: #9ca3af;
    font-size: 14px;
}

.demo-accounts {
    text-align: center;
    margin-bottom: 24px;
}

.demo-accounts h3 {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 16px;
}

.demo-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.demo-btn {
    flex: 1;
    min-width: 140px;
    font-size: 14px;
    padding: 10px 16px;
}

.auth-footer {
    text-align: center;
    color: #6b7280;
    font-size: 14px;
}

.auth-footer p {
    margin-bottom: 8px;
}

.auth-footer a {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
}

.auth-footer a:hover {
    color: #2563eb;
}

/* Responsive Design */
@media (max-width: 640px) {
    .auth-container {
        padding: 16px;
    }
    
    .auth-card {
        padding: 24px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-options {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .demo-buttons {
        flex-direction: column;
    }
    
    .demo-btn {
        min-width: auto;
    }
}
     </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 12l2 2 4-4"></path>
                        <path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9c2.39 0 4.68.94 6.36 2.64"></path>
                    </svg>
                    <h1>Track Back App</h1>
                </div>
                <h2>Welcome Back</h2>
                <p>Sign in to your account to continue</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" id="loginForm" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required placeholder="Enter your password">
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" id="rememberMe" name="remember_me">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary auth-submit">
                    <span class="btn-text">Sign In</span>
                    <div class="btn-loader" style="display: none;">
                        <div class="spinner"></div>
                    </div>
                </button>
            </form>

            <div class="auth-divider">
                <span>or</span>
            </div>

            <div class="demo-accounts">
                <h3>Demo Accounts</h3>
                <div class="demo-buttons">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="email" value="admin@trackback.com">
                        <input type="hidden" name="password" value="admin123">
                        <button type="submit" class="btn btn-outline demo-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                            </svg>
                            Login as Admin
                        </button>
                    </form>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="email" value="user@trackback.com">
                        <input type="hidden" name="password" value="user123">
                        <button type="submit" class="btn btn-outline demo-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            Login as User
                        </button>
                    </form>
                </div>
            </div>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Sign up here</a></p>
                <p><a href="index.php">← Back to Home</a></p>
            </div>
        </div>
    </div>

    <script src="public/auth.js"></script>
</body>
</html>