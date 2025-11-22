<?php
require_once 'auth.php';
requireLogin();

/**
 * Secure File Upload Handler
 * Handles uploads for: carousel images, size charts, fabric images, shop logo
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Configuration
$uploadConfig = [
    'max_size' => 5 * 1024 * 1024, // 5MB
    'allowed_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'],
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
    'upload_dirs' => [
        'carousel' => '../uploads/images/',
        'size_chart' => '../uploads/size-charts/',
        'fabric' => '../uploads/fabrics/',
        'logo' => '../uploads/logos/'
    ]
];

try {
    // Get upload type
    $uploadType = $_POST['upload_type'] ?? '';
    
    if (!isset($uploadConfig['upload_dirs'][$uploadType])) {
        throw new Exception('Invalid upload type');
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error');
    }
    
    $file = $_FILES['file'];
    
    // Validate file size
    if ($file['size'] > $uploadConfig['max_size']) {
        throw new Exception('File size exceeds 5MB limit');
    }
    
    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $uploadConfig['allowed_types'])) {
        throw new Exception('Invalid file type. Only JPG, PNG, and WebP allowed');
    }
    
    // Validate file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $uploadConfig['allowed_extensions'])) {
        throw new Exception('Invalid file extension');
    }
    
    // Generate secure filename
    $timestamp = time();
    $randomStr = bin2hex(random_bytes(8));
    $newFilename = $uploadType . '_' . $timestamp . '_' . $randomStr . '.' . $extension;
    
    // Create upload directory if it doesn't exist
    $uploadDir = $uploadConfig['upload_dirs'][$uploadType];
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Move uploaded file
    $destination = $uploadDir . $newFilename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Failed to save file');
    }
    
    // Return relative path (without ../)
    $relativePath = 'uploads/' . ($uploadType === 'carousel' ? 'images/' : 
                                   ($uploadType === 'size_chart' ? 'size-charts/' :
                                   ($uploadType === 'fabric' ? 'fabrics/' : 'logos/'))) . $newFilename;
    
    echo json_encode([
        'success' => true,
        'filename' => $newFilename,
        'path' => $relativePath,
        'url' => '/' . $relativePath
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
