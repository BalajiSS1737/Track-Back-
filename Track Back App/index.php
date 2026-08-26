<?php
require_once 'config/database.php';
require_once 'includes/session.php';

// Get recent items from database
try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT i.*, u.first_name, u.last_name FROM items i 
                          LEFT JOIN users u ON i.user_id = u.id 
                          WHERE i.status IN ('lost', 'found') 
                          ORDER BY i.created_at DESC LIMIT 6");
    $stmt->execute();
    $recentItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $recentItems = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'includes/header.php'; ?>
    <link rel="stylesheet" href="public\css\styles.css">
</head>
    <!-- Search Bar -->
    <section class="search-section">
        <div class="container">
            <form method="GET" action="search.php" class="search-bar">
                <div class="search-input-group">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input type="text" name="search" placeholder="Search for items..." id="searchInput">
                </div>
                <div class="search-input-group">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <input type="text" name="location" placeholder="Location..." id="locationInput">
                </div>
                <select name="category" id="categorySelect">
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
                <button class="btn btn-outline" type="button" id="filtersBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22,3 2,3 10,12.46 10,19 14,21 14,12.46"></polygon>
                    </svg>
                    Filters
                </button>
                <button class="btn btn-primary" type="submit">Search</button>
            </form>
            <div class="filters-panel" id="filtersPanel">
                <div class="filter-group">
                    <label>Date Range</label>
                    <input type="date" name="date_from" id="dateFilter">
                </div>
                <div class="filter-group">
                    <label>Status</label>
                    <select name="status" id="statusFilter">
                        <option value="">All Items</option>
                        <option value="lost">Recently Lost</option>
                        <option value="found">Recently Found</option>
                        <option value="claimed">Claimed</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Sort By</label>
                    <select name="sort" id="sortFilter">
                        <option value="recent">Most Recent</option>
                        <option value="oldest">Oldest First</option>
                        <option value="relevant">Most Relevant</option>
                    </select>
                </div>
            </div>
        </div>
    </section>

    <!-- Item Browser -->
    <section id="browse" class="items-section">
        <div class="container">
            <div class="section-header">
                <h2>Recent Items</h2>
                <p>Browse through recently reported lost and found items. Use the filters to find what you're looking for.</p>
            </div>
            
            <div class="items-controls">
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="all">All Items (<?php echo count($recentItems); ?>)</button>
                    <button class="filter-btn" data-filter="lost">Lost</button>
                    <button class="filter-btn" data-filter="found">Found</button>
                </div>
                <div class="view-controls">
                    <span>View:</span>
                    <div class="view-toggle">
                        <button class="view-btn active" data-view="grid">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                        </button>
                        <button class="view-btn" data-view="list">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="8" y1="6" x2="21" y2="6"></line>
                                <line x1="8" y1="12" x2="21" y2="12"></line>
                                <line x1="8" y1="18" x2="21" y2="18"></line>
                                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                <line x1="3" y1="18" x2="3.01" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="items-grid" id="itemsGrid">
                <?php foreach ($recentItems as $item): ?>
                <div class="item-card" data-status="<?php echo htmlspecialchars($item['status']); ?>">
                    <div class="item-image">
                        <?php
                        // Get first photo for this item
                        try {
                            $photoStmt = $pdo->prepare("SELECT photo_path FROM item_photos WHERE item_id = ? LIMIT 1");
                            $photoStmt->execute([$item['id']]);
                            $photo = $photoStmt->fetch(PDO::FETCH_ASSOC);
                            $imagePath = $photo ? $photo['photo_path'] : 'https://images.pexels.com/photos/1029757/pexels-photo-1029757.jpeg?auto=compress&cs=tinysrgb&w=400';
                        } catch(PDOException $e) {
                            $imagePath = 'https://images.pexels.com/photos/1029757/pexels-photo-1029757.jpeg?auto=compress&cs=tinysrgb&w=400';
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                        <div class="item-status <?php echo $item['status']; ?>">
                            <?php echo ucfirst($item['status']); ?>
                        </div>
                    </div>
                    <div class="item-content">
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p class="item-description"><?php echo htmlspecialchars(substr($item['description'], 0, 100)) . '...'; ?></p>
                        <div class="item-meta">
                            <div class="item-location">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <?php echo htmlspecialchars($item['location']); ?>
                            </div>
                            <div class="item-date">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                <?php echo date('M j, Y', strtotime($item['date_lost_found'])); ?>
                            </div>
                        </div>
                        <div class="item-actions">
                            <button class="btn btn-outline" onclick="viewDetails(<?php echo $item['id']; ?>)">View Details</button>
                            <button class="btn btn-primary" onclick="contactReporter(<?php echo $item['id']; ?>)">Contact</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="load-more">
                <a href="browse.php" class="btn btn-primary">Load More Items</a>
            </div>
        </div>
    </section>

    <!-- Report Form -->
    <section id="report" class="report-section">
        <div class="container">
            <div class="section-header">
                <h2>Report an Item</h2>
                <p>Help us reunite lost items with their owners by providing detailed information about what you've lost or found.</p>
            </div>
            
            <div class="report-form-container">
                <div class="form-type-toggle">
                    <button class="toggle-btn active" data-type="lost">Lost Item</button>
                    <button class="toggle-btn" data-type="found">Found Item</button>
                </div>
                
                <form class="report-form" id="reportForm" method="POST" action="report_item.php" enctype="multipart/form-data">
                    <input type="hidden" name="item_type" id="itemType" value="lost">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="itemTitle">Item Title *</label>
                            <input type="text" name="title" id="itemTitle" required placeholder="e.g., iPhone 14 Pro">
                        </div>
                        <div class="form-group">
                            <label for="itemCategory">Category *</label>
                            <select name="category" id="itemCategory" required>
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
                    </div>
                    
                    <div class="form-group">
                        <label for="itemDescription">Description *</label>
                        <textarea name="description" id="itemDescription" required rows="4" placeholder="Provide detailed description including color, size, distinctive features, etc."></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="itemLocation">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                Location *
                            </label>
                            <input type="text" name="location" id="itemLocation" required placeholder="Where was it lost/found?">
                        </div>
                        <div class="form-group">
                            <label for="itemDate">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                Date *
                            </label>
                            <input type="date" name="date_lost_found" id="itemDate" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Upload Photos</label>
                        <div class="upload-area" id="uploadArea">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7,10 12,15 17,10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            <p>Drag and drop photos here, or click to select</p>
                            <small>Up to 5 photos, max 10MB each</small>
                            <input type="file" name="photos[]" multiple accept="image/*" style="display: none;" id="photoInput">
                            <button type="button" class="btn btn-outline" onclick="document.getElementById('photoInput').click()">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                                Choose Photos
                            </button>
                        </div>
                    </div>
                    
                    <div class="contact-section">
                        <h3>Contact Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="contactName">Name *</label>
                                <input type="text" name="contact_name" id="contactName" required>
                            </div>
                            <div class="form-group">
                                <label for="contactEmail">Email *</label>
                                <input type="email" name="contact_email" id="contactEmail" required>
                            </div>
                            <div class="form-group">
                                <label for="contactPhone">Phone</label>
                                <input type="tel" name="contact_phone" id="contactPhone">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-submit">
                        <button type="submit" class="btn btn-primary" id="submitBtn">Report Lost Item</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Success Stories -->
    <section id="success" class="success-section">
        <div class="container">
            <div class="section-header">
                <svg class="heart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                <h2>Success Stories</h2>
                <p>Real stories from our community members who have successfully reunited with their lost items.</p>
            </div>
            
            <div class="stories-grid">
                <div class="story-card">
                    <div class="story-header">
                        <img src="https://images.pexels.com/photos/1239291/pexels-photo-1239291.jpeg?auto=compress&cs=tinysrgb&w=400" alt="Maria Rodriguez">
                        <div class="story-info">
                            <h3>Maria Rodriguez</h3>
                            <p>Brooklyn, NY</p>
                        </div>
                    </div>
                    <div class="story-item">Wedding Ring</div>
                    <div class="story-content">
                        <svg class="quote-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"></path>
                            <path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"></path>
                        </svg>
                        <p>I lost my grandmother's wedding ring at the farmers market. Thanks to Track Back App, someone found it and returned it to me within 24 hours. I'm forever grateful!</p>
                    </div>
                </div>
                
                <div class="story-card">
                    <div class="story-header">
                        <img src="https://images.pexels.com/photos/1043471/pexels-photo-1043471.jpeg?auto=compress&cs=tinysrgb&w=400" alt="David Chen">
                        <div class="story-info">
                            <h3>David Chen</h3>
                            <p>Manhattan, NY</p>
                        </div>
                    </div>
                    <div class="story-item">Laptop with Work Files</div>
                    <div class="story-content">
                        <svg class="quote-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"></path>
                            <path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"></path>
                        </svg>
                        <p>My laptop with years of work was stolen, but someone found it abandoned and posted it here. The honest finder made my week - I had everything backed up but the peace of mind was priceless.</p>
                    </div>
                </div>
                
                <div class="story-card">
                    <div class="story-header">
                        <img src="https://images.pexels.com/photos/1382731/pexels-photo-1382731.jpeg?auto=compress&cs=tinysrgb&w=400" alt="Jennifer Park">
                        <div class="story-info">
                            <h3>Jennifer Park</h3>
                            <p>Queens, NY</p>
                        </div>
                    </div>
                    <div class="story-item">Dog Tag and Leash</div>
                    <div class="story-content">
                        <svg class="quote-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"></path>
                            <path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"></path>
                        </svg>
                        <p>My dog's favorite leash with his ID tag went missing during our morning walk. A kind stranger found it and used this site to contact me. My dog is happy to have his favorite leash back!</p>
                    </div>
                </div>
            </div>
            
            <div class="share-story">
                <div class="share-story-card">
                    <h3>Have Your Own Success Story?</h3>
                    <p>We'd love to hear how Track Back App helped you reunite with your lost item or return something to its rightful owner.</p>
                    <button class="btn btn-success">Share Your Story</button>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; // Assuming you have a footer file ?>
    <script src="js/app.js"></script>