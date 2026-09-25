<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$secret = $_POST['secret'] ?? '';
if (!hash_equals(UPLOAD_SECRET, (string)$secret)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden.']);
    exit;
}

$code = trim($_POST['code'] ?? '');
if (!preg_match('/^\d{4}$/', $code)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid code.']);
    exit;
}

$pdo = get_db();

$stmt = $pdo->prepare('SELECT file_path FROM skin_files WHERE code = ?');
$stmt->execute([$code]);
foreach ($stmt->fetchAll() as $row) {
    if (is_file($row['file_path'])) @unlink($row['file_path']);
}

$stmt = $pdo->prepare('SELECT preview_path FROM skins WHERE code = ?');
$stmt->execute([$code]);
$row = $stmt->fetch();
if ($row && is_file($row['preview_path'])) @unlink($row['preview_path']);

$pdo->prepare('DELETE FROM skin_files WHERE code = ?')->execute([$code]);
$pdo->prepare('DELETE FROM skins WHERE code = ?')->execute([$code]);

$dir = UPLOAD_DIR . $code;
if (is_dir($dir)) @rmdir($dir);

echo json_encode(['status' => 'ok']);
