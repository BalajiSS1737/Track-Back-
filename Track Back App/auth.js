// Authentication System
class AuthSystem {
    constructor() {
        this.users = this.loadUsers();
        this.currentUser = this.getCurrentUser();
        this.init();
    }

    init() {
        // Check if user is already logged in
        if (this.currentUser && window.location.pathname.includes('login.html')) {
            this.redirectToDashboard();
        }

        // Setup form listeners
        this.setupFormListeners();
    }

    loadUsers() {
        const defaultUsers = [
            {
                id: '1',
                firstName: 'Admin',
                lastName: 'User',
                email: 'admin@trackback.com',
                password: 'admin123',
                phone: '+1 (555) 123-4567',
                role: 'admin',
                status: 'active',
                joinDate: '2024-01-01',
                itemsReported: 0
            },
            {
                id: '2',
                firstName: 'John',
                lastName: 'Doe',
                email: 'john@example.com',
                password: 'user123',
                phone: '+1 (555) 234-5678',
                role: 'user',
                status: 'active',
                joinDate: '2024-01-15',
                itemsReported: 2
            },
            {
                id: '3',
                firstName: 'Jane',
                lastName: 'Smith',
                email: 'jane@example.com',
                password: 'user123',
                phone: '+1 (555) 345-6789',
                role: 'user',
                status: 'active',
                joinDate: '2024-02-01',
                itemsReported: 1
            },
            {
                id: '4',
                firstName: 'Mike',
                lastName: 'Johnson',
                email: 'mike@example.com',
                password: 'user123',
                phone: '+1 (555) 456-7890',
                role: 'user',
                status: 'active',
                joinDate: '2024-02-10',
                itemsReported: 3
            },
            {
                id: '5',
                firstName: 'Sarah',
                lastName: 'Wilson',
                email: 'sarah@example.com',
                password: 'user123',
                phone: '+1 (555) 567-8901',
                role: 'user',
                status: 'inactive',
                joinDate: '2024-01-20',
                itemsReported: 0
            }
        ];

        const stored = localStorage.getItem('trackback_users');
        return stored ? JSON.parse(stored) : defaultUsers;
    }

    saveUsers() {
        localStorage.setItem('trackback_users', JSON.stringify(this.users));
    }

    getCurrentUser() {
        const stored = localStorage.getItem('trackback_current_user');
        return stored ? JSON.parse(stored) : null;
    }

    setCurrentUser(user) {
        this.currentUser = user;
        localStorage.setItem('trackback_current_user', JSON.stringify(user));
    }

    setupFormListeners() {
        // Login form
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        // Register form
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => this.handleRegister(e));
            
            // Password strength checker
            const passwordInput = document.getElementById('password');
            if (passwordInput) {
                passwordInput.addEventListener('input', (e) => this.checkPasswordStrength(e.target.value));
            }
        }
    }

    async handleLogin(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const email = formData.get('email');
        const password = formData.get('password');
        const rememberMe = formData.get('rememberMe');

        this.showLoading(true);

        // Simulate API call delay
        await new Promise(resolve => setTimeout(resolve, 1000));

        const user = this.users.find(u => u.email === email && u.password === password);

        if (user) {
            if (user.status === 'inactive') {
                this.showNotification('Your account has been deactivated. Please contact support.', 'error');
                this.showLoading(false);
                return;
            }

            this.setCurrentUser(user);
            this.showNotification('Login successful! Redirecting...', 'success');
            
            setTimeout(() => {
                this.redirectToDashboard();
            }, 1500);
        } else {
            this.showNotification('Invalid email or password. Please try again.', 'error');
            this.showLoading(false);
        }
    }

    async handleRegister(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const userData = {
            firstName: formData.get('firstName'),
            lastName: formData.get('lastName'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            password: formData.get('password'),
            confirmPassword: formData.get('confirmPassword')
        };

        // Validation
        if (userData.password !== userData.confirmPassword) {
            this.showNotification('Passwords do not match!', 'error');
            return;
        }

        if (this.users.find(u => u.email === userData.email)) {
            this.showNotification('An account with this email already exists!', 'error');
            return;
        }

        this.showLoading(true);

        // Simulate API call delay
        await new Promise(resolve => setTimeout(resolve, 1500));

        // Create new user
        const newUser = {
            id: Date.now().toString(),
            firstName: userData.firstName,
            lastName: userData.lastName,
            email: userData.email,
            password: userData.password,
            phone: userData.phone || '',
            role: 'user',
            status: 'active',
            joinDate: new Date().toISOString().split('T')[0],
            itemsReported: 0
        };

        this.users.push(newUser);
        this.saveUsers();
        this.setCurrentUser(newUser);

        this.showNotification('Account created successfully! Redirecting...', 'success');
        
        setTimeout(() => {
            this.redirectToDashboard();
        }, 1500);
    }

    loginAsDemo(type) {
        let user;
        if (type === 'admin') {
            user = this.users.find(u => u.role === 'admin');
        } else {
            user = this.users.find(u => u.role === 'user' && u.status === 'active');
        }

        if (user) {
            this.setCurrentUser(user);
            this.showNotification(`Logged in as ${type}! Redirecting...`, 'success');
            
            setTimeout(() => {
                this.redirectToDashboard();
            }, 1000);
        }
    }

    logout() {
        localStorage.removeItem('trackback_current_user');
        this.currentUser = null;
        this.showNotification('Logged out successfully!', 'success');
        
        setTimeout(() => {
            window.location.href = 'index.html';
        }, 1000);
    }

    redirectToDashboard() {
        if (this.currentUser.role === 'admin') {
            window.location.href = 'admin.html';
        } else {
            window.location.href = 'index.html';
        }
    }

    checkPasswordStrength(password) {
        const strengthIndicator = document.getElementById('passwordStrength');
        if (!strengthIndicator) return;

        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        strengthIndicator.className = 'password-strength';
        
        if (strength < 3) {
            strengthIndicator.classList.add('weak');
        } else if (strength < 5) {
            strengthIndicator.classList.add('medium');
        } else {
            strengthIndicator.classList.add('strong');
        }
    }

    showLoading(show) {
        const submitBtn = document.querySelector('.auth-submit');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoader = submitBtn.querySelector('.btn-loader');

        if (show) {
            btnText.style.opacity = '0';
            btnLoader.style.display = 'block';
            submitBtn.disabled = true;
        } else {
            btnText.style.opacity = '1';
            btnLoader.style.display = 'none';
            submitBtn.disabled = false;
        }
    }

    showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span>${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    // Public methods for global access
    getUsers() {
        return this.users;
    }

    addUser(userData) {
        const newUser = {
            id: Date.now().toString(),
            ...userData,
            joinDate: new Date().toISOString().split('T')[0],
            itemsReported: 0
        };
        
        this.users.push(newUser);
        this.saveUsers();
        return newUser;
    }

    updateUser(userId, updates) {
        const userIndex = this.users.findIndex(u => u.id === userId);
        if (userIndex !== -1) {
            this.users[userIndex] = { ...this.users[userIndex], ...updates };
            this.saveUsers();
            return this.users[userIndex];
        }
        return null;
    }

    deleteUser(userId) {
        this.users = this.users.filter(u => u.id !== userId);
        this.saveUsers();
    }
}

// Password toggle functionality
function togglePassword(inputId = 'password') {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    
    if (input.type === 'password') {
        input.type = 'text';
        button.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
        `;
    } else {
        input.type = 'password';
        button.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        `;
    }
}

// Demo login functions
function loginAsDemo(type) {
    window.authSystem.loginAsDemo(type);
}

function logout() {
    window.authSystem.logout();
}

// Initialize auth system
window.authSystem = new AuthSystem();

// Add notification styles if not already present
if (!document.querySelector('#notification-styles')) {
    const notificationStyles = document.createElement('style');
    notificationStyles.id = 'notification-styles';
    notificationStyles.textContent = `
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.3s ease;
        }
        
        .notification-info {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            color: #1e40af;
        }
        
        .notification-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }
        
        .notification-error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }
        
        .notification-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
        }
        
        .notification-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            margin-left: 12px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        
        .notification-close:hover {
            background: rgba(0, 0, 0, 0.1);
        }
        
        .notification-close svg {
            width: 16px;
            height: 16px;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(notificationStyles);
}