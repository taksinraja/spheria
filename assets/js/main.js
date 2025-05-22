document.addEventListener('DOMContentLoaded', function() {
    // Load suggested profiles
    loadSuggestedProfiles();
    
    // Sidebar toggle functionality
    setupSidebarToggle();
    
    // We're removing the loadStories function call here
    // as it's now handled by stories.js
});

// Remove the loadStories function entirely since it contains hardcoded stories
// function loadStories() { ... }

function loadSuggestedProfiles() {
    const suggestionsContainer = document.querySelector('.suggestions-container');
    if (!suggestionsContainer) return;
    
    const suggestions = [
        { username: 'user1', image: 'assets/images/default-avatar.png' },
        { username: 'user2', image: 'assets/images/default-avatar.png' },
        { username: 'user3', image: 'assets/images/default-avatar.png' },
        { username: 'user4', image: 'assets/images/default-avatar.png' }
    ];

    suggestions.forEach(profile => {
        const profileElement = `
            <div class="suggestion-item d-flex align-items-center mb-3">
                <img src="${profile.image}" class="rounded-circle me-2" width="40" height="40">
                <div>
                    <h6 class="mb-0">${profile.username}</h6>
                    <small class="text-muted">liked by time_writer</small>
                </div>
                <button class="btn btn-sm btn-primary ms-auto">Follow</button>
            </div>
        `;
        suggestionsContainer.innerHTML += profileElement;
    });
}

function setupSidebarToggle() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (!sidebarToggle) {
        // Create toggle button if it doesn't exist
        const toggleBtn = document.createElement('button');
        toggleBtn.id = 'sidebarToggle';
        toggleBtn.className = 'sidebar-toggle';
        toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
        document.body.appendChild(toggleBtn);
    }
    
    // Check for saved sidebar state
    const sidebarOpen = localStorage.getItem('sidebarOpen') === 'true';
    if (sidebarOpen) {
        document.body.classList.add('sidebar-open');
    }
    
    // Toggle sidebar on button click
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.body.classList.toggle('sidebar-open');
        
        // Save sidebar state
        localStorage.setItem('sidebarOpen', document.body.classList.contains('sidebar-open'));
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const isMobile = window.innerWidth <= 576;
        const isClickInsideSidebar = event.target.closest('.messages-nav');
        const isClickOnToggle = event.target.closest('#sidebarToggle');
        
        if (isMobile && document.body.classList.contains('sidebar-open') && !isClickInsideSidebar && !isClickOnToggle) {
            document.body.classList.remove('sidebar-open');
            localStorage.setItem('sidebarOpen', 'false');
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 991) {
            document.body.classList.remove('sidebar-open');
            localStorage.setItem('sidebarOpen', 'false');
        }
    });
}