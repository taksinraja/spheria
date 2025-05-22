document.addEventListener('DOMContentLoaded', function() {
    // Select all post items in profile page
    const postItems = document.querySelectorAll('.post-item');
    
    postItems.forEach(item => {
        item.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            if (!postId) return;
            
            // Load post modal content
            fetch(`/spheria1/includes/posts/get_post_modal.php?id=${postId}`)
                .then(response => response.text())
                .then(html => {
                    const modalContainer = document.getElementById('postModalContainer');
                    modalContainer.innerHTML = html;
                    
                    // Setup save button functionality
                    setupSaveButton();
                })
                .catch(error => console.error('Error:', error));
        });
    });
});

function setupSaveButton() {
    const saveBtn = document.querySelector('.save-post-btn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const postId = this.getAttribute('data-post-id');
            if (!postId) return;
            
            fetch('/spheria1/includes/posts/toggle_save.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Toggle save button appearance
                    if (data.saved) {
                        this.classList.add('saved');
                        this.querySelector('i').classList.remove('far');
                        this.querySelector('i').classList.add('fas');
                    } else {
                        this.classList.remove('saved');
                        this.querySelector('i').classList.remove('fas');
                        this.querySelector('i').classList.add('far');
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
}