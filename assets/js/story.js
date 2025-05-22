document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.querySelector('.story-upload-area');
    const fileInput = document.querySelector('.file-input');
    const previewContainer = document.querySelector('.preview-container');
    const uploadPlaceholder = document.querySelector('.upload-placeholder');
    
    // Handle file selection
    fileInput.addEventListener('change', function(e) {
        const files = Array.from(this.files);
        if (files.length > 0) {
            uploadPlaceholder.style.display = 'none';
            showPreviews(files);
        } else {
            uploadPlaceholder.style.display = 'block';
            previewContainer.innerHTML = '';
        }
    });

    function showPreviews(files) {
        previewContainer.innerHTML = '';
        files.forEach(file => {
            if (file.type.startsWith('image/') || file.type.startsWith('video/')) {
                const preview = document.createElement('div');
                preview.className = 'preview-item';

                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.onload = () => URL.revokeObjectURL(img.src);
                    preview.appendChild(img);
                } else {
                    const video = document.createElement('video');
                    video.src = URL.createObjectURL(file);
                    video.onloadedmetadata = () => URL.revokeObjectURL(video.src);
                    video.muted = true;
                    
                    // Create play/pause icon container
                    const playPauseIcon = document.createElement('div');
                    playPauseIcon.className = 'play-pause-icon';
                    playPauseIcon.innerHTML = '<i class="fas fa-play"></i>';
                    
                    // Initially show the play icon
                    playPauseIcon.style.display = 'flex';
                    
                    // Add click event to play/pause
                    video.addEventListener('click', function(e) {
                        e.stopPropagation(); // Prevent event bubbling
                        if (video.paused) {
                            video.play();
                            playPauseIcon.innerHTML = '<i class="fas fa-play"></i>';
                            // Show icon briefly then fade out
                            playPauseIcon.style.display = 'flex';
                            playPauseIcon.style.opacity = '1';
                            setTimeout(() => {
                                playPauseIcon.style.opacity = '0';
                                setTimeout(() => {
                                    playPauseIcon.style.display = 'none';
                                    playPauseIcon.style.opacity = '1';
                                }, 300);
                            }, 700);
                        } else {
                            video.pause();
                            playPauseIcon.innerHTML = '<i class="fas fa-pause"></i>';
                            // Show icon
                            playPauseIcon.style.display = 'flex';
                            playPauseIcon.style.opacity = '1';
                        }
                    });
                    
                    // Also show/hide icon on hover
                    videoWrapper.addEventListener('mouseenter', function() {
                        if (!video.paused) {
                            playPauseIcon.style.display = 'flex';
                            playPauseIcon.style.opacity = '1';
                        }
                    });
                    
                    videoWrapper.addEventListener('mouseleave', function() {
                        if (!video.paused) {
                            playPauseIcon.style.opacity = '0';
                            setTimeout(() => {
                                if (!video.paused) {
                                    playPauseIcon.style.display = 'none';
                                }
                            }, 300);
                        }
                    });
                    
                    // Create a wrapper for the video and icon
                    const videoWrapper = document.createElement('div');
                    videoWrapper.className = 'video-wrapper';
                    videoWrapper.appendChild(video);
                    videoWrapper.appendChild(playPauseIcon);
                    
                    preview.appendChild(videoWrapper);
                    
                    // Add CSS styles for the play/pause icon
                    if (!document.getElementById('video-controls-style')) {
                        const style = document.createElement('style');
                        style.id = 'video-controls-style';
                        style.textContent = `
                            .video-wrapper {
                                position: relative;
                                width: 100%;
                                height: 100%;
                            }
                            .video-wrapper video {
                                width: 100%;
                                height: 100%;
                                object-fit: cover;
                            }
                            .play-pause-icon {
                                position: absolute;
                                top: 50%;
                                left: 50%;
                                transform: translate(-50%, -50%);
                                background-color: rgba(0, 0, 0, 0.5);
                                color: white;
                                border-radius: 50%;
                                width: 50px;
                                height: 50px;
                                display: flex;
                                justify-content: center;
                                align-items: center;
                                font-size: 20px;
                                transition: opacity 0.3s ease;
                                z-index: 10;
                            }
                        `;
                        document.head.appendChild(style);
                    }
                }

                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-preview';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.onclick = (e) => {
                    e.stopPropagation();
                    preview.remove();
                    if (previewContainer.children.length === 0) {
                        fileInput.value = '';
                        uploadPlaceholder.style.display = 'block';
                    }
                };

                preview.appendChild(removeBtn);
                previewContainer.appendChild(preview);
            }
        });
    }

    // Keep existing drag and drop handlers
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        const files = Array.from(e.dataTransfer.files);
        if (files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            uploadPlaceholder.style.display = 'none';
            showPreviews(files);
        }
    });
});