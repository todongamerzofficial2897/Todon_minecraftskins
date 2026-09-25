<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$code = trim($_GET['code'] ?? '');
if (!preg_match('/^\d{4}$/', $code)) {
    echo json_encode(['error' => 'invalid_code']);
    exit;
}

$pdo = get_db();

$stmt = $pdo->prepare('SELECT name FROM skins WHERE code = ?');
$stmt->execute([$code]);
$skin = $stmt->fetch();

if (!$skin) {
    echo json_encode(['error' => 'not_found']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, filename FROM skin_files WHERE code = ? ORDER BY id');
$stmt->execute([$code]);
$files = $stmt->fetchAll();

echo json_encode([
    'name' => $skin['name'],
    'previewUrl' => 'serve.php?type=preview&code=' . urlencode($code),
    'files' => array_map(function ($f) use ($code) {
        return [
            'name' => $f['filename'],
            'url' => 'serve.php?type=file&code=' . urlencode($code) . '&id=' . (int)$f['id'],
        ];
    }, $files),
    'downloadZipUrl' => count($files) > 1 ? ('download_zip.php?code=' . urlencode($code)) : null,
]);
