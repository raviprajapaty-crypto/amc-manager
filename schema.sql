-- =========================================================
-- AMC Management System - Database Schema
-- Import this file in phpMyAdmin (or via mysql CLI) first
-- =========================================================

CREATE DATABASE IF NOT EXISTS amc_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE amc_system;

-- ---------------------------------------------------------
-- Users (Admin + Technician login)
-- ---------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mobile VARCHAR(20) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','technician') NOT NULL DEFAULT 'technician',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- NOTE: Admin & Technician login users are NOT inserted here because
-- passwords must be securely hashed by PHP (password_hash), not typed
-- directly into SQL. After importing this schema, open install/seed.php
-- once in your browser to create the default Admin + Technician logins.
-- Then DELETE the install/ folder for security.

-- ---------------------------------------------------------
-- Customers
-- ---------------------------------------------------------
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    company_name VARCHAR(150) DEFAULT NULL,
    mobile VARCHAR(20) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    address TEXT,
    total_cameras INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- AMC Contracts
-- ---------------------------------------------------------
CREATE TABLE amc_contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_visits INT NOT NULL DEFAULT 4,
    used_visits INT NOT NULL DEFAULT 0,
    technician_id INT DEFAULT NULL,
    amc_amount DECIMAL(10,2) DEFAULT 0,
    status ENUM('active','expiring','expired') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Visits (Technician site visit reports)
-- ---------------------------------------------------------
CREATE TABLE visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amc_id INT NOT NULL,
    technician_id INT NOT NULL,
    visit_date DATE NOT NULL,
    report_text TEXT,
    issue_resolved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (amc_id) REFERENCES amc_contracts(id) ON DELETE CASCADE,
    FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Visit Photos
-- ---------------------------------------------------------
CREATE TABLE visit_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Invoices
-- ---------------------------------------------------------
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    amc_id INT DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL,
    invoice_date DATE NOT NULL,
    notes TEXT,
    status ENUM('paid','unpaid') NOT NULL DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (amc_id) REFERENCES amc_contracts(id) ON DELETE SET NULL
) ENGINE=InnoDB;

