// Admin Dashboard System
class AdminDashboard {
    constructor() {
        this.authSystem = window.authSystem;
        this.currentSection = 'dashboard';
        this.items = this.loadItems();
        this.init();
    }

    init() {
        // Check if user is admin
        if (!this.authSystem.currentUser || this.authSystem.currentUser.role !== 'admin') {
            window.location.href = 'login.html';
            return;
        }

        this.setupEventListeners();
        this.updateUserInfo();
        this.updateDashboardStats();
        this.renderRecentActivity();
        this.showSection('dashboard');
    }

    loadItems() {
        // Get items from main app or use default data
        const defaultItems = [
            {
                id: '1',
                title: 'iPhone 14 Pro',
                description: 'Black iPhone 14 Pro with a blue case. Lost near Central Park on Tuesday evening.',
                category: 'Electronics',
                location: 'Central Park, NYC',
                date: '2024-01-15',
                image: 'https://images.pexels.com/photos/788946/pexels-photo-788946.jpeg?auto=compress&cs=tinysrgb&w=400',
                status: 'lost',
                reportedBy: 'Sarah M.',
                contactInfo: 'sarah.m@email.com',
                userId: '2'
            },
            {
                id: '2',
                title: 'Brown Leather Wallet',
                description: 'Brown leather wallet with driver\'s license and credit cards. Found at Union Square.',
                category: 'Documents',
                location: 'Union Square, NYC',
                date: '2024-01-14',
                image: 'https://images.pexels.com/photos/1068523/pexels-photo-1068523.jpeg?auto=compress&cs=tinysrgb&w=400',
                status: 'found',
                reportedBy: 'John D.',
                contactInfo: 'john.d@email.com',
                userId: '3'
            },
            {
                id: '3',
                title: 'Gold Wedding Ring',
                description: 'Gold wedding band with engraving "Forever & Always". Lost at Brooklyn Bridge.',
                category: 'Jewelry',
                location: 'Brooklyn Bridge, NYC',
                date: '2024-01-13',
                image: 'https://images.pexels.com/photos/1030861/pexels-photo-1030861.jpeg?auto=compress&cs=tinysrgb&w=400',
                status: 'lost',
                reportedBy: 'Emily R.',
                contactInfo: 'emily.r@email.com',
                userId: '4'
            },
            {
                id: '4',
                title: 'Blue Backpack',
                description: 'Navy blue backpack with laptop compartment. Found at subway station.',
                category: 'Bags',
                location: '42nd Street Station, NYC',
                date: '2024-01-12',
                image: 'https://images.pexels.com/photos/2905238/pexels-photo-2905238.jpeg?auto=compress&cs=tinysrgb&w=400',
                status: 'found',
                reportedBy: 'Mike T.',
                contactInfo: 'mike.t@email.com',
                userId: '5'
            },
            {
                id: '5',
                title: 'Car Keys with Toyota Keychain',
                description: 'Toyota car keys with house keys and a small flashlight keychain.',
                category: 'Keys',
                location: 'Times Square, NYC',
                date: '2024-01-11',
                image: 'https://images.pexels.com/photos/279949/pexels-photo-279949.jpeg?auto=compress&cs=tinysrgb&w=400',
                status: 'lost',
                reportedBy: 'Lisa K.',
                contactInfo: 'lisa.k@email.com',
                userId: '2'
            },
            {
                id: '6',
                title: 'Red Scarf',
                description: 'Bright red woolen scarf with fringe. Found at coffee shop.',
                category: 'Clothing',
                location: 'Starbucks, Manhattan',
                date: '2024-01-10',
                image: 'https://images.pexels.com/photos/985635/pexels-photo-985635.jpeg?auto=compress&cs=tinysrgb&w=400',
                status: 'found',
                reportedBy: 'Anna P.',
                contactInfo: 'anna.p@email.com',
                userId: '3'
            }
        ];

        const stored = localStorage.getItem('trackback_items');
        return stored ? JSON.parse(stored) : defaultItems;
    }

    saveItems() {
        localStorage.setItem('trackback_items', JSON.stringify(this.items));
    }

    setupEventListeners() {
        // Menu navigation
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const section = item.dataset.section;
                this.showSection(section);
            });
        });

        // Search and filter listeners
        this.setupSearchAndFilters();

        // Modal listeners
        this.setupModalListeners();
    }

    setupSearchAndFilters() {
        // Items search and filters
        const itemSearch = document.getElementById('itemSearch');
        const itemStatusFilter = document.getElementById('itemStatusFilter');
        const itemCategoryFilter = document.getElementById('itemCategoryFilter');

        if (itemSearch) {
            itemSearch.addEventListener('input', () => this.renderItemsTable());
        }
        if (itemStatusFilter) {
            itemStatusFilter.addEventListener('change', () => this.renderItemsTable());
        }
        if (itemCategoryFilter) {
            itemCategoryFilter.addEventListener('change', () => this.renderItemsTable());
        }

        // Users search and filters
        const userSearch = document.getElementById('userSearch');
        const userRoleFilter = document.getElementById('userRoleFilter');
        const userStatusFilter = document.getElementById('userStatusFilter');

        if (userSearch) {
            userSearch.addEventListener('input', () => this.renderUsersTable());
        }
        if (userRoleFilter) {
            userRoleFilter.addEventListener('change', () => this.renderUsersTable());
        }
        if (userStatusFilter) {
            userStatusFilter.addEventListener('change', () => this.renderUsersTable());
        }
    }

    setupModalListeners() {
        // Add item form
        const addItemForm = document.getElementById('addItemForm');
        if (addItemForm) {
            addItemForm.addEventListener('submit', (e) => this.handleAddItem(e));
        }

        // Edit item form
        const editItemForm = document.getElementById('editItemForm');
        if (editItemForm) {
            editItemForm.addEventListener('submit', (e) => this.handleEditItem(e));
        }

        // Add user form
        const addUserForm = document.getElementById('addUserForm');
        if (addUserForm) {
            addUserForm.addEventListener('submit', (e) => this.handleAddUser(e));
        }

        // Edit user form
        const editUserForm = document.getElementById('editUserForm');
        if (editUserForm) {
            editUserForm.addEventListener('submit', (e) => this.handleEditUser(e));
        }
    }

    updateUserInfo() {
        const userNameElement = document.getElementById('adminUserName');
        if (userNameElement && this.authSystem.currentUser) {
            userNameElement.textContent = `${this.authSystem.currentUser.firstName} ${this.authSystem.currentUser.lastName}`;
        }
    }

    updateDashboardStats() {
        const lostItems = this.items.filter(item => item.status === 'lost').length;
        const foundItems = this.items.filter(item => item.status === 'found').length;
        const totalUsers = this.authSystem.getUsers().length;
        const reunited = 12; // Mock data

        document.getElementById('totalLostItems').textContent = lostItems;
        document.getElementById('totalFoundItems').textContent = foundItems;
        document.getElementById('totalUsers').textContent = totalUsers;
        document.getElementById('totalReunited').textContent = reunited;
    }

    renderRecentActivity() {
        const activityContainer = document.getElementById('recentActivity');
        if (!activityContainer) return;

        const activities = [
            {
                type: 'item_added',
                title: 'New lost item reported',
                description: 'iPhone 14 Pro reported lost in Central Park',
                time: '2 hours ago',
                icon: 'lost'
            },
            {
                type: 'item_found',
                title: 'Item marked as found',
                description: 'Brown wallet found at Union Square',
                time: '4 hours ago',
                icon: 'found'
            },
            {
                type: 'user_registered',
                title: 'New user registered',
                description: 'John Doe joined the platform',
                time: '1 day ago',
                icon: 'user'
            },
            {
                type: 'item_reunited',
                title: 'Item successfully reunited',
                description: 'Gold ring returned to owner',
                time: '2 days ago',
                icon: 'success'
            }
        ];

        activityContainer.innerHTML = activities.map(activity => `
            <div class="activity-item">
                <div class="activity-icon ${activity.icon}">
                    ${this.getActivityIcon(activity.type)}
                </div>
                <div class="activity-content">
                    <div class="activity-title">${activity.title}</div>
                    <div class="activity-time">${activity.time}</div>
                </div>
            </div>
        `).join('');
    }

    getActivityIcon(type) {
        const icons = {
            item_added: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
            item_found: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22,4 12,14.01 9,11.01"></polyline></svg>',
            user_registered: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>',
            item_reunited: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>'
        };
        return icons[type] || '';
    }

    showSection(sectionName) {
        // Update active menu item
        document.querySelectorAll('.menu-item').forEach(item => {
            item.classList.remove('active');
            if (item.dataset.section === sectionName) {
                item.classList.add('active');
            }
        });

        // Show/hide sections
        document.querySelectorAll('.admin-section').forEach(section => {
            section.classList.remove('active');
        });

        const targetSection = document.getElementById(`${sectionName}-section`);
        if (targetSection) {
            targetSection.classList.add('active');
        }

        this.currentSection = sectionName;

        // Load section-specific data
        switch (sectionName) {
            case 'items':
                this.renderItemsTable();
                break;
            case 'users':
                this.renderUsersTable();
                break;
            case 'reports':
                this.renderReports();
                break;
        }
    }

    renderItemsTable() {
        const tbody = document.getElementById('itemsTableBody');
        if (!tbody) return;

        let filteredItems = [...this.items];

        // Apply filters
        const searchTerm = document.getElementById('itemSearch')?.value.toLowerCase() || '';
        const statusFilter = document.getElementById('itemStatusFilter')?.value || '';
        const categoryFilter = document.getElementById('itemCategoryFilter')?.value || '';

        if (searchTerm) {
            filteredItems = filteredItems.filter(item => 
                item.title.toLowerCase().includes(searchTerm) ||
                item.description.toLowerCase().includes(searchTerm)
            );
        }

        if (statusFilter) {
            filteredItems = filteredItems.filter(item => item.status === statusFilter);
        }

        if (categoryFilter) {
            filteredItems = filteredItems.filter(item => 
                item.category.toLowerCase() === categoryFilter.toLowerCase()
            );
        }

        tbody.innerHTML = filteredItems.map(item => `
            <tr>
                <td>
                    <div class="table-user">
                        <img src="${item.image}" alt="${item.title}" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;">
                        <div class="user-details">
                            <h4>${item.title}</h4>
                            <p>${item.description.substring(0, 50)}...</p>
                        </div>
                    </div>
                </td>
                <td>${item.category}</td>
                <td><span class="status-badge ${item.status}">${item.status}</span></td>
                <td>${item.location}</td>
                <td>${this.formatDate(item.date)}</td>
                <td>${item.reportedBy}</td>
                <td>
                    <div class="table-actions">
                        <button class="action-btn view" onclick="adminDashboard.viewItem('${item.id}')" title="View">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                        <button class="action-btn edit" onclick="adminDashboard.editItem('${item.id}')" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button class="action-btn delete" onclick="adminDashboard.deleteItem('${item.id}')" title="Delete">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3,6 5,6 21,6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    renderUsersTable() {
        const tbody = document.getElementById('usersTableBody');
        if (!tbody) return;

        let filteredUsers = [...this.authSystem.getUsers()];

        // Apply filters
        const searchTerm = document.getElementById('userSearch')?.value.toLowerCase() || '';
        const roleFilter = document.getElementById('userRoleFilter')?.value || '';
        const statusFilter = document.getElementById('userStatusFilter')?.value || '';

        if (searchTerm) {
            filteredUsers = filteredUsers.filter(user => 
                user.firstName.toLowerCase().includes(searchTerm) ||
                user.lastName.toLowerCase().includes(searchTerm) ||
                user.email.toLowerCase().includes(searchTerm)
            );
        }

        if (roleFilter) {
            filteredUsers = filteredUsers.filter(user => user.role === roleFilter);
        }

        if (statusFilter) {
            filteredUsers = filteredUsers.filter(user => user.status === statusFilter);
        }

        tbody.innerHTML = filteredUsers.map(user => `
            <tr>
                <td>
                    <div class="table-user">
                        <div class="user-avatar">${user.firstName.charAt(0)}${user.lastName.charAt(0)}</div>
                        <div class="user-details">
                            <h4>${user.firstName} ${user.lastName}</h4>
                            <p>${user.phone || 'No phone'}</p>
                        </div>
                    </div>
                </td>
                <td>${user.email}</td>
                <td><span class="status-badge ${user.role}">${user.role}</span></td>
                <td><span class="status-badge ${user.status}">${user.status}</span></td>
                <td>${this.formatDate(user.joinDate)}</td>
                <td>${user.itemsReported}</td>
                <td>
                    <div class="table-actions">
                        <button class="action-btn view" onclick="adminDashboard.viewUser('${user.id}')" title="View">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                        <button class="action-btn edit" onclick="adminDashboard.editUser('${user.id}')" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        ${user.id !== this.authSystem.currentUser.id ? `
                            <button class="action-btn delete" onclick="adminDashboard.deleteUser('${user.id}')" title="Delete">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3,6 5,6 21,6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `).join('');
    }

    renderReports() {
        // Render category statistics
        const categoryStats = document.getElementById('categoryStats');
        if (categoryStats) {
            const categories = {};
            this.items.forEach(item => {
                categories[item.category] = (categories[item.category] || 0) + 1;
            });

            categoryStats.innerHTML = Object.entries(categories).map(([category, count]) => `
                <div class="category-stat">
                    <span class="category-name">${category}</span>
                    <span class="category-count">${count}</span>
                </div>
            `).join('');
        }

        // Mock chart
        const chartCanvas = document.getElementById('monthlyChart');
        if (chartCanvas) {
            const ctx = chartCanvas.getContext('2d');
            ctx.fillStyle = '#f3f4f6';
            ctx.fillRect(0, 0, chartCanvas.width, chartCanvas.height);
            ctx.fillStyle = '#6b7280';
            ctx.font = '16px Inter';
            ctx.textAlign = 'center';
            ctx.fillText('Chart visualization would go here', chartCanvas.width / 2, chartCanvas.height / 2);
        }
    }

    // Modal functions
    showAddItemModal() {
        this.showModal('addItemModal');
    }

    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
        }
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
        }
    }

    handleAddItem(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const newItem = {
            id: Date.now().toString(),
            title: formData.get('title'),
            category: formData.get('category'),
            description: formData.get('description'),
            location: formData.get('location'),
            status: formData.get('status'),
            date: new Date().toISOString().split('T')[0],
            image: 'https://images.pexels.com/photos/1068523/pexels-photo-1068523.jpeg?auto=compress&cs=tinysrgb&w=400',
            reportedBy: `${this.authSystem.currentUser.firstName} ${this.authSystem.currentUser.lastName}`,
            contactInfo: this.authSystem.currentUser.email,
            userId: this.authSystem.currentUser.id
        };

        this.items.push(newItem);
        this.saveItems();
        this.renderItemsTable();
        this.updateDashboardStats();
        this.closeModal('addItemModal');
        this.showNotification('Item added successfully!', 'success');
        
        // Reset form
        e.target.reset();
    }

    // Item actions
    viewItem(itemId) {
        window.location.href = `item_detail.php?id=${itemId}`;
    }

    editItem(itemId) {
        const item = this.items.find(i => i.id === itemId);
        if (item) {
            document.getElementById('editItemId').value = item.id;
            document.getElementById('editItemTitle').value = item.title;
            document.getElementById('editItemCategory').value = item.category;
            document.getElementById('editItemDescription').value = item.description;
            document.getElementById('editItemLocation').value = item.location;
            document.getElementById('editItemDate').value = item.date;
            document.getElementById('editItemStatus').value = item.status;
            this.showModal('editItemModal');
        }
    }

    handleEditItem(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const itemId = formData.get('id');
        const itemIndex = this.items.findIndex(i => i.id === itemId);

        if (itemIndex > -1) {
            this.items[itemIndex] = {
                ...this.items[itemIndex],
                title: formData.get('title'),
                category: formData.get('category'),
                description: formData.get('description'),
                location: formData.get('location'),
                date: formData.get('date_lost_found'),
                status: formData.get('status')
            };
            this.saveItems();
            this.renderItemsTable();
            this.showNotification('Item updated successfully!', 'success');
            this.closeModal('editItemModal');
        }
    }

    deleteItem(itemId) {
        if (confirm('Are you sure you want to delete this item?')) {
            this.items = this.items.filter(i => i.id !== itemId);
            this.saveItems();
            this.renderItemsTable();
            this.updateDashboardStats();
            this.showNotification('Item deleted successfully!', 'success');
        }
    }

    // User actions
    viewUser(userId) {
        window.location.href = `user_detail.php?id=${userId}`;
    }

    editUser(userId) {
        const user = this.authSystem.getUsers().find(u => u.id === userId);
        if (user) {
            document.getElementById('editUserId').value = user.id;
            document.getElementById('editUserFirstName').value = user.firstName;
            document.getElementById('editUserLastName').value = user.lastName;
            document.getElementById('editUserEmail').value = user.email;
            document.getElementById('editUserPhone').value = user.phone;
            document.getElementById('editUserRole').value = user.role;
            document.getElementById('editUserStatus').value = user.status;
            this.showModal('editUserModal');
        }
    }

    handleEditUser(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const userId = formData.get('id');
        const userIndex = this.authSystem.getUsers().findIndex(u => u.id === userId);

        if (userIndex > -1) {
            const updatedUser = {
                ...this.authSystem.getUsers()[userIndex],
                firstName: formData.get('first_name'),
                lastName: formData.get('last_name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                role: formData.get('role'),
                status: formData.get('status')
            };
            this.authSystem.updateUser(userId, updatedUser);
            this.renderUsersTable();
            this.showNotification('User updated successfully!', 'success');
            this.closeModal('editUserModal');
        }
    }

    deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            this.authSystem.deleteUser(userId);
            this.renderUsersTable();
            this.updateDashboardStats();
            this.showNotification('User deleted successfully!', 'success');
        }
    }

    showAddUserModal() {
        this.showModal('addUserModal');
    }

    handleAddUser(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const newUser = {
            id: Date.now().toString(),
            firstName: formData.get('first_name'),
            lastName: formData.get('last_name'),
            email: formData.get('email'),
            password: formData.get('password'), // In a real app, hash this!
            phone: formData.get('phone'),
            role: formData.get('role'),
            status: formData.get('status'),
            joinDate: new Date().toISOString().split('T')[0],
            itemsReported: 0
        };

        this.authSystem.addUser(newUser);
        this.renderUsersTable();
        this.updateDashboardStats();
        this.closeModal('addUserModal');
        this.showNotification('User added successfully!', 'success');
        e.target.reset();
    }

    exportReport() {
        const headers = ["ID", "Title", "Category", "Description", "Location", "Date", "Status", "Reported By", "Contact Info"];
        const rows = this.items.map(item => [
            item.id,
            item.title,
            item.category,
            item.description.replace(/\n/g, " "), // Remove newlines for CSV
            item.location,
            item.date,
            item.status,
            item.reportedBy,
            item.contactInfo
        ]);

        let csvContent = "data:text/csv;charset=utf-8," + headers.join(",") + "\n";
        rows.forEach(row => {
            csvContent += row.join(",") + "\n";
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "items_report.csv");
        document.body.appendChild(link); // Required for Firefox
        link.click();
        document.body.removeChild(link);

        this.showNotification('Items report exported successfully!', 'success');
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }

    showNotification(message, type = 'info') {
        this.authSystem.showNotification(message, type);
    }
}

// Global functions for onclick handlers
function showSection(sectionName) {
    window.adminDashboard.showSection(sectionName);
}

function showAddItemModal() {
    window.adminDashboard.showAddItemModal();
}

function showAddUserModal() {
    window.adminDashboard.showAddUserModal();
}

function closeModal(modalId) {
    window.adminDashboard.closeModal(modalId);
}

function exportReport() {
    window.adminDashboard.exportReport();
}

function logout() {
    window.authSystem.logout();
}

// Initialize admin dashboard when page loads
document.addEventListener('DOMContentLoaded', function() {
    window.adminDashboard = new AdminDashboard();
});