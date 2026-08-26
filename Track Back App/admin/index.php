<?php
require_once '../config/database.php';
require_once '../includes/session.php';

// Check if user is admin (you can implement proper admin authentication)
// For now, we'll use a simple check - in production, implement proper admin authentication
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // For demo purposes, set admin session - remove this in production
    $_SESSION['is_admin'] = true;
    $_SESSION['admin_name'] = 'Admin User';
}

// Get statistics
try {
    $pdo = getConnection();
    
    // Total items
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM items");
    $totalItems = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Lost items
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM items WHERE status = 'lost'");
    $lostItems = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Found items
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM items WHERE status = 'found'");
    $foundItems = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Claimed items
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM items WHERE status = 'claimed'");
    $claimedItems = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Recent items
    $stmt = $pdo->prepare("SELECT i.*, u.first_name, u.last_name FROM items i 
                          LEFT JOIN users u ON i.user_id = u.id 
                          ORDER BY i.created_at DESC LIMIT 10");
    $stmt->execute();
    $recentItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $totalItems = $lostItems = $foundItems = $claimedItems = $totalUsers = 0;
    $recentItems = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Track Back App</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-container">
            <div class="admin-brand">
                <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h1>Track Back Admin</h1>
            </div>
            <div class="admin-user">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn btn-outline btn-sm">Logout</a>
            </div>
        </div>
    </header>

    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <nav class="admin-nav">
                <a href="#dashboard" class="admin-nav-link active" data-section="dashboard">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Dashboard
                </a>
                <a href="#items" class="admin-nav-link" data-section="items">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                    </svg>
                    Manage Items
                </a>
                <a href="#users" class="admin-nav-link" data-section="users">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Manage Users
                </a>
                <a href="#reports" class="admin-nav-link" data-section="reports">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 17H7A5 5 0 0 1 7 7h2m0 10a5 5 0 0 0 5-5V7a5 5 0 0 0-5-5m0 10v3m0-3h8a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-8m0-2v2"></path>
                    </svg>
                    Reports
                </a>
                <a href="#settings" class="admin-nav-link" data-section="settings">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    Settings
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Dashboard Section -->
            <section id="dashboard" class="admin-section active">
                <div class="admin-section-header">
                    <h2>Dashboard Overview</h2>
                    <p>Monitor your Track Back App performance and statistics</p>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon lost">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $lostItems; ?></h3>
                            <p>Lost Items</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon found">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $foundItems; ?></h3>
                            <p>Found Items</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon claimed">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $claimedItems; ?></h3>
                            <p>Claimed Items</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87m-4-12a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $totalUsers; ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Items -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3>Recent Items</h3>
                        <a href="#items" class="btn btn-outline btn-sm" onclick="switchSection('items')">View All</a>
                    </div>
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Status</th>
                                    <th>Reporter</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentItems as $item): ?>
                                <tr>
                                    <td>
                                        <div class="item-info">
                                            <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                                            <small><?php echo htmlspecialchars($item['category']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $item['status']; ?>">
                                            <?php echo ucfirst($item['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($item['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-outline" onclick="viewItem(<?php echo $item['id']; ?>)">View</button>
                                            <button class="btn btn-sm btn-primary" onclick="editItem(<?php echo $item['id']; ?>)">Edit</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Items Management Section -->
            <section id="items" class="admin-section">
                <div class="admin-section-header">
                    <h2>Manage Items</h2>
                    <p>View, edit, and manage all lost and found items</p>
                </div>

                <div class="admin-controls">
                    <div class="search-controls">
                        <input type="text" placeholder="Search items..." class="admin-search" id="itemSearch">
                        <select class="admin-filter" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="lost">Lost</option>
                            <option value="found">Found</option>
                            <option value="claimed">Claimed</option>
                        </select>
                        <select class="admin-filter" id="categoryFilter">
                            <option value="">All Categories</option>
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
                    <button class="btn btn-primary" onclick="loadItems()">Refresh</button>
                </div>

                <div class="admin-card">
                    <div class="admin-table-container">
                        <table class="admin-table" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Item</th>
                                    <th>Status</th>
                                    <th>Category</th>
                                    <th>Location</th>
                                    <th>Reporter</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                <!-- Items will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Users Management Section -->
            <section id="users" class="admin-section">
                <div class="admin-section-header">
                    <h2>Manage Users</h2>
                    <p>View and manage user accounts</p>
                </div>

                <div class="admin-controls">
                    <div class="search-controls">
                        <input type="text" placeholder="Search users..." class="admin-search" id="userSearch">
                    </div>
                    <button class="btn btn-primary" onclick="loadUsers()">Refresh</button>
                </div>

                <div class="admin-card">
                    <div class="admin-table-container">
                        <table class="admin-table" id="usersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Items Reported</th>
                                    <th>Join Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <!-- Users will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Reports Section -->
            <section id="reports" class="admin-section">
                <div class="admin-section-header">
                    <h2>Reports & Analytics</h2>
                    <p>View detailed reports and analytics</p>
                </div>

                <div class="reports-grid">
                    <div class="admin-card">
                        <h3>Items by Category</h3>
                        <div class="chart-placeholder">
                            <p>Chart will be implemented here</p>
                        </div>
                    </div>
                    <div class="admin-card">
                        <h3>Monthly Activity</h3>
                        <div class="chart-placeholder">
                            <p>Chart will be implemented here</p>
                        </div>
                    </div>
                    <div class="admin-card">
                        <h3>Success Rate</h3>
                        <div class="chart-placeholder">
                            <p>Success rate: <?php echo $totalItems > 0 ? round(($claimedItems / $totalItems) * 100, 1) : 0; ?>%</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Settings Section -->
            <section id="settings" class="admin-section">
                <div class="admin-section-header">
                    <h2>System Settings</h2>
                    <p>Configure application settings</p>
                </div>

                <div class="settings-grid">
                    <div class="admin-card">
                        <h3>General Settings</h3>
                        <form class="settings-form">
                            <div class="form-group">
                                <label>Site Name</label>
                                <input type="text" value="Track Back App" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Contact Email</label>
                                <input type="email" value="admin@trackback.com" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Items per Page</label>
                                <input type="number" value="12" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </form>
                    </div>

                    <div class="admin-card">
                        <h3>Email Settings</h3>
                        <form class="settings-form">
                            <div class="form-group">
                                <label>SMTP Host</label>
                                <input type="text" placeholder="smtp.gmail.com" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>SMTP Port</label>
                                <input type="number" value="587" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>SMTP Username</label>
                                <input type="text" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>SMTP Password</label>
                                <input type="password" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary">Save Email Settings</button>
                        </form>
                    </div>

                    <div class="admin-card">
                        <h3>Security Settings</h3>
                        <form class="settings-form">
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" checked> Enable user registration
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" checked> Require email verification
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox"> Enable admin approval for items
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Security Settings</button>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Edit Item Modal -->
    <div id="editItemModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Item</h3>
                <button class="modal-close" onclick="closeModal('editItemModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editItemForm">
                    <input type="hidden" id="editItemId">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" id="editItemTitle" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="editItemStatus" class="form-control">
                            <option value="lost">Lost</option>
                            <option value="found">Found</option>
                            <option value="claimed">Claimed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select id="editItemCategory" class="form-control">
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
                        <label>Description</label>
                        <textarea id="editItemDescription" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" id="editItemLocation" class="form-control">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('editItemModal')">Cancel</button>
                <button class="btn btn-primary" onclick="saveItem()">Save Changes</button>
                <button class="btn btn-danger" onclick="deleteItem()">Delete Item</button>
            </div>
        </div>
    </div>

    <script src="../js/app.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>