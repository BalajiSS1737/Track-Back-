<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireAdmin();

// Get statistics
try {
    $pdo = getConnection();
    
    // Get counts
    $lostItems = $pdo->query("SELECT COUNT(*) FROM items WHERE status = 'lost'")->fetchColumn();
    $foundItems = $pdo->query("SELECT COUNT(*) FROM items WHERE status = 'found'")->fetchColumn();
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
    $reunited = $pdo->query("SELECT COUNT(*) FROM items WHERE status = 'reunited'")->fetchColumn();
    
    // Get recent items
    $stmt = $pdo->prepare("SELECT i.*, u.first_name, u.last_name FROM items i 
                          LEFT JOIN users u ON i.user_id = u.id 
                          ORDER BY i.created_at DESC LIMIT 10");
    $stmt->execute();
    $recentItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get users
    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $lostItems = $foundItems = $totalUsers = $reunited = 0;
    $recentItems = $users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Track Back App</title>
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/css/admin.css">
    <style>/* Reset and Base Styles */
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
/* Admin Dashboard Styles */
.admin-header {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.admin-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    max-width: none;
}

.admin-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.admin-brand .logo-icon {
    width: 32px;
    height: 32px;
    color: #3b82f6;
}

.admin-brand h1 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
}

.admin-user {
    display: flex;
    align-items: center;
    gap: 16px;
}

.user-info {
    text-align: right;
}

.user-name {
    display: block;
    font-weight: 600;
    color: #1f2937;
}

.user-role {
    font-size: 14px;
    color: #6b7280;
}

.admin-container {
    display: flex;
    min-height: calc(100vh - 70px);
}

.admin-sidebar {
    width: 280px;
    background: #f8fafc;
    border-right: 1px solid #e5e7eb;
    padding: 24px 0;
    display: flex;
    flex-direction: column;
}

.admin-menu {
    flex: 1;
    padding: 0 16px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 8px;
    text-decoration: none;
    color: #4b5563;
    font-weight: 500;
    margin-bottom: 4px;
    transition: all 0.2s ease;
}

.menu-item:hover {
    background: #e5e7eb;
    color: #1f2937;
}

.menu-item.active {
    background: #3b82f6;
    color: white;
}

.menu-item svg {
    width: 20px;
    height: 20px;
}

.sidebar-footer {
    padding: 16px;
    border-top: 1px solid #e5e7eb;
    margin-top: 24px;
}

.admin-main {
    flex: 1;
    padding: 24px;
    background: #f9fafb;
    overflow-y: auto;
}

.admin-section {
    display: none;
}

.admin-section.active {
    display: block;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
    flex-wrap: wrap;
    gap: 16px;
}

.section-header h2 {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
}

.section-header p {
    color: #6b7280;
    margin-top: 4px;
}

/* Dashboard Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon.lost {
    background: #fee2e2;
    color: #dc2626;
}

.stat-icon.found {
    background: #d1fae5;
    color: #059669;
}

.stat-icon.users {
    background: #dbeafe;
    color: #2563eb;
}

.stat-icon.success {
    background: #fce7f3;
    color: #ec4899;
}

.stat-icon svg {
    width: 24px;
    height: 24px;
}

.stat-content h3 {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.stat-content p {
    color: #6b7280;
    font-size: 14px;
}

/* Dashboard Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
}

.dashboard-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.dashboard-card h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 20px;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f9fafb;
    border-radius: 8px;
}

.activity-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 2px;
}

.activity-time {
    font-size: 12px;
    color: #6b7280;
}

.quick-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Admin Filters */
.admin-filters {
    display: flex;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.admin-filters input,
.admin-filters select {
    padding: 8px 12px;
    border: 2px solid #e5e7eb;
    border-radius: 6px;
    font-size: 14px;
    min-width: 200px;
}

/* Admin Table */
.admin-table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th,
.admin-table td {
    padding: 16px;
    text-align: left;
    border-bottom: 1px solid #f3f4f6;
}

.admin-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
    font-size: 14px;
}

.admin-table td {
    color: #4b5563;
}

.admin-table tr:hover {
    background: #f9fafb;
}

.table-user {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: #6b7280;
    font-size: 14px;
}

.user-details h4 {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 2px;
}

.user-details p {
    font-size: 12px;
    color: #6b7280;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.status-badge.lost {
    background: #fee2e2;
    color: #dc2626;
}

.status-badge.found {
    background: #d1fae5;
    color: #059669;
}

.status-badge.reunited {
    background: #fce7f3;
    color: #ec4899;
}

.status-badge.active {
    background: #d1fae5;
    color: #059669;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #dc2626;
}

.status-badge.admin {
    background: #dbeafe;
    color: #2563eb;
}

.status-badge.user {
    background: #f3f4f6;
    color: #6b7280;
}

.table-actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    padding: 6px 8px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 12px;
}

.action-btn.edit {
    background: #dbeafe;
    color: #2563eb;
}

.action-btn.edit:hover {
    background: #bfdbfe;
}

.action-btn.delete {
    background: #fee2e2;
    color: #dc2626;
}

.action-btn.delete:hover {
    background: #fecaca;
}

.action-btn.view {
    background: #f3f4f6;
    color: #6b7280;
}

.action-btn.view:hover {
    background: #e5e7eb;
}

/* Reports */
.reports-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
}

.report-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.report-card h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 20px;
}

.report-chart {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f9fafb;
    border-radius: 8px;
    color: #6b7280;
}

.category-stats {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.category-stat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #f9fafb;
    border-radius: 8px;
}

.category-name {
    font-weight: 500;
    color: #374151;
}

.category-count {
    font-weight: 600;
    color: #1f2937;
}

/* Settings */
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 24px;
}

.settings-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.settings-card h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 20px;
}

.settings-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 10000;
    animation: fadeIn 0.3s ease;
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    animation: slideUp 0.3s ease;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 24px 0;
    margin-bottom: 24px;
}

.modal-header h3 {
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #9ca3af;
    padding: 4px;
    border-radius: 4px;
    transition: color 0.2s ease;
}

.modal-close:hover {
    color: #6b7280;
}

.modal-form {
    padding: 0 24px 24px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
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

/* Responsive Design */
@media (max-width: 1024px) {
    .admin-container {
        flex-direction: column;
    }
    
    .admin-sidebar {
        width: 100%;
        order: 2;
    }
    
    .admin-main {
        order: 1;
    }
    
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .reports-grid {
        grid-template-columns: 1fr;
    }
    
    .settings-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .admin-nav {
        padding: 12px 16px;
    }
    
    .admin-main {
        padding: 16px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .admin-filters {
        flex-direction: column;
    }
    
    .admin-filters input,
    .admin-filters select {
        min-width: auto;
    }
    
    .admin-table-container {
        overflow-x: auto;
    }
    
    .admin-table {
        min-width: 800px;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }
}</style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-nav">
            <div class="admin-brand">
                <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 12l2 2 4-4"></path>
                    <path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9c2.39 0 4.68.94 6.36 2.64"></path>
                </svg>
                <h1>Track Back Admin</h1>
            </div>
            <div class="admin-user">
                <div class="user-info">
                    <span class="user-name" id="adminUserName"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <span class="user-role">Administrator</span>
                </div>
                <div class="user-menu">
                    <a href="logout.php" class="btn btn-outline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16,17 21,12 16,7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <nav class="admin-menu">
                <a href="#dashboard" class="menu-item active" data-section="dashboard">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Dashboard
                </a>
                <a href="#items" class="menu-item" data-section="items">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                    </svg>
                    Manage Items
                </a>
                <a href="#users" class="menu-item" data-section="users">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Users
                </a>
                <a href="#reports" class="menu-item" data-section="reports">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14,2 14,8 20,8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10,9 9,9 8,9"></polyline>
                    </svg>
                    Reports
                </a>
                <a href="#settings" class="menu-item" data-section="settings">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    Settings
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="index.php" class="btn btn-outline">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9,22 9,12 15,12 15,22"></polyline>
                    </svg>
                    Back to Site
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Dashboard Section -->
            <section id="dashboard-section" class="admin-section active">
                <div class="section-header">
                    <h2>Dashboard Overview</h2>
                    <p>Welcome to the Track Back App admin dashboard</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon lost">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3 id="totalLostItems"><?php echo $lostItems; ?></h3>
                            <p>Lost Items</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon found">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22,4 12,14.01 9,11.01"></polyline>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3 id="totalFoundItems"><?php echo $foundItems; ?></h3>
                            <p>Found Items</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3 id="totalUsers"><?php echo $totalUsers; ?></h3>
                            <p>Registered Users</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3 id="totalReunited"><?php echo $reunited; ?></h3>
                            <p>Items Reunited</p>
                        </div>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <h3>Recent Activity</h3>
                        <div class="activity-list" id="recentActivity">
                            <?php foreach (array_slice($recentItems, 0, 5) as $item): ?>
                            <div class="activity-item">
                                <div class="activity-icon <?php echo $item['status']; ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                    </svg>
                                </div>
                                <div class="activity-content">
                                    <p><strong><?php echo htmlspecialchars($item['title']); ?></strong> was reported as <?php echo $item['status']; ?></p>
                                    <small><?php echo date('M j, Y g:i A', strtotime($item['created_at'])); ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="dashboard-card">
                        <h3>Quick Actions</h3>
                        <div class="quick-actions">
                            <button class="btn btn-primary" onclick="showSection('items')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                Add New Item
                            </button>
                            <button class="btn btn-success" onclick="showSection('users')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="8.5" cy="7" r="4"></circle>
                                    <line x1="20" y1="8" x2="20" y2="14"></line>
                                    <line x1="23" y1="11" x2="17" y2="11"></line>
                                </svg>
                                Manage Users
                            </button>
                            <button class="btn btn-outline" onclick="showSection('reports')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14,2 14,8 20,8"></polyline>
                                </svg>
                                View Reports
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Items Management Section -->
            <section id="items-section" class="admin-section">
                <div class="section-header">
                    <h2>Manage Items</h2>
                    <button class="btn btn-primary" onclick="showAddItemModal()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Add Item
                    </button>
                </div>

                <div class="admin-filters">
                    <input type="text" placeholder="Search items..." id="itemSearch">
                    <select id="itemStatusFilter">
                        <option value="">All Status</option>
                        <option value="lost">Lost</option>
                        <option value="found">Found</option>
                        <option value="reunited">Reunited</option>
                    </select>
                    <select id="itemCategoryFilter">
                        <option value="">All Categories</option>
                        <option value="electronics">Electronics</option>
                        <option value="clothing">Clothing</option>
                        <option value="jewelry">Jewelry</option>
                        <option value="keys">Keys</option>
                        <option value="documents">Documents</option>
                        <option value="bags">Bags</option>
                    </select>
                </div>

                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Location</th>
                                <th>Date</th>
                                <th>Reporter</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <?php foreach ($recentItems as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td><span class="status-badge <?php echo $item['status']; ?>"><?php echo ucfirst($item['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($item['location']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($item['date_lost_found'])); ?></td>
                                <td><?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline" onclick="editItem(<?php echo $item['id']; ?>)">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteItem(<?php echo $item['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Users Management Section -->
            <section id="users-section" class="admin-section">
                <div class="section-header">
                    <h2>User Management</h2>
                    <button class="btn btn-primary" onclick="showAddUserModal()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <line x1="20" y1="8" x2="20" y2="14"></line>
                            <line x1="23" y1="11" x2="17" y2="11"></line>
                        </svg>
                        Add User
                    </button>
                </div>

                <div class="admin-filters">
                    <input type="text" placeholder="Search users..." id="userSearch">
                    <select id="userRoleFilter">
                        <option value="">All Roles</option>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                    <select id="userStatusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></td>
                                <td><span class="status-badge <?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline" onclick="editUser(<?php echo $user['id']; ?>)">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Reports Section -->
            <section id="reports-section" class="admin-section">
                <div class="section-header">
                    <h2>Reports & Analytics</h2>
                    <button class="btn btn-outline" onclick="exportReport()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7,10 12,15 17,10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Export Report
                    </button>
                </div>

                <div class="reports-grid">
                    <div class="report-card">
                        <h3>Monthly Statistics</h3>
                        <div class="report-chart">
                            <canvas id="monthlyChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                    <div class="report-card">
                        <h3>Category Breakdown</h3>
                        <div class="category-stats" id="categoryStats">
                            <!-- Category stats will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </section>

            <!-- Settings Section -->
            <section id="settings-section" class="admin-section">
                <div class="section-header">
                    <h2>System Settings</h2>
                    <p>Configure application settings and preferences</p>
                </div>

                <div class="settings-grid">
                    <div class="settings-card">
                        <h3>General Settings</h3>
                        <form class="settings-form">
                            <div class="form-group">
                                <label for="siteName">Site Name</label>
                                <input type="text" id="siteName" value="Track Back App">
                            </div>
                            <div class="form-group">
                                <label for="siteEmail">Contact Email</label>
                                <input type="email" id="siteEmail" value="support@trackbackapp.com">
                            </div>
                            <div class="form-group">
                                <label for="maxFileSize">Max File Size (MB)</label>
                                <input type="number" id="maxFileSize" value="10">
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                    <div class="settings-card">
                        <h3>Notification Settings</h3>
                        <form class="settings-form">
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" checked>
                                    <span class="checkmark"></span>
                                    Email notifications for new items
                                </label>
                            </div>
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" checked>
                                    <span class="checkmark"></span>
                                    SMS notifications for matches
                                </label>
                            </div>
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox">
                                    <span class="checkmark"></span>
                                    Weekly summary reports
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="admin.js"></script>

    <!-- Edit Item Modal -->
    <div id="editItemModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Item</h3>
                <button class="modal-close" onclick="closeModal('editItemModal')">&times;</button>
            </div>
            <form class="modal-form" id="editItemForm">
                <input type="hidden" id="editItemId" name="id">
                <div class="form-group">
                    <label for="editItemTitle">Item Title *</label>
                    <input type="text" name="title" id="editItemTitle" required>
                </div>
                <div class="form-group">
                    <label for="editItemCategory">Category *</label>
                    <select name="category" id="editItemCategory" required>
                        <option value="">Select a category</option>
                        <option value="electronics">Electronics</option>
                        <option value="clothing">Clothing</option>
                        <option value="jewelry">Jewelry</option>
                        <option value="keys">Keys</option>
                        <option value="documents">Documents</option>
                        <option value="bags">Bags</option>
                        <option value="sports">Sports Equipment</option>
                        <option value="books">Books</option>
                        <option value="toys">Toys</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editItemDescription">Description *</label>
                    <textarea name="description" id="editItemDescription" required rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="editItemLocation">Location *</label>
                    <input type="text" name="location" id="editItemLocation" required>
                </div>
                <div class="form-group">
                    <label for="editItemDate">Date *</label>
                    <input type="date" name="date_lost_found" id="editItemDate" required>
                </div>
                <div class="form-group">
                    <label for="editItemStatus">Status *</label>
                    <select name="status" id="editItemStatus" required>
                        <option value="lost">Lost</option>
                        <option value="found">Found</option>
                        <option value="reunited">Reunited</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editItemModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New User</h3>
                <button class="modal-close" onclick="closeModal('addUserModal')">&times;</button>
            </div>
            <form class="modal-form" id="addUserForm">
                <div class="form-group">
                    <label for="addFirstName">First Name *</label>
                    <input type="text" name="first_name" id="addFirstName" required>
                </div>
                <div class="form-group">
                    <label for="addLastName">Last Name *</label>
                    <input type="text" name="last_name" id="addLastName" required>
                </div>
                <div class="form-group">
                    <label for="addEmail">Email *</label>
                    <input type="email" name="email" id="addEmail" required>
                </div>
                <div class="form-group">
                    <label for="addPassword">Password *</label>
                    <input type="password" name="password" id="addPassword" required>
                </div>
                <div class="form-group">
                    <label for="addPhone">Phone</label>
                    <input type="tel" name="phone" id="addPhone">
                </div>
                <div class="form-group">
                    <label for="addRole">Role *</label>
                    <select name="role" id="addRole" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="addStatus">Status *</label>
                    <select name="status" id="addStatus" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addUserModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit User</h3>
                <button class="modal-close" onclick="closeModal('editUserModal')">&times;</button>
            </div>
            <form class="modal-form" id="editUserForm">
                <input type="hidden" id="editUserId" name="id">
                <div class="form-group">
                    <label for="editUserFirstName">First Name *</label>
                    <input type="text" name="first_name" id="editUserFirstName" required>
                </div>
                <div class="form-group">
                    <label for="editUserLastName">Last Name *</label>
                    <input type="text" name="last_name" id="editUserLastName" required>
                </div>
                <div class="form-group">
                    <label for="editUserEmail">Email *</label>
                    <input type="email" name="email" id="editUserEmail" required>
                </div>
                <div class="form-group">
                    <label for="editUserPhone">Phone</label>
                    <input type="tel" name="phone" id="editUserPhone">
                </div>
                <div class="form-group">
                    <label for="editUserRole">Role *</label>
                    <select name="role" id="editUserRole" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editUserStatus">Status *</label>
                    <select name="status" id="editUserStatus" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editUserModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>