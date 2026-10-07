CREATE DATABASE studenthub;

USE studenthub;

CREATE TABLE profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    enrollment VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    branch VARCHAR(100) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    dob DATE NOT NULL,
    address TEXT NOT NULL
);