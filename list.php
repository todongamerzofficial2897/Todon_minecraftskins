<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$secret = $_POST['secret'] ?? ($_GET['secret'] ?? '');
if (!hash_equals(UPLOAD_SECRET, (string)$secret)) {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

$pdo = get_db();
$stmt = $pdo->query(
    'SELECT s.code, s.name, COUNT(f.id) AS file_count
     FROM skins s
     LEFT JOIN skin_files f ON f.code = s.code
     GROUP BY s.code, s.name
     ORDER BY s.code'
);

echo json_encode($stmt->fetchAll());
