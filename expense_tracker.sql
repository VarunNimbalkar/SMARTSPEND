CREATE DATABASE IF NOT EXISTS expense_tracker;

USE expensee_tracker;


-- =========================================
-- USERS TABLE
-- =========================================

CREATE TABLE users (

    id INT AUTO_INCREMENT PRIMARY KEY,

    full_name VARCHAR(100) NOT NULL,

    username VARCHAR(50) NOT NULL UNIQUE,

    email VARCHAR(150) NOT NULL UNIQUE,

    mobile VARCHAR(20) NOT NULL UNIQUE,

    password VARCHAR(255) NOT NULL,

    email_verified TINYINT(1) DEFAULT 0,

    phone_verified TINYINT(1) DEFAULT 0,

    email_token VARCHAR(100) DEFAULT NULL,

    role ENUM('User', 'Admin') DEFAULT 'User',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);


-- =========================================
-- CATEGORIES TABLE
-- =========================================

CREATE TABLE categories (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    name VARCHAR(80) NOT NULL,

    type ENUM('Income', 'Expense') NOT NULL,

    description VARCHAR(255),

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE

);


-- =========================================
-- TRANSACTIONS TABLE
-- =========================================

CREATE TABLE transactions (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    title VARCHAR(150) NOT NULL,

    amount DECIMAL(12,2) NOT NULL,

    category VARCHAR(80) NOT NULL,

    payment_method VARCHAR(40) NOT NULL,

    type ENUM('Income', 'Expense') NOT NULL,

    trans_date DATE NOT NULL,

    description TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE

);


-- =========================================
-- BUDGETS TABLE
-- =========================================

CREATE TABLE budgets (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    budget_name VARCHAR(100) NOT NULL,

    category VARCHAR(80) NOT NULL,

    amount DECIMAL(12,2) NOT NULL,

    start_date DATE,

    end_date DATE,

    alert_limit INT DEFAULT 80,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE

);
