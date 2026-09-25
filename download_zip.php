<?php
require_once __DIR__ . '/config.php';

$code = trim($_GET['code'] ?? '');
if (!preg_match('/^\d{4}$/', $code)) {
    http_response_code(400);
    exit('Invalid code.');
}

if (!class_exists('ZipArchive')) {
    http_response_code(500);
    exit('The server\'s PHP does not have the zip extension enabled. Ask your host to enable php-zip.');
}

$pdo = get_db();
$stmt = $pdo->prepare('SELECT filename, file_path FROM skin_files WHERE code = ? ORDER BY id');
$stmt->execute([$code]);
$files = $stmt->fetchAll();

if (!$files) {
    http_response_code(404);
    exit('Not found.');
}

$tmpZip = tempnam(sys_get_temp_dir(), 'skinzip');
$zip = new ZipArchive();
$zip->open($tmpZip, ZipArchive::OVERWRITE);

$usedNames = [];
foreach ($files as $f) {
    if (!is_file($f['file_path'])) continue;
    $entryName = basename($f['filename']);
    // avoid collisions if two files share the same name
    if (isset($usedNames[$entryName])) {
        $usedNames[$entryName]++;
        $info = pathinfo($entryName);
        $entryName = $info['filename'] . '_' . $usedNames[$entryName] . (isset($info['extension']) ? '.' . $info['extension'] : '');
    } else {
        $usedNames[$entryName] = 0;
    }
    $zip->addFile($f['file_path'], $entryName);
}
$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="skin-files.zip"');
header('Content-Length: ' . filesize($tmpZip));
readfile($tmpZip);
unlink($tmpZip);
