-- Database: portfolio_db
CREATE DATABASE IF NOT EXISTS portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio_db;

-- Admin users
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Projects
CREATE TABLE IF NOT EXISTS projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  short_description TEXT,
  description LONGTEXT,
  image VARCHAR(255),
  demo_video_url VARCHAR(255),
  url VARCHAR(255),
  tech VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- About profile (single row)
CREATE TABLE IF NOT EXISTS about_profiles (
  id TINYINT UNSIGNED PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  intro TEXT,
  body LONGTEXT NOT NULL,
  signature VARCHAR(255),
  profile_image VARCHAR(255),
  github_url VARCHAR(255),
  linkedin_url VARCHAR(255),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Contact messages
CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255),
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE USER IF NOT EXISTS 'portfolio_app'@'localhost'
  IDENTIFIED BY '';           -- empty password
GRANT ALL PRIVILEGES ON portfolio_db.* TO 'portfolio_app'@'localhost';
FLUSH PRIVILEGES;
EXIT;
