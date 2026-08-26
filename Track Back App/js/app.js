// Track Back App JavaScript

// DOM Elements
const searchInput = document.getElementById('searchInput');
const locationInput = document.getElementById('locationInput');
const categorySelect = document.getElementById('categorySelect');
const filtersBtn = document.getElementById('filtersBtn');
const filtersPanel = document.getElementById('filtersPanel');
const itemsGrid = document.getElementById('itemsGrid');
const reportForm = document.getElementById('reportForm');
const toggleBtns = document.querySelectorAll('.toggle-btn');
const filterBtns = document.querySelectorAll('.filter-btn');
const viewBtns = document.querySelectorAll('.view-btn');
const uploadArea = document.getElementById('uploadArea');
const photoInput = document.getElementById('photoInput');

// Initialize app
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    initializeFilters();
    initializeUpload();
});

// Event Listeners
function initializeEventListeners() {
    // Filter toggle
    if (filtersBtn) {
        filtersBtn.addEventListener('click', toggleFilters);
    }

    // Form type toggle
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            toggleFormType(this.dataset.type);
        });
    });

    // Item filter buttons
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterItems(this.dataset.filter);
        });
    });

    // View toggle buttons
    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            toggleView(this.dataset.view);
        });
    });

    // Upload area
    if (uploadArea && photoInput) {
        uploadArea.addEventListener('click', () => photoInput.click());
        uploadArea.addEventListener('dragover', handleDragOver);
        uploadArea.addEventListener('drop', handleDrop);
        photoInput.addEventListener('change', handleFileSelect);
    }

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', debounce(performSearch, 300));
    }
}

// Toggle filters panel
function toggleFilters() {
    if (filtersPanel) {
        filtersPanel.classList.toggle('active');
    }
}

// Toggle form type (lost/found)
function toggleFormType(type) {
    toggleBtns.forEach(btn => btn.classList.remove('active'));
    document.querySelector(`[data-type="${type}"]`).classList.add('active');
    
    const itemTypeInput = document.getElementById('itemType');
    const submitBtn = document.getElementById('submitBtn');
    
    if (itemTypeInput) {
        itemTypeInput.value = type;
    }
    
    if (submitBtn) {
        submitBtn.textContent = type === 'lost' ? 'Report Lost Item' : 'Report Found Item';
    }
}

// Filter items by status
function filterItems(filter) {
    filterBtns.forEach(btn => btn.classList.remove('active'));
    document.querySelector(`[data-filter="${filter}"]`).classList.add('active');
    
    const itemCards = document.querySelectorAll('.item-card');
    
    itemCards.forEach(card => {
        if (filter === 'all' || card.dataset.status === filter) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Toggle view (grid/list)
function toggleView(view) {
    viewBtns.forEach(btn => btn.classList.remove('active'));
    document.querySelector(`[data-view="${view}"]`).classList.add('active');
    
    if (itemsGrid) {
        if (view === 'list') {
            itemsGrid.classList.add('list-view');
        } else {
            itemsGrid.classList.remove('list-view');
        }
    }
}

// Initialize filters
function initializeFilters() {
    // Set default date to today
    const dateFilter = document.getElementById('itemDate');
    if (dateFilter) {
        dateFilter.value = new Date().toISOString().split('T')[0];
    }
}

// File upload handling
function initializeUpload() {
    if (!uploadArea || !photoInput) return;
    
    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
}

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function handleDragOver(e) {
    uploadArea.classList.add('drag-over');
}

function handleDrop(e) {
    uploadArea.classList.remove('drag-over');
    const files = e.dataTransfer.files;
    handleFiles(files);
}

function handleFileSelect(e) {
    const files = e.target.files;
    handleFiles(files);
}

function handleFiles(files) {
    const maxFiles = 5;
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    if (files.length > maxFiles) {
        alert(`You can only upload up to ${maxFiles} files.`);
        return;
    }
    
    Array.from(files).forEach(file => {
        if (file.size > maxSize) {
            alert(`File ${file.name} is too large. Maximum size is 10MB.`);
            return;
        }
        
        if (!file.type.startsWith('image/')) {
            alert(`File ${file.name} is not an image.`);
            return;
        }
    });
    
    // Update upload area text
    if (files.length > 0) {
        const uploadText = uploadArea.querySelector('p');
        if (uploadText) {
            uploadText.textContent = `${files.length} file(s) selected`;
        }
    }
}

// Search functionality
function performSearch() {
    const searchTerm = searchInput.value.toLowerCase();
    const itemCards = document.querySelectorAll('.item-card');
    
    itemCards.forEach(card => {
        const title = card.querySelector('h3').textContent.toLowerCase();
        const description = card.querySelector('.item-description').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

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

// Make functions globally available
window.showClaimForm = showClaimForm;
window.hideClaimForm = hideClaimForm;

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

// View item details function
function viewDetails(itemId) {
    if (!itemId) {
        console.error('Item ID is required');
        return;
    }
    
    // Redirect to item detail page
    window.location.href = `item_detail.php?id=${itemId}`;
}

// Contact reporter function
function contactReporter(itemId) {
    if (!itemId) {
        console.error('Item ID is required');
        return;
    }
    
    // You can either redirect to a contact form or open email client
    // For now, let's redirect to the item detail page where contact info is available
    window.location.href = `item_detail.php?id=${itemId}`;
}

// Form validation
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Mobile menu toggle (if needed)
function toggleMobileMenu() {
    const navMenu = document.querySelector('.nav-menu');
    if (navMenu) {
        navMenu.classList.toggle('active');
    }
}

// Smooth scroll to section
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}

// Export functions for global access
window.viewDetails = viewDetails;
window.contactReporter = contactReporter;
window.toggleMobileMenu = toggleMobileMenu;
window.scrollToSection = scrollToSection;