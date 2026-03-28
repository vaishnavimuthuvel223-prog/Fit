-- SQL setup script for Fitness app
CREATE DATABASE IF NOT EXISTS fitness_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fitness_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS fitness_data (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  steps INT DEFAULT 0,
  calories INT DEFAULT 0,
  water FLOAT DEFAULT 0,
  date DATE NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS goals ( 
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,           
  steps_goal INT DEFAULT 10000,  
  calories_goal INT DEFAULT 500,
  water_goal FLOAT DEFAULT 2,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- No demo login inserted. Create real user accounts through the signup form. 