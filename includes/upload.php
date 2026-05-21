<?php
/**
 * NOXARA - File Upload Handler
 */

function handle_upload(array $file, string $folder, array $allowed_types = [], int $max_size = 0): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload gagal. Error code: ' . $file['error']];
    }
    
    if (empty($allowed_types)) $allowed_types = ALLOWED_IMAGE_TYPES;
    if ($max_size <= 0) $max_size = MAX_AVATAR_SIZE;
    
    // Check size
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File terlalu besar. Maksimal ' . round($max_size / 1024 / 1024, 1) . 'MB.'];
    }
    
    // Check MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, $allowed_types)) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan.'];
    }
    
    // Check extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed_ext)) {
        return ['success' => false, 'message' => 'Ekstensi file tidak diizinkan.'];
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $upload_dir = UPLOADS_PATH . '/' . $folder;
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filepath = $upload_dir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'path' => '/uploads/' . $folder . '/' . $filename];
    }
    
    return ['success' => false, 'message' => 'Gagal menyimpan file.'];
}
