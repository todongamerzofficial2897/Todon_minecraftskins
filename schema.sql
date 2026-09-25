-- Run this once in phpMyAdmin (or your host's SQL tool) on the
-- database you created for this site.

CREATE TABLE IF NOT EXISTS skins (
  code CHAR(4) PRIMARY KEY,
  name VARCHAR(255) DEFAULT '',
  preview_path VARCHAR(500) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS skin_files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code CHAR(4) NOT NULL,
  filename VARCHAR(255) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  FOREIGN KEY (code) REFERENCES skins(code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
