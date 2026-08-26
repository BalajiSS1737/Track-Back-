// Sample data for items
const itemsData = [
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
        contactInfo: 'sarah.m@email.com'
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
        contactInfo: 'john.d@email.com'
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
        contactInfo: 'emily.r@email.com'
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
        contactInfo: 'mike.t@email.com'
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
        contactInfo: 'lisa.k@email.com'
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
        contactInfo: 'anna.p@email.com'
    }
];

// Global variables
let currentFilter = 'all';
let currentView = 'grid';
let filteredItems = [...itemsData];

// DOM Elements
const itemsGrid = document.getElementById('itemsGrid');
const filterButtons = document.querySelectorAll('.filter-btn');
const viewButtons = document.querySelectorAll('.view-btn');
const filtersBtn = document.getElementById('filtersBtn');
const filtersPanel = document.getElementById('filtersPanel');
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const navMenu = document.getElementById('navMenu');
const reportForm = document.getElementById('reportForm');
const toggleButtons = document.querySelectorAll('.toggle-btn');
const submitBtn = document.getElementById('submitBtn');

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    renderItems();
    setupEventListeners();
    updateFilterCounts();
});

// Event Listeners
function setupEventListeners() {
    // Filter buttons
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;
            setActiveFilter(filter);
            filterItems(filter);
        });
    });

    // View toggle buttons
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.dataset.view;
            setActiveView(view);
            toggleView(view);
        });
    });

    // Filters panel toggle
    filtersBtn.addEventListener('click', function() {
        filtersPanel.classList.toggle('active');
    });

    // Mobile menu toggle
    mobileMenuBtn.addEventListener('click', function() {
        navMenu.classList.toggle('active');
    });

    // Report form type toggle
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const type = this.dataset.type;
            setActiveToggle(type);
            updateSubmitButton(type);
        });
    });

    // Report form submission
    reportForm.addEventListener('submit', handleFormSubmit);

    // Upload area click
    const uploadArea = document.getElementById('uploadArea');
    uploadArea.addEventListener('click', function() {
        // In a real application, this would trigger file input
        alert('File upload functionality would be implemented here');
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const locationInput = document.getElementById('locationInput');
    const categorySelect = document.getElementById('categorySelect');

    searchInput.addEventListener('input', performSearch);
    locationInput.addEventListener('input', performSearch);
    categorySelect.addEventListener('change', performSearch);
}

// Render items in the grid
function renderItems() {
    itemsGrid.innerHTML = '';
    
    filteredItems.forEach(item => {
        const itemCard = createItemCard(item);
        itemsGrid.appendChild(itemCard);
    });

    // Apply current view
    itemsGrid.className = `items-grid ${currentView === 'list' ? 'list-view' : ''}`;
}

// Create item card HTML
function createItemCard(item) {
    const card = document.createElement('div');
    card.className = 'item-card';
    card.innerHTML = `
        <div class="item-image">
            <img src="${item.image}" alt="${item.title}">
            <div class="item-status ${item.status}">${item.status.toUpperCase()}</div>
        </div>
        <div class="item-content">
            <h3 class="item-title">${item.title}</h3>
            <p class="item-description">${item.description}</p>
            <div class="item-details">
                <div class="item-detail">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                    </svg>
                    <span>${item.category}</span>
                </div>
                <div class="item-detail">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span>${item.location}</span>
                </div>
                <div class="item-detail">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>${formatDate(item.date)}</span>
                </div>
                <div class="item-detail">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>Reported by ${item.reportedBy}</span>
                </div>
            </div>
            <div class="item-actions">
                <button class="btn btn-primary" onclick="contactReporter('${item.id}')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Contact
                </button>
                <button class="btn btn-outline" onclick="viewDetails('${item.id}')">Details</button>
            </div>
        </div>
    `;
    return card;
}

// Filter items
function filterItems(filter) {
    currentFilter = filter;
    
    if (filter === 'all') {
        filteredItems = [...itemsData];
    } else {
        filteredItems = itemsData.filter(item => item.status === filter);
    }
    
    renderItems();
}

// Set active filter button
function setActiveFilter(filter) {
    filterButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.filter === filter) {
            btn.classList.add('active');
        }
    });
}

// Set active view button
function setActiveView(view) {
    currentView = view;
    viewButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.view === view) {
            btn.classList.add('active');
        }
    });
}

// Toggle view between grid and list
function toggleView(view) {
    if (view === 'list') {
        itemsGrid.classList.add('list-view');
    } else {
        itemsGrid.classList.remove('list-view');
    }
}

// Update filter button counts
function updateFilterCounts() {
    const allCount = itemsData.length;
    const lostCount = itemsData.filter(item => item.status === 'lost').length;
    const foundCount = itemsData.filter(item => item.status === 'found').length;
    
    filterButtons.forEach(btn => {
        const filter = btn.dataset.filter;
        let count = 0;
        
        switch(filter) {
            case 'all':
                count = allCount;
                break;
            case 'lost':
                count = lostCount;
                break;
            case 'found':
                count = foundCount;
                break;
        }
        
        btn.textContent = btn.textContent.replace(/\(\d+\)/, `(${count})`);
    });
}

// Set active toggle button for report form
function setActiveToggle(type) {
    toggleButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.type === type) {
            btn.classList.add('active');
        }
    });
}

// Update submit button text based on form type
function updateSubmitButton(type) {
    const text = type === 'lost' ? 'Report Lost Item' : 'Report Found Item';
    submitBtn.textContent = text;
    
    // Update button color
    submitBtn.className = `btn ${type === 'lost' ? 'btn-danger' : 'btn-success'}`;
}

// Handle form submission
function handleFormSubmit(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(reportForm);
    const itemData = {
        title: document.getElementById('itemTitle').value,
        category: document.getElementById('itemCategory').value,
        description: document.getElementById('itemDescription').value,
        location: document.getElementById('itemLocation').value,
        date: document.getElementById('itemDate').value,
        contactName: document.getElementById('contactName').value,
        contactEmail: document.getElementById('contactEmail').value,
        contactPhone: document.getElementById('contactPhone').value,
        type: document.querySelector('.toggle-btn.active').dataset.type
    };
    
    // In a real application, this would send data to a server
    console.log('Form submitted:', itemData);
    alert('Thank you! Your item has been reported successfully. You will be contacted if there are any matches.');
    
    // Reset form
    reportForm.reset();
}

// Search functionality
function performSearch() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const location = document.getElementById('locationInput').value.toLowerCase();
    const category = document.getElementById('categorySelect').value;
    
    let results = itemsData;
    
    // Filter by search term
    if (searchTerm) {
        results = results.filter(item => 
            item.title.toLowerCase().includes(searchTerm) ||
            item.description.toLowerCase().includes(searchTerm)
        );
    }
    
    // Filter by location
    if (location) {
        results = results.filter(item => 
            item.location.toLowerCase().includes(location)
        );
    }
    
    // Filter by category
    if (category) {
        results = results.filter(item => 
            item.category.toLowerCase() === category.toLowerCase()
        );
    }
    
    // Apply current status filter
    if (currentFilter !== 'all') {
        results = results.filter(item => item.status === currentFilter);
    }
    
    filteredItems = results;
    renderItems();
}

// Contact reporter function
function contactReporter(itemId) {
    const item = itemsData.find(i => i.id === itemId);
    if (item) {
        // In a real application, this would open a contact form or messaging system
        alert(`Contact ${item.reportedBy} at ${item.contactInfo}`);
    }
}

// View details function
function viewDetails(itemId) {
    const item = itemsData.find(i => i.id === itemId);
    if (item) {
        // In a real application, this would open a detailed view modal
        alert(`Viewing details for: ${item.title}`);
    }
}

// Scroll to section function
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}

// Format date function
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

// Add CSS class for danger button
const style = document.createElement('style');
style.textContent = `
    .btn-danger {
        background: #ef4444;
        color: white;
    }
    
    .btn-danger:hover {
        background: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }
`;
document.head.appendChild(style);