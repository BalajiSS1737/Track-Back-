-- Track Back App Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS trackback_app;
USE trackback_app;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Items table
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('electronics', 'clothing', 'jewelry', 'keys', 'documents', 'bags', 'sports', 'books', 'toys', 'other') NOT NULL,
    location VARCHAR(200) NOT NULL,
    date_lost_found DATE NOT NULL,
    status ENUM('lost', 'found', 'reunited', 'claimed') NOT NULL,
    contact_name VARCHAR(100) NOT NULL,
    contact_email VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Item photos table
CREATE TABLE item_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    photo_path VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
);

-- Active users table (for tracking login sessions)
CREATE TABLE active_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('user', 'admin') NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_active_session (user_id, user_type),
    INDEX idx_user_type (user_type),
    INDEX idx_login_time (login_time)
);

-- Create indexes for better performance
CREATE INDEX idx_items_status ON items(status);
CREATE INDEX idx_items_category ON items(category);
CREATE INDEX idx_items_date ON items(date_lost_found);
CREATE INDEX idx_items_location ON items(location);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_admins_email ON admins(email);
CREATE INDEX idx_admins_status ON admins(status);