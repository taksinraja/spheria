<?php
// Create upload directories if they don't exist
$upload_dirs = [
    __DIR__ . '/../uploads',
    __DIR__ . '/../uploads/profiles',
    __DIR__ . '/../uploads/covers',
    __DIR__ . '/../uploads/posts'
];

foreach ($upload_dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
        echo "Created directory: $dir<br>";
    } else {
        // Make sure permissions are correct
        chmod($dir, 0777);
        echo "Updated permissions for: $dir<br>";
    }
}

echo "Upload directories setup complete!";
?>