// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    const resultsContainer = document.getElementById('resultsContainer');
    
    // Search input handler
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        if (query.length < 1) {
            searchResults.style.display = 'none';
            return;
        }
        
        // Fetch search results
        fetch(`/spheria1/includes/search/search_users.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                resultsContainer.innerHTML = '';
                
                if (data.length === 0) {
                    resultsContainer.innerHTML = '<div class="no-results">No users found</div>';
                    searchResults.style.display = 'block';
                    return;
                }
                
                data.forEach(user => {
                    const profileImage = user.profile_image || '/spheria1/assets/images/default-avatar.png';
                    const displayName = user.full_name || user.username;
                    
                    // Determine button text and class based on following status
                    const buttonText = user.is_following ? 'Following' : 'Follow';
                    const buttonClass = user.is_following ? 'btn-secondary following' : 'btn-primary';
                    
                    const userHtml = `
                        <div class="search-result-item" data-user-id="${user.user_id}">
                            <div class="d-flex align-items-center">
                                <img src="${profileImage}" class="rounded-circle me-3" width="40" height="40">
                                <div class="user-info">
                                    <div class="username">${displayName}</div>
                                    <div class="handle">@${user.username}</div>
                                </div>
                                <div class="ms-auto">
                                    <button class="btn ${buttonClass} btn-sm follow-btn" onclick="followUser(${user.user_id}, this)">${buttonText}</button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    resultsContainer.innerHTML += userHtml;
                });
                
                searchResults.style.display = 'block';
                
                // Add click event to search results
                document.querySelectorAll('.search-result-item').forEach(item => {
                    item.addEventListener('click', function(e) {
                        // Don't trigger if clicking on the follow button
                        if (e.target.classList.contains('follow-btn') || e.target.closest('.follow-btn')) {
                            return;
                        }
                        
                        const userId = this.dataset.userId;
                        saveSearch(userId);
                        window.location.href = `/spheria1/profile.php?id=${userId}`;
                    });
                });
            })
            .catch(error => {
                console.error('Search error:', error);
                resultsContainer.innerHTML = '<div class="error">An error occurred while searching</div>';
                searchResults.style.display = 'block';
            });
    });
    
    // Follow user function
    window.followUser = function(userId, button) {
        // Disable button during request
        button.disabled = true;
        const originalText = button.textContent;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        
        fetch('/spheria1/includes/profile/follow.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}`
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`Server responded with status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Follow response:', data);
            
            if (data.success) {
                if (data.action === 'followed') {
                    button.textContent = 'Following';
                    button.classList.remove('btn-primary');
                    button.classList.add('btn-secondary', 'following');
                } else {
                    button.textContent = 'Follow';
                    button.classList.remove('btn-secondary', 'following');
                    button.classList.add('btn-primary');
                }
            } else {
                // Restore original button state on error
                button.textContent = originalText;
                console.error('Follow error:', data.message);
                alert('Failed to follow: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            // Restore original button state on error
            button.textContent = originalText;
            console.error('Error:', error);
            alert('An error occurred while following this user');
        })
        .finally(() => {
            // Re-enable button
            button.disabled = false;
        });
    };
    
    // Save search function
    function saveSearch(userId) {
        fetch('/spheria1/includes/search/save_search.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            console.log('Search saved:', data);
        })
        .catch(error => {
            console.error('Error saving search:', error);
        });
    }
    
    // Remove search history item
    document.querySelectorAll('.remove-search').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const searchId = this.dataset.searchId;
            const searchItem = this.closest('.recent-search-item');
            
            fetch('/spheria1/includes/search/remove_search.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `search_id=${searchId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    searchItem.remove();
                }
            })
            .catch(error => {
                console.error('Error removing search:', error);
            });
        });
    });
    
    // Click event for recent search items
    document.querySelectorAll('.recent-search-item').forEach(item => {
        item.addEventListener('click', function() {
            const userId = this.dataset.userId;
            window.location.href = `/spheria1/profile.php?id=${userId}`;
        });
    });
});