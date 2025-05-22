document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.querySelector('.file-input');
    const previewContainer = document.querySelector('.preview-container');
    const uploadPlaceholder = document.querySelector('.upload-placeholder');
    const uploadArea = document.querySelector('.upload-area');
    const form = document.querySelector('form');
    const maxImageSize = 4 * 1024 * 1024; // 4MB
    const maxVideoSize = 50 * 1024 * 1024; // 50MB
    const mediaTypeInput = document.getElementById('mediaType');

    // Toggle buttons
    const toggleBtns = document.querySelectorAll('.toggle-btn');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            toggleBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const mediaType = btn.getAttribute('data-type');
            mediaTypeInput.value = mediaType;
            
            if (mediaType === 'video') {
                fileInput.accept = 'video/*';
            } else if (mediaType === 'image') {
                fileInput.accept = 'image/*';
            } else {
                fileInput.accept = 'image/*,video/*';
            }
            
            document.querySelector('.upload-card h5').textContent = `Share your ${btn.textContent.trim()}`;
        });
    });

    // Prevent click propagation on file input
    fileInput.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Handle click on upload area
    uploadArea.addEventListener('click', function(e) {
        if (e.target !== fileInput && !e.target.closest('.media-preview')) {
            fileInput.click();
        }
    });

    // Handle file input change
    fileInput.addEventListener('change', function(e) {
        const files = Array.from(this.files);
        if (files.length > 0) {
            uploadPlaceholder.style.display = 'none';
            showPreviews(files);
        }
    });

    function showPreviews(files) {
        previewContainer.innerHTML = '';
        
        files.forEach(file => {
            const preview = document.createElement('div');
            preview.className = 'media-preview';
            
            if (file.type.startsWith('image/')) {
                if (file.size > maxImageSize) {
                    alert(`Image ${file.name} is too large. Maximum size is 4MB.`);
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <span class="file-name">${file.name}</span>
                        <button type="button" class="remove-preview"><i class="fas fa-times"></i></button>
                    `;
                    previewContainer.appendChild(preview);
                };
                reader.readAsDataURL(file);
            } else if (file.type.startsWith('video/')) {
                if (file.size > maxVideoSize) {
                    alert(`Video ${file.name} is too large. Maximum size is 50MB.`);
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <video controls>
                            <source src="${e.target.result}" type="${file.type}">
                        </video>
                        <span class="file-name">${file.name}</span>
                        <button type="button" class="remove-preview"><i class="fas fa-times"></i></button>
                    `;
                    previewContainer.appendChild(preview);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Handle remove preview
    previewContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-preview')) {
            e.preventDefault();
            e.stopPropagation();
            const preview = e.target.closest('.media-preview');
            preview.remove();
            
            if (previewContainer.children.length === 0) {
                uploadPlaceholder.style.display = 'block';
                fileInput.value = '';
            }
        }
    });

    // Handle drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const files = Array.from(e.dataTransfer.files);
        if (files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            uploadPlaceholder.style.display = 'none';
            showPreviews(files);
        }
    });

    // Form validation
    form.addEventListener('submit', function(e) {
        if (!fileInput.files.length) {
            e.preventDefault();
            alert('Please select at least one file to upload.');
        }
    });
});