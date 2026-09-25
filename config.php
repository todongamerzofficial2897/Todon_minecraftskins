<?php
// ============================================================
//  EDIT THESE VALUES FOR YOUR HOSTING BEFORE UPLOADING
// ============================================================

// Your MySQL database details (create the DB + user in your host's
// control panel, e.g. Hostinger hPanel / cPanel "MySQL Databases").
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');

// The 4-digit-or-whatever secret code required to upload/manage skins.
// Change this to your own secret before going live.
define('UPLOAD_SECRET', '2308');

// Where uploaded files are stored on the server. Keep this folder
// writable (chmod 755 or 775) and DO NOT remove uploads/.htaccess.
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// ============================================================
//  You shouldn't need to edit anything below this line.
// ============================================================

function get_db(){
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    return $pdo;
}

function safe_filename($name){
    $name = basename($name);
    $name = preg_replace('/[^A-Za-z0-9_.\-]/', '_', $name);
    return ($name === '' || $name === '.' || $name === '..') ? 'file' : $name;
}
