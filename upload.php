<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

function fail($msg, $httpCode = 400){
    http_response_code($httpCode);
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Invalid request method.', 405);
}

// If the request body was bigger than post_max_size, PHP empties
// $_POST/$_FILES but still knows a Content-Length was sent.
if (empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    fail('Those files are too large for this server\'s current upload settings. See README.txt to raise the limits.', 413);
}

$secret = $_POST['secret'] ?? '';
if (!hash_equals(UPLOAD_SECRET, (string)$secret)) {
    fail('Incorrect secret code.', 403);
}

$code = trim($_POST['code'] ?? '');
if (!preg_match('/^\d{4}$/', $code)) {
    fail('Code must be exactly 4 digits.');
}

$name = trim($_POST['name'] ?? '');
$confirm = isset($_POST['confirm']) && $_POST['confirm'] === '1';

if (empty($_FILES['preview']) || $_FILES['preview']['error'] !== UPLOAD_ERR_OK) {
    fail('Please choose a preview image.');
}
if (
    empty($_FILES['downloads']) ||
    !is_array($_FILES['downloads']['name']) ||
    count($_FILES['downloads']['name']) === 0
) {
    fail('Please choose at least one download file.');
}

$pdo = get_db();

$stmt = $pdo->prepare('SELECT name FROM skins WHERE code = ?');
$stmt->execute([$code]);
$existing = $stmt->fetch();

if ($existing && !$confirm) {
    echo json_encode(['status' => 'exists', 'name' => $existing['name']]);
    exit;
}

$codeDir = UPLOAD_DIR . $code . '/';
if (!is_dir($codeDir) && !mkdir($codeDir, 0755, true)) {
    fail('Could not create a folder for this upload on the server.', 500);
}

// If overwriting, remove the old files first.
if ($existing) {
    $stmt = $pdo->prepare('SELECT file_path FROM skin_files WHERE code = ?');
    $stmt->execute([$code]);
    foreach ($stmt->fetchAll() as $row) {
        if (is_file($row['file_path'])) @unlink($row['file_path']);
    }

    $stmt = $pdo->prepare('SELECT preview_path FROM skins WHERE code = ?');
    $stmt->execute([$code]);
    $old = $stmt->fetch();
    if ($old && is_file($old['preview_path'])) @unlink($old['preview_path']);

    $pdo->prepare('DELETE FROM skin_files WHERE code = ?')->execute([$code]);
    $pdo->prepare('DELETE FROM skins WHERE code = ?')->execute([$code]);
}

$previewName = safe_filename($_FILES['preview']['name']);
$previewDest = $codeDir . 'preview_' . $previewName;
if (!move_uploaded_file($_FILES['preview']['tmp_name'], $previewDest)) {
    fail('Failed to save the preview image.', 500);
}

$pdo->prepare('INSERT INTO skins (code, name, preview_path) VALUES (?, ?, ?)')
    ->execute([$code, $name, $previewDest]);

$insertFile = $pdo->prepare('INSERT INTO skin_files (code, filename, file_path) VALUES (?, ?, ?)');

$count = count($_FILES['downloads']['name']);
for ($i = 0; $i < $count; $i++) {
    if ($_FILES['downloads']['error'][$i] !== UPLOAD_ERR_OK) continue;
    $origName = $_FILES['downloads']['name'][$i];
    $dest = $codeDir . $i . '_' . safe_filename($origName);
    if (move_uploaded_file($_FILES['downloads']['tmp_name'][$i], $dest)) {
        $insertFile->execute([$code, $origName, $dest]);
    }
}

echo json_encode(['status' => 'ok']);
