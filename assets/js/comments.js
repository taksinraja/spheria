
// document.addEventListener('DOMContentLoaded', function() {

//     // Comment Modal Functionality
//     document.querySelectorAll('.comment-btn').forEach(btn => {
//         btn.addEventListener('click', function(e) {
//             e.preventDefault();
//             const postId = this.closest('.post-card').dataset.postId;
//             openCommentModal(postId);
//         });
//     });
    
//     // Close comment modal when clicking the close button
//     const commentCloseBtn = document.querySelector('.comment-close');
//     if (commentCloseBtn) {
//         commentCloseBtn.addEventListener('click', function() {
//             document.querySelector('.comment-modal').classList.remove('active');
//         });
//     }
    
//     // Close comment modal when clicking outside
//     document.addEventListener('click', function(e) {
//         const commentModal = document.querySelector('.comment-modal');
//         if (commentModal && !commentModal.contains(e.target) && 
//             !e.target.closest('.comment-btn')) {
//             commentModal.classList.remove('active');
//         }
//     });
    
//     // Comment form submission
//     const commentForm = document.getElementById('comment-form');
//     if (commentForm) {
//         commentForm.addEventListener('submit', submitComment);
//     }
// });

// function openCommentModal(postId) {
//     const commentModal = document.querySelector('.comment-modal');
//     commentModal.classList.add('active');
    
//     // Load comments for this post
//     loadComments(postId);
    
//     // Set the current post ID for comment submission
//     document.getElementById('comment-form').dataset.postId = postId;
// }

// function loadComments(postId) {
//     // AJAX request to fetch comments
//     fetch(`includes/get_comments.php?post_id=${postId}`)
//         .then(response => response.json())
//         .then(data => {
//             const commentsContainer = document.querySelector('.comments-container');
//             commentsContainer.innerHTML = '';
            
//             if (data.length === 0) {
//                 commentsContainer.innerHTML = '<p class="no-comments">No comments yet. Be the first to share your thoughts!</p>';
//                 return;
//             }
            
//             data.forEach(comment => {
//                 const commentElement = createCommentElement(comment);
//                 commentsContainer.appendChild(commentElement);
//             });
//         })
//         .catch(error => {
//             console.error('Error loading comments:', error);
//         });
// }

// function createCommentElement(comment) {
//     const commentDiv = document.createElement('div');
//     commentDiv.className = 'comment-item';
    
//     commentDiv.innerHTML = `
//         <div class="comment-user-info">
//             <img src="${comment.profile_image || 'assets/images/default-avatar.png'}" alt="${comment.username}" class="rounded-circle">
//             <div>
//                 <h6>${comment.username}</h6>
//                 <small class="text-secondary">${comment.created_at}</small>
//             </div>
//         </div>
//         <p class="comment-text">${comment.comment_text}</p>
//     `;
    
//     return commentDiv;
// }

// function submitComment(event) {
//     event.preventDefault();
    
//     const form = document.getElementById('comment-form');
//     const postId = form.dataset.postId;
//     const commentInput = document.getElementById('comment-input');
//     const comment = commentInput.value.trim();
    
//     if (!comment) return;
    
//     // AJAX request to submit comment
//     fetch('includes/add_comment.php', {
//         method: 'POST',
//         headers: {
//             'Content-Type': 'application/x-www-form-urlencoded',
//         },
//         body: `post_id=${postId}&content=${encodeURIComponent(comment)}`
//     })
//     .then(response => response.json())
//     .then(data => {
//         if (data.success) {
//             // Clear input
//             commentInput.value = '';
            
//             // Reload comments
//             loadComments(postId);
            
//             // Update comment count in the feed
//             const commentCountElement = document.querySelector(`.post-card[data-post-id="${postId}"] .comments-count`);
//             if (commentCountElement) {
//                 commentCountElement.textContent = parseInt(commentCountElement.textContent) + 1;
//             }
//         }
//     })
//     .catch(error => {
//         console.error('Error submitting comment:', error);
//     });
// }