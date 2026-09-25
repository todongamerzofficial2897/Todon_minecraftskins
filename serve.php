<?php
require_once __DIR__ . '/config.php';

$type = $_GET['type'] ?? '';
$code = trim($_GET['code'] ?? '');

if (!preg_match('/^\d{4}$/', $code)) {
    http_response_code(400);
    exit('Invalid code.');
}

$pdo = get_db();

if ($type === 'preview') {
    $stmt = $pdo->prepare('SELECT preview_path FROM skins WHERE code = ?');
    $stmt->execute([$code]);
    $row = $stmt->fetch();

    if (!$row || !is_file($row['preview_path'])) {
        http_response_code(404);
        exit('Not found.');
    }

    $path = $row['preview_path'];
    $mime = function_exists('mime_content_type') ? (mime_content_type($path) ?: 'application/octet-stream') : 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    header('Cache-Control: public, max-age=86400');
    readfile($path);
    exit;
}

if ($type === 'file') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT filename, file_path FROM skin_files WHERE code = ? AND id = ?');
    $stmt->execute([$code, $id]);
    $row = $stmt->fetch();

    if (!$row || !is_file($row['file_path'])) {
        http_response_code(404);
        exit('Not found.');
    }

    $path = $row['file_path'];
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($row['filename']) . '"');
    header('Content-Length: ' . filesize($path));
    header('Cache-Control: no-cache');
    readfile($path);
    exit;
}

http_response_code(400);
echo 'Invalid request.';
