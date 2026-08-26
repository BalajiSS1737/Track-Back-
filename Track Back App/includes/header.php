<?php
require_once 'includes/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Back App</title>
    <link rel="stylesheet" href="./.css">
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
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="nav-brand">
                <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 12l2 2 4-4"></path>
                    <path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9c2.39 0 4.68.94 6.36 2.64"></path>
                </svg>
                <h1>Track Back App</h1>
            </div>
            <nav class="nav-menu" id="navMenu">
                <a href="index.php#browse" class="nav-link">Browse Items</a>
                <a href="index.php#report" class="nav-link">Report Item</a>
                <a href="index.php#success" class="nav-link">Success Stories</a>
                <a href="index.php#contact" class="nav-link">Contact</a>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="admin.php" class="nav-link">Admin</a>
                    <?php endif; ?>
                    <a href="logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                <?php endif; ?>
            </nav>
            <div class="contact-info">
                <div class="contact-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span>San Francisco, CA</span>
                </div>
                <div class="contact-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    <span>(555) 123-4567</span>
                </div>
            </div>
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
