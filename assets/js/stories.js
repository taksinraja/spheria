document.addEventListener('DOMContentLoaded', function() {
    const storiesContainer = document.querySelector('.stories-wrapper');
    let currentStoryIndex = 0;
    let allStories = [];
    let userStories = {}; // To group stories by user
    let currentUserIndex = 0;
    let currentUserStoryIndex = 0;
    let userStoriesArray = []; // Array of user story groups
    
    // Add story viewer modal to the body
    document.body.insertAdjacentHTML('beforeend', `
        <div class="story-viewer-modal">
            <div class="story-viewer-container">
                <div class="story-progress-bar"></div>
                <div class="story-header">
                    <div class="user-info">
                        <img src="" alt="" class="story-user-avatar">

                        <div class="story-user-details">
                            <span class="story-username"></span>
                            <small class="story-timestamp">Just now</small>
                        </div>
                    </div>
                    <div class="story-option">
                        <button class="close-story"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <div class="story-content">
                    <div class="story-media">
                    </div>
                </div>
                <div class="story-footer">
                    <div class="story-reply">
                        <input type="text" placeholder="Reply to story...">
                        <button class="send-reply"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
            <button class="story-nav-btn prev-story"><i class="fas fa-chevron-left"></i></button>
            <button class="story-nav-btn next-story"><i class="fas fa-chevron-right"></i></button>
        </div>
    `);

    // Modify the click event in displayStories function
    function displayStories(stories) {
        if (!storiesContainer) return;
        allStories = stories;
        
        // Group stories by user_id
        userStories = {};
        stories.forEach(story => {
            if (!userStories[story.user_id]) {
                userStories[story.user_id] = {
                    user_id: story.user_id,
                    username: story.username,
                    profile_image: story.profile_image,
                    stories: []
                };
            }
            userStories[story.user_id].stories.push(story);
        });
        
        // Convert to array for easier navigation
        userStoriesArray = Object.values(userStories);
        
        // Get viewed stories from localStorage
        const viewedStories = JSON.parse(localStorage.getItem('viewedStories') || '{}');
        
        // Clear existing content
        storiesContainer.innerHTML = '';

        // Add create story option
        const createStoryHTML = `
            <div class="story-item">
                <a href="create_story.php" class="story-link">
                    <div class="story-avatar">
                        <div class="create-story-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                </a>
            </div>
        `;
        storiesContainer.innerHTML = createStoryHTML;

        // Add stories grouped by user
        userStoriesArray.forEach((userStoryGroup, index) => {
            // Create a container for the story item
            const storyItemContainer = document.createElement('div');
            storyItemContainer.className = 'story-item';
            
            // Create the HTML structure for the story avatar
            const storyHTML = `
                <div class="story-avatar-wrapper">
                    <div class="story-avatar" 
                         data-user-id="${userStoryGroup.user_id}"
                         data-user-index="${index}">
                        <div class="story-border">
                            <img src="${userStoryGroup.stories[0].media_type === 'image' ? userStoryGroup.stories[0].file_path : 'assets/images/video-thumbnail.png'}" 
                                 alt="${userStoryGroup.username}'s story"
                                 data-story-id="${userStoryGroup.stories[0].story_id}"
                                 class="story-preview-img">
                            
                            <span class="story-username">
                            <img src="${userStoryGroup.profile_image || 'assets/images/default-avatar.png'}" 
                                 alt="${userStoryGroup.username}'s avatar"
                                 class="story-username-avatar">
                            ${userStoryGroup.username}</span>
                        </div>
                    </div>
                </div>
            `;
            storyItemContainer.innerHTML = storyHTML;
            storiesContainer.appendChild(storyItemContainer);
            
            // If it's a video, create a video element to capture the thumbnail
            if (userStoryGroup.stories[0].media_type === 'video') {
                const imgElement = storyItemContainer.querySelector('.story-preview-img');
                const videoElement = document.createElement('video');
                videoElement.src = userStoryGroup.stories[0].file_path;
                videoElement.style.display = 'none';
                videoElement.muted = true;
                
                // When video metadata is loaded, capture a frame
                videoElement.addEventListener('loadedmetadata', function() {
                    // Seek to 1 second or 25% into the video, whichever is less
                    const seekTime = Math.min(1, videoElement.duration * 0.25);
                    videoElement.currentTime = seekTime;
                });
                
                // When the video has seeked to the specified time
                videoElement.addEventListener('seeked', function() {
                    // Create a canvas to capture the video frame
                    const canvas = document.createElement('canvas');
                    canvas.width = videoElement.videoWidth;
                    canvas.height = videoElement.videoHeight;
                    const ctx = canvas.getContext('2d');
                    
                    // Draw the video frame on the canvas
                    ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
                    
                    // Set the canvas image as the source for the img element
                    try {
                        imgElement.src = canvas.toDataURL('image/jpeg');
                    } catch(e) {
                        console.error('Error creating thumbnail:', e);
                        // Keep the default thumbnail if there's an error
                    }
                    
                    // Clean up
                    document.body.removeChild(videoElement);
                });
                
                // Add the video element to the DOM to load it
                document.body.appendChild(videoElement);
            }
        });

        // Add click event listeners to story avatars
        document.querySelectorAll('.story-avatar').forEach(avatar => {
            if (!avatar.classList.contains('create-story-icon')) {
                avatar.addEventListener('click', async () => {
                    const userIndex = parseInt(avatar.dataset.userIndex);
                    
                    if (userIndex !== undefined) {
                        try {
                            currentUserIndex = userIndex;
                            currentUserStoryIndex = 0; // Start with first story of this user
                            const userStoryGroup = userStoriesArray[userIndex];
                            const firstStory = userStoryGroup.stories[0];
                            
                            const response = await fetch(`includes/stories/get_story.php?story_id=${firstStory.story_id}`);
                            if (!response.ok) throw new Error('Story not found');
                            const story = await response.json();
                            
                            // Show the story with total count info
                            showStoryViewer(story, {
                                currentIndex: 0,
                                totalStories: userStoryGroup.stories.length
                            });
                        } catch (error) {
                            console.error('Error loading story:', error);
                        }
                    }
                });
            }
        });
    }

    // Update showStoryViewer to handle multiple stories per user
    function showStoryViewer(story, storyInfo) {
        const modal = document.querySelector('.story-viewer-modal');
        const userAvatar = modal.querySelector('.story-user-avatar');
        const username = modal.querySelector('.story-username');
        const mediaContainer = modal.querySelector('.story-media');
        const progressBar = modal.querySelector('.story-progress-bar');
        const timestamp = modal.querySelector('.story-timestamp');
    
        // Mark the story as viewed in the UI
        const storyAvatar = document.querySelector(`.story-avatar[data-user-index="${currentUserIndex}"]`);
        if (storyAvatar) {
            storyAvatar.classList.add('viewed');
            
            // Save viewed status to localStorage
            const viewedStories = JSON.parse(localStorage.getItem('viewedStories') || '{}');
            viewedStories[userStoriesArray[currentUserIndex].user_id] = {
                timestamp: Date.now(),
                lastStoryId: story.story_id
            };
            localStorage.setItem('viewedStories', JSON.stringify(viewedStories));
        }
    
        // Clear any existing animation and timeout
        if (window.storyTimeout) {
            clearTimeout(window.storyTimeout);
        }
        
        // Reset progress bar animation
        progressBar.style.animation = 'none';
        void progressBar.offsetWidth; // Trigger reflow to restart animation
        progressBar.style.animation = 'progress 5s linear forwards';
    
        userAvatar.src = story.profile_image || 'assets/images/default-avatar.png';
        username.textContent = story.username;
    
        // Update timestamp with story count if available
        if (storyInfo) {
            timestamp.textContent = `${storyInfo.currentIndex + 1}/${storyInfo.totalStories}`;
        } else {
            timestamp.textContent = 'Just now';
        }
    
        // Check if this story belongs to the current user and add delete button if it does
        const existingDeleteBtn = modal.querySelector('.delete-story-btn');
        if (existingDeleteBtn) {
            existingDeleteBtn.remove();
        }
        
        if (story.user_id == document.body.getAttribute('data-user-id')) {
            const storyOption = modal.querySelector('.story-option');
            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'delete-story-btn';
            deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
            deleteBtn.setAttribute('data-story-id', story.story_id);
            storyOption.appendChild(deleteBtn);
            
            // Add event listener for delete button
            deleteBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (confirm('Are you sure you want to delete this story?')) {
                    deleteStory(story.story_id);
                }
            });
        }
    
        if (story.media_type === 'image') {
            mediaContainer.innerHTML = `<img src="${story.file_path}" alt="Story">`;
            
            // Set timeout for auto-navigation after 5 seconds
            window.storyTimeout = setTimeout(() => {
                navigateUserStory('next');
            }, 5000);
            
        } else if (story.media_type === 'video') {
            mediaContainer.innerHTML = `
                <video autoplay>
                    <source src="${story.file_path}" type="${story.mime_type}">
                </video>
            `;
            
            const video = mediaContainer.querySelector('video');
            
            video.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent closing the story
                if (video.paused) {
                    video.play();
                    // Resume progress bar animation
                    const progressBar = document.querySelector('.story-progress-bar');
                    const remainingTime = video.duration - video.currentTime;
                    progressBar.style.animationPlayState = 'running';
                    
                    // Reset the timeout for auto-navigation
                    if (window.storyTimeout) {
                        clearTimeout(window.storyTimeout);
                    }
                    window.storyTimeout = setTimeout(() => {
                        navigateUserStory('next');
                    }, remainingTime * 1000);
                } else {
                    video.pause();
                    // Pause progress bar animation
                    const progressBar = document.querySelector('.story-progress-bar');
                    progressBar.style.animationPlayState = 'paused';
                    
                    // Clear the timeout for auto-navigation
                    if (window.storyTimeout) {
                        clearTimeout(window.storyTimeout);
                    }
                }
            });
            video.onloadedmetadata = function() {
                // Adjust progress bar animation duration to match video duration
                progressBar.style.animation = `progress ${video.duration * 1000}ms linear forwards`;
                
                // Set timeout for auto-navigation after video ends
                window.storyTimeout = setTimeout(() => {
                    navigateUserStory('next');
                }, video.duration * 1000);
            };
            
            video.onended = function() {
                navigateUserStory('next');
            };
        }
    
        modal.classList.add('active');
    }

    // Add the deleteStory function
    function deleteStory(storyId) {
        const formData = new FormData();
        formData.append('story_id', storyId);
        
        fetch('includes/stories/delete_story.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close the story viewer
                document.querySelector('.story-viewer-modal').classList.remove('active');
                
                // Remove the story from the userStoriesArray
                const currentUserStories = userStoriesArray[currentUserIndex].stories;
                const storyIndex = currentUserStories.findIndex(s => s.story_id == storyId);
                
                if (storyIndex !== -1) {
                    currentUserStories.splice(storyIndex, 1);
                    
                    // If no more stories for this user, remove the user from the array
                    if (currentUserStories.length === 0) {
                        userStoriesArray.splice(currentUserIndex, 1);
                        
                        // Refresh the stories display
                        displayStories(allStories.filter(s => s.story_id != storyId));
                    } else {
                        // Navigate to the next story or previous if this was the last one
                        if (currentUserStoryIndex >= currentUserStories.length) {
                            currentUserStoryIndex = currentUserStories.length - 1;
                        }
                        
                        // Show the next available story
                        if (currentUserStories[currentUserStoryIndex]) {
                            fetch(`includes/stories/get_story.php?story_id=${currentUserStories[currentUserStoryIndex].story_id}`)
                                .then(response => response.json())
                                .then(story => {
                                    showStoryViewer(story, {
                                        currentIndex: currentUserStoryIndex,
                                        totalStories: currentUserStories.length
                                    });
                                })
                                .catch(error => {
                                    console.error('Error loading next story:', error);
                                    navigateStory('next');
                                });
                        } else {
                            navigateStory('next');
                        }
                    }
                }
                
                // Show success message
                const notification = document.createElement('div');
                notification.className = 'notification success';
                notification.textContent = 'Story deleted successfully';
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.classList.add('show');
                    setTimeout(() => {
                        notification.classList.remove('show');
                        setTimeout(() => {
                            notification.remove();
                        }, 300);
                    }, 3000);
                }, 100);
                
            } else {
                console.error('Error deleting story:', data.message);
                alert('Failed to delete story: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting story:', error);
            alert('An error occurred while deleting the story');
        });
    }

    // New function to navigate between stories of the same user
    function navigateUserStory(direction) {
        if (window.storyTimeout) {
            clearTimeout(window.storyTimeout);
        }
        
        // Stop current video if playing
        const videoElement = document.querySelector('.story-media video');
        if (videoElement) {
            videoElement.pause();
            videoElement.currentTime = 0;
            videoElement.src = ""; // Clear the source
        }
        
        const currentUserStories = userStoriesArray[currentUserIndex].stories;
        
        if (direction === 'next') {
            currentUserStoryIndex++;
            
            // If we've seen all stories from this user, move to next user
            if (currentUserStoryIndex >= currentUserStories.length) {
                currentUserStoryIndex = 0;
                return navigateStory('next');
            }
        } else {
            currentUserStoryIndex--;
            
            // If we're at the first story of this user, go to previous user
            if (currentUserStoryIndex < 0) {
                currentUserStoryIndex = 0;
                return navigateStory('prev');
            }
        }
        
        // Load the next/prev story of the current user
        const nextStory = currentUserStories[currentUserStoryIndex];
        if (nextStory) {
            fetch(`includes/stories/get_story.php?story_id=${nextStory.story_id}`)
                .then(response => {
                    if (!response.ok) throw new Error('Story not found');
                    return response.json();
                })
                .then(story => {
                    showStoryViewer(story, {
                        currentIndex: currentUserStoryIndex,
                        totalStories: currentUserStories.length
                    });
                })
                .catch(error => {
                    console.error('Error loading next story:', error);
                });
        }
    }

    // Update the navigation function to navigate between users
    function navigateStory(direction) {
        if (window.storyTimeout) {
            clearTimeout(window.storyTimeout);
        }
        
        // Stop current video if playing
        const videoElement = document.querySelector('.story-media video');
        if (videoElement) {
            videoElement.pause();
            videoElement.currentTime = 0;
            videoElement.src = ""; // Clear the source
        }
        
        if (direction === 'next') {
            currentUserIndex++;
            if (currentUserIndex >= userStoriesArray.length) {
                currentUserIndex = 0;
                // Close the viewer if we've gone through all users' stories
                document.querySelector('.story-viewer-modal').classList.remove('active');
                return;
            }
        } else {
            currentUserIndex--;
            if (currentUserIndex < 0) {
                currentUserIndex = 0;
                return;
            }
        }
        
        // Reset to first story of the next/prev user
        currentUserStoryIndex = 0;
        const nextUserStories = userStoriesArray[currentUserIndex].stories;
        
        if (nextUserStories && nextUserStories.length > 0) {
            fetch(`includes/stories/get_story.php?story_id=${nextUserStories[0].story_id}`)
                .then(response => {
                    if (!response.ok) throw new Error('Story not found');
                    return response.json();
                })
                .then(story => {
                    showStoryViewer(story, {
                        currentIndex: 0,
                        totalStories: nextUserStories.length
                    });
                })
                .catch(error => {
                    console.error('Error loading next user story:', error);
                });
        } else {
            // Close the viewer if no more stories
            document.querySelector('.story-viewer-modal').classList.remove('active');
        }
    }

    // Update navigation button event listeners
    document.querySelector('.prev-story').addEventListener('click', (e) => {
        e.stopPropagation();
        navigateUserStory('prev');
    });
    
    document.querySelector('.next-story').addEventListener('click', (e) => {
        e.stopPropagation();
        navigateUserStory('next');
    });

    // Update the close story event listener to stop video playback
    document.querySelector('.close-story').addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent event from bubbling up
        
        // Clear any existing timeout
        if (window.storyTimeout) {
            clearTimeout(window.storyTimeout);
        }
        
        // Stop video playback if present
        const videoElement = document.querySelector('.story-media video');
        if (videoElement) {
            videoElement.pause();
            videoElement.currentTime = 0;
            videoElement.src = ""; // Clear the source
        }
        
        document.querySelector('.story-viewer-modal').classList.remove('active');
    });

    // Add event listener for story reply
    document.querySelector('.send-reply').addEventListener('click', function() {
        const replyInput = document.querySelector('.story-reply input');
        const replyText = replyInput.value.trim();
        
        if (replyText) {
            // Get current story user ID
            const currentUserStories = userStoriesArray[currentUserIndex];
            const storyOwnerId = currentUserStories.user_id;
            
            // First check if a conversation exists or create one
            fetch('includes/messages/get_or_create_conversation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `recipient_id=${storyOwnerId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Now send the message with the conversation_id
                    return fetch('includes/messages/send_message.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `conversation_id=${data.conversation_id}&message=${encodeURIComponent(replyText)}`
                    });
                } else {
                    throw new Error(data.message || 'Failed to get conversation');
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear input and show success feedback
                    replyInput.value = '';
                    
                    // Show a temporary success message
                    const replyDiv = document.querySelector('.story-reply');
                    const successMsg = document.createElement('div');
                    successMsg.className = 'reply-success';
                    successMsg.textContent = 'Reply sent!';
                    successMsg.style.color = '#a970ff';
                    successMsg.style.fontSize = '12px';
                    successMsg.style.marginTop = '5px';
                    
                    replyDiv.appendChild(successMsg);
                    
                    // Remove success message after 2 seconds
                    setTimeout(() => {
                        successMsg.remove();
                    }, 2000);
                } else {
                    console.error('Error sending reply:', data.message || 'Unknown error');
                }
            })
            .catch(error => {
                console.error('Error sending reply:', error);
            });
        }
    });
    // Also add keypress event for the input field (to send on Enter key)
    document.querySelector('.story-reply input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.querySelector('.send-reply').click();
        }
    });

    // Also update the outside click handler to stop video
    document.querySelector('.story-viewer-modal').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) {
            if (window.storyTimeout) {
                clearTimeout(window.storyTimeout);
            }
            
            // Stop video playback if present
            const videoElement = document.querySelector('.story-media video');
            if (videoElement) {
                videoElement.pause();
                videoElement.currentTime = 0;
                videoElement.src = ""; // Clear the source
            }
            
            e.currentTarget.classList.remove('active');
        }
    });

    // Remove the duplicate navigateUserStory function
    // Fetch stories
    fetchStories();

    async function fetchStories() {
        try {
            const response = await fetch('includes/stories/get_stories.php');
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server response:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const stories = await response.json();
            displayStories(stories);
        } catch (error) {
            console.error('Error fetching stories:', error);
            // Handle the error gracefully
            const storiesContainer = document.querySelector('.stories-wrapper');
            if (storiesContainer) {
                storiesContainer.innerHTML = `
                    <div class="story-item">
                        <a href="create_story.php" class="story-link">
                            <div class="story-avatar">
                                <div class="create-story-icon">
                                    <i class="fas fa-plus"></i>
                                </div>
                            </div>
                            <span>Create Story</span>
                        </a>
                    </div>
                `;
            }
        }
    }


});

