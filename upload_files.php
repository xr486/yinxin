<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$uploadDir = 'uploads/'; // 上传目录
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$urls = [];
foreach ($_FILES as $key => $file) {
    if (isset($file['tmp_name']) && is_array($file['tmp_name'])) {
        foreach ($file['tmp_name'] as $index => $tmpName) {
            if ($tmpName && $file['name'][$index]) {
                $originalName = $file['name'][$index];
                $uniqueName = uniqid() . '_' . basename($originalName);
                $targetPath = $uploadDir . $uniqueName;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    $urls[] = '/uploads/' . $uniqueName;
                }
            }
        }
    }
}

echo json_encode([
    'success' => true,
    'urls' => $urls
]);
?>