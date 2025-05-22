/**
 * Video Player functionality for Spheria social media platform
 * Handles video playback, controls, and autoplay behavior in the feed
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all video containers
    const videoContainers = document.querySelectorAll('.video-container');
    
    if (videoContainers.length > 0) {
        initializeVideoPlayers(videoContainers);
    }
    
    /**
     * Initializes video players with controls and behavior
     * @param {NodeList} containers - The video container elements
     */
    function initializeVideoPlayers(containers) {
        containers.forEach(container => {
            const video = container.querySelector('video');
            const playPauseBtn = container.querySelector('.play-pause-btn');
            const volumeBtn = container.querySelector('.volume-btn');
            
            if (!video || !playPauseBtn || !volumeBtn) return;
            
            // Initialize video state
            video.volume = 1;
            container.classList.add('paused');
            
            // Play/Pause functionality
            playPauseBtn.addEventListener('click', () => {
                togglePlayPause(video, container);
            });
            
            // Click on video to play/pause
            video.addEventListener('click', () => {
                togglePlayPause(video, container);
            });
            
            // Volume control
            volumeBtn.addEventListener('click', () => {
                toggleMute(video, container);
            });
            
            // Update UI when video ends
            video.addEventListener('ended', () => {
                container.classList.remove('playing');
                container.classList.add('paused');
            });
            
            // Show controls on hover
            container.addEventListener('mouseenter', () => {
                container.classList.add('show-controls');
            });
            
            container.addEventListener('mouseleave', () => {
                container.classList.remove('show-controls');
            });
            
            // Set up intersection observer for autoplay/pause
            setupIntersectionObserver(video, container);
        });
    }
    
    /**
     * Toggles video play/pause state
     * @param {HTMLVideoElement} video - The video element
     * @param {HTMLElement} container - The container element
     */
    function togglePlayPause(video, container) {
        // Pause all other videos first
        pauseAllVideosExcept(video);
        
        if (video.paused) {
            video.play()
                .then(() => {
                    container.classList.remove('paused');
                    container.classList.add('playing');
                })
                .catch(error => {
                    console.error('Error playing video:', error);
                });
        } else {
            video.pause();
            container.classList.remove('playing');
            container.classList.add('paused');
        }
    }
    
    /**
     * Toggles video mute state
     * @param {HTMLVideoElement} video - The video element
     * @param {HTMLElement} container - The container element
     */
    function toggleMute(video, container) {
        if (video.volume === 0) {
            video.volume = 1;
            container.classList.remove('muted');
        } else {
            video.volume = 0;
            container.classList.add('muted');
        }
    }
    
    /**
     * Pauses all videos except the current one
     * @param {HTMLVideoElement} currentVideo - The current video element
     */
    function pauseAllVideosExcept(currentVideo) {
        const allVideos = document.querySelectorAll('.feed-video');
        
        allVideos.forEach(video => {
            if (video !== currentVideo && !video.paused) {
                video.pause();
                const container = video.closest('.video-container');
                if (container) {
                    container.classList.remove('playing');
                    container.classList.add('paused');
                }
            }
        });
    }
    
    /**
     * Sets up intersection observer for autoplay/pause
     * @param {HTMLVideoElement} video - The video element
     * @param {HTMLElement} container - The container element
     */
    function setupIntersectionObserver(video, container) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    // If video is not in viewport and is playing, pause it
                    if (!entry.isIntersecting && !video.paused) {
                        video.pause();
                        container.classList.remove('playing');
                        container.classList.add('paused');
                    }
                    
                    // Optional: Auto-play when in viewport (uncomment if needed)
                    // Browsers may block this due to autoplay policies
                    /*
                    if (entry.isIntersecting && video.paused) {
                        video.play().catch(e => console.log('Autoplay prevented:', e));
                        container.classList.remove('paused');
                        container.classList.add('playing');
                    }
                    */
                });
            },
            { threshold: 0.5 } // Trigger when 50% of the video is visible
        );
        
        observer.observe(container);
    }
    
    // Export functions for use in other scripts
    window.videoPlayerModule = {
        initializeVideoPlayers,
        togglePlayPause,
        toggleMute,
        pauseAllVideosExcept
    };
});