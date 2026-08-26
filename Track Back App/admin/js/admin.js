// Admin JavaScript functionality

// Initialize admin panel
document.addEventListener('DOMContentLoaded', function() {
    initializeAdmin();
    loadItems();
    loadUsers();
});

// Initialize admin functionality
function initializeAdmin() {
    // Navigation handling
    const navLinks = document.querySelectorAll('.admin-nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.dataset.section;
            switchSection(section);
        });
    });

    // Search functionality
    const itemSearch = document.getElementById('itemSearch');
    const userSearch = document.getElementById('userSearch');
    
    if (itemSearch) {
        itemSearch.addEventListener('input', debounce(filterItems, 300));
    }
    
    if (userSearch) {
        userSearch.addEventListener('input', debounce(filterUsers, 300));
    }

    // Filter functionality
    const statusFilter = document.getElementById('statusFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterItems);
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterItems);
    }
}

// Switch between admin sections
function switchSection(sectionName) {
    // Hide all sections
    const sections = document.querySelectorAll('.admin-section');
    sections.forEach(section => section.classList.remove('active'));
    
    // Show selected section
    const targetSection = document.getElementById(sectionName);
    if (targetSection) {
        targetSection.classList.add('active');
    }
    
    // Update navigation
    const navLinks = document.querySelectorAll('.admin-nav-link');
    navLinks.forEach(link => link.classList.remove('active'));
    
    const activeLink = document.querySelector(`[data-section="${sectionName}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }
    
    // Load section-specific data
    switch(sectionName) {
        case 'items':
            loadItems();
            break;
        case 'users':
            loadUsers();
            break;
        case 'reports':
            loadReports();
            break;
    }
}

// Load items data
async function loadItems() {
    const tableBody = document.getElementById('itemsTableBody');
    if (!tableBody) return;
    
    try {
        tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Loading...</td></tr>';
        
        const response = await fetch('api/get_items.php');
        const data = await response.json();
        
        if (data.success) {
            displayItems(data.items);
        } else {
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-red-500">Error loading items</td></tr>';
        }
    } catch (error) {
        console.error('Error loading items:', error);
        tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-red-500">Error loading items</td></tr>';
    }
}

// Display items in table
function displayItems(items) {
    const tableBody = document.getElementById('itemsTableBody');
    if (!tableBody) return;
    
    if (items.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="8" class="text-center">No items found</td></tr>';
        return;
    }
    
    tableBody.innerHTML = items.map(item => `
        <tr>
            <td>${item.id}</td>
            <td>
                <div class="item-info">
                    <strong>${escapeHtml(item.title)}</strong>
                    <small>${escapeHtml(item.description.substring(0, 50))}...</small>
                </div>
            </td>
            <td>
                <span class="status-badge ${item.status}">
                    ${item.status.charAt(0).toUpperCase() + item.status.slice(1)}
                </span>
            </td>
            <td>${escapeHtml(item.category)}</td>
            <td>${escapeHtml(item.location)}</td>
            <td>${escapeHtml(item.reporter_name || 'Unknown')}</td>
            <td>${formatDate(item.created_at)}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-outline" onclick="viewItem(${item.id})">View</button>
                    <button class="btn btn-sm btn-primary" onclick="editItem(${item.id})">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="confirmDeleteItem(${item.id})">Delete</button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Load users data
async function loadUsers() {
    const tableBody = document.getElementById('usersTableBody');
    if (!tableBody) return;
    
    try {
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Loading...</td></tr>';
        
        const response = await fetch('api/get_users.php');
        const data = await response.json();
        
        if (data.success) {
            displayUsers(data.users);
        } else {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-red-500">Error loading users</td></tr>';
        }
    } catch (error) {
        console.error('Error loading users:', error);
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-red-500">Error loading users</td></tr>';
    }
}

// Display users in table
function displayUsers(users) {
    const tableBody = document.getElementById('usersTableBody');
    if (!tableBody) return;
    
    if (users.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No users found</td></tr>';
        return;
    }
    
    tableBody.innerHTML = users.map(user => `
        <tr>
            <td>${user.id}</td>
            <td>${escapeHtml(user.first_name + ' ' + user.last_name)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${user.items_count || 0}</td>
            <td>${formatDate(user.created_at)}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-outline" onclick="viewUser(${user.id})">View</button>
                    <button class="btn btn-sm btn-primary" onclick="editUser(${user.id})">Edit</button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Filter items
function filterItems() {
    const searchTerm = document.getElementById('itemSearch')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const categoryFilter = document.getElementById('categoryFilter')?.value || '';
    
    const rows = document.querySelectorAll('#itemsTableBody tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length < 8) return; // Skip loading/error rows
        
        const title = cells[1].textContent.toLowerCase();
        const status = cells[2].textContent.toLowerCase().trim();
        const category = cells[3].textContent.toLowerCase();
        
        const matchesSearch = title.includes(searchTerm);
        const matchesStatus = !statusFilter || status.includes(statusFilter.toLowerCase());
        const matchesCategory = !categoryFilter || category.includes(categoryFilter.toLowerCase());
        
        if (matchesSearch && matchesStatus && matchesCategory) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Filter users
function filterUsers() {
    const searchTerm = document.getElementById('userSearch')?.value.toLowerCase() || '';
    const rows = document.querySelectorAll('#usersTableBody tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length < 6) return; // Skip loading/error rows
        
        const name = cells[1].textContent.toLowerCase();
        const email = cells[2].textContent.toLowerCase();
        
        const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
        
        if (matchesSearch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// View item details
function viewItem(itemId) {
    window.open(`../item_detail.php?id=${itemId}`, '_blank');
}

// Edit item
async function editItem(itemId) {
    try {
        const response = await fetch(`api/get_item.php?id=${itemId}`);
        const data = await response.json();
        
        if (data.success) {
            const item = data.item;
            
            // Populate edit form
            document.getElementById('editItemId').value = item.id;
            document.getElementById('editItemTitle').value = item.title;
            document.getElementById('editItemStatus').value = item.status;
            document.getElementById('editItemCategory').value = item.category;
            document.getElementById('editItemDescription').value = item.description;
            document.getElementById('editItemLocation').value = item.location;
            
            // Show modal
            document.getElementById('editItemModal').style.display = 'block';
        } else {
            showAlert('Error loading item details', 'error');
        }
    } catch (error) {
        console.error('Error loading item:', error);
        showAlert('Error loading item details', 'error');
    }
}

// Save item changes
async function saveItem() {
    const formData = new FormData();
    formData.append('id', document.getElementById('editItemId').value);
    formData.append('title', document.getElementById('editItemTitle').value);
    formData.append('status', document.getElementById('editItemStatus').value);
    formData.append('category', document.getElementById('editItemCategory').value);
    formData.append('description', document.getElementById('editItemDescription').value);
    formData.append('location', document.getElementById('editItemLocation').value);
    
    try {
        const response = await fetch('api/update_item.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Item updated successfully', 'success');
            closeModal('editItemModal');
            loadItems();
        } else {
            showAlert(data.message || 'Error updating item', 'error');
        }
    } catch (error) {
        console.error('Error updating item:', error);
        showAlert('Error updating item', 'error');
    }
}

// Delete item
async function deleteItem() {
    const itemId = document.getElementById('editItemId').value;
    
    if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch('api/delete_item.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: itemId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Item deleted successfully', 'success');
            closeModal('editItemModal');
            loadItems();
        } else {
            showAlert(data.message || 'Error deleting item', 'error');
        }
    } catch (error) {
        console.error('Error deleting item:', error);
        showAlert('Error deleting item', 'error');
    }
}

// Confirm delete item
function confirmDeleteItem(itemId) {
    if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        deleteItemById(itemId);
    }
}

// Delete item by ID
async function deleteItemById(itemId) {
    try {
        const response = await fetch('api/delete_item.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: itemId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Item deleted successfully', 'success');
            loadItems();
        } else {
            showAlert(data.message || 'Error deleting item', 'error');
        }
    } catch (error) {
        console.error('Error deleting item:', error);
        showAlert('Error deleting item', 'error');
    }
}

// View user details
function viewUser(userId) {
    // Implement user detail view
    console.log('View user:', userId);
}

// Edit user
function editUser(userId) {
    // Implement user editing
    console.log('Edit user:', userId);
}

// Load reports data
function loadReports() {
    // Implement reports loading
    console.log('Loading reports...');
}

// Modal functions
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

// Utility functions
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

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

function showAlert(message, type = 'info') {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    
    // Add to page
    const main = document.querySelector('.admin-main');
    main.insertBefore(alert, main.firstChild);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Export functions for global access
window.switchSection = switchSection;
window.loadItems = loadItems;
window.loadUsers = loadUsers;
window.viewItem = viewItem;
window.editItem = editItem;
window.saveItem = saveItem;
window.deleteItem = deleteItem;
window.confirmDeleteItem = confirmDeleteItem;
window.viewUser = viewUser;
window.editUser = editUser;
window.closeModal = closeModal;