-- Neo Med Cardio — admin panel database schema
-- Import this file via phpMyAdmin (or `mysql -u user -p dbname < schema.sql`) on the hosting MySQL database.

CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS submissions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(32) NOT NULL,
  service VARCHAR(64) NOT NULL,
  message TEXT NULL,
  status ENUM('yangi','korib_chiqilgan','boglanilgan') NOT NULL DEFAULT 'yangi',
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (status),
  INDEX idx_service (service),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- After importing, create the first admin account by generating a password hash
-- (run locally: php -r "echo password_hash('YOUR_REAL_PASSWORD', PASSWORD_DEFAULT);")
-- then insert it manually, e.g.:
-- INSERT INTO admins (username, password_hash) VALUES ('admin', '<paste hash here>');
