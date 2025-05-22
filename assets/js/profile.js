document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons and panes
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Add active class to clicked button and corresponding pane
            this.classList.add('active');
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Profile and cover image upload functionality
    const profileImageInput = document.getElementById('profileImageInput');
    const coverPhotoInput = document.getElementById('coverPhotoInput');
    
    if (profileImageInput) {
        profileImageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                // Validate file type on client side
                const file = this.files[0];
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, PNG, GIF, WEBP)');
                    this.value = ''; // Clear the input
                    return;
                }
                
                if (file.size > 5 * 1024 * 1024) { // 5MB limit
                    alert('File size too large. Please select an image under 5MB.');
                    this.value = ''; // Clear the input
                    return;
                }
                
                const formData = new FormData(document.getElementById('profileImageForm'));
                uploadImage(formData, 'profile_image');
            }
        });
    }
    
    if (coverPhotoInput) {
        coverPhotoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                // Validate file type on client side
                const file = this.files[0];
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, PNG, GIF, WEBP)');
                    this.value = ''; // Clear the input
                    return;
                }
                
                if (file.size > 5 * 1024 * 1024) { // 5MB limit
                    alert('File size too large. Please select an image under 5MB.');
                    this.value = ''; // Clear the input
                    return;
                }
                
                const formData = new FormData(document.getElementById('coverPhotoForm'));
                uploadImage(formData, 'cover_image');
            }
        });
    }
    
    // Function to handle image uploads
    function uploadImage(formData, imageType) {
        // Show loading indicator
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'loading-indicator';
        loadingIndicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        loadingIndicator.style.position = 'fixed';
        loadingIndicator.style.top = '50%';
        loadingIndicator.style.left = '50%';
        loadingIndicator.style.transform = 'translate(-50%, -50%)';
        loadingIndicator.style.backgroundColor = 'rgba(0,0,0,0.7)';
        loadingIndicator.style.color = 'white';
        loadingIndicator.style.padding = '20px';
        loadingIndicator.style.borderRadius = '5px';
        loadingIndicator.style.zIndex = '9999';
        document.body.appendChild(loadingIndicator);
        
        fetch('includes/profile/update.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            return response.json().catch(error => {
                return { success: false, message: 'Invalid server response' };
            });
        })
        .then(data => {
            // Remove loading indicator
            document.body.removeChild(loadingIndicator);
            
            if (data.success) {
                // Update the image on the page
                if (imageType === 'profile_image') {
                    document.querySelector('.profile-picture img').src = data.file_path + '?t=' + new Date().getTime();
                } else if (imageType === 'cover_image') {
                    document.querySelector('.cover-photo img').src = data.file_path + '?t=' + new Date().getTime();
                }
                
                // Show success message
                alert('Image updated successfully!');
            } else {
                // Show error message
                alert('Error: ' + (data.message || 'Failed to update image'));
            }
        })
        .catch(error => {
            // Remove loading indicator
            if (document.querySelector('.loading-indicator')) {
                document.body.removeChild(loadingIndicator);
            }
            
            alert('An error occurred while uploading the image');
        });
    }
    
    // Initialize post items in profile page with modal functionality
    initializeProfilePosts();
});

// Function to initialize post items in profile page
function initializeProfilePosts() {
    const postItems = document.querySelectorAll('.post-item');
    postItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const postId = this.getAttribute('data-post-id');
            if (!postId) return;

            // Create or get modal container
            let modalContainer = document.getElementById('postModalContainer');
            if (!modalContainer) {
                modalContainer = document.createElement('div');
                modalContainer.id = 'postModalContainer';
                document.body.appendChild(modalContainer);
            }

            // Show loading state
            modalContainer.innerHTML = `
                <div class="post-modal">
                    <div class="loading-indicator">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                </div>
            `;

            // Show the modal
            modalContainer.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            // Fetch post content
            fetch(`includes/posts/get_post_modal.php?id=${postId}`)
                .then(response => response.text())
                .then(html => {
                    modalContainer.innerHTML = html;

                    // Setup close functionality
                    const closeBtn = modalContainer.querySelector('.btn-close-white');
                    if (closeBtn) {
                        closeBtn.addEventListener('click', function() {
                            closeModal(modalContainer);
                        });
                    }

                    // Close on background click
                    modalContainer.addEventListener('click', function(e) {
                        if (e.target === modalContainer) {
                            closeModal(modalContainer);
                        }
                    });

                    // Initialize post interactions
                    initializePostInteractions(modalContainer);
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalContainer.innerHTML = `
                        <div class="post-modal">
                            <div class="error-message">
                                Failed to load post. Please try again.
                            </div>
                        </div>
                    `;
                });
        });
    });
}

// Function to close modal
function closeModal(modalContainer) {
    modalContainer.style.display = 'none';
    document.body.style.overflow = 'auto';
    modalContainer.innerHTML = '';
}

// Function to initialize post interactions within the modal
function initializePostInteractions(modalContainer) {
    // Like button functionality
    const likeBtn = modalContainer.querySelector('.like-btn');
    if (likeBtn) {
        likeBtn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const likeIcon = this.querySelector('i');
            const likesCountElement = modalContainer.querySelector('.likes-count');
            
            fetch('includes/posts/toggle_like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.liked) {
                        likeIcon.classList.remove('far');
                        likeIcon.classList.add('fas');
                        this.classList.add('liked');
                    } else {
                        likeIcon.classList.remove('fas');
                        likeIcon.classList.add('far');
                        this.classList.remove('liked');
                    }
                    likesCountElement.innerHTML = `<strong>${data.likes_count}</strong> likes`;
                }
            });
        });
    }

    // Comment form functionality
    const commentForm = modalContainer.querySelector('.comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('includes/comments/add_comment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear input
                    this.querySelector('input[name="comment_text"]').value = '';
                    
                    // Add new comment to the top
                    const commentsSection = modalContainer.querySelector('.comments-section');
                    const newComment = document.createElement('div');
                    newComment.className = 'comment';
                    newComment.innerHTML = `
                        <div class="user-info">
                            <img src="${data.comment.profile_image}" alt="Profile">
                            <div>
                                <a href="profile.php?username=${data.comment.username}">${data.comment.username}</a>
                                <p>${data.comment.comment_text}</p>
                                <small class="text-muted">Just now</small>
                            </div>
                        </div>
                    `;
                    commentsSection.insertBefore(newComment, commentsSection.firstChild);
                }
            });
        });
    }
}