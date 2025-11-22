-- Tạo database
CREATE DATABASE IF NOT EXISTS BookProject
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE BookProject;

-- Bảng User
CREATE TABLE `User` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `avatar` VARCHAR(255),
    `name` VARCHAR(150),
    `birthday` DATE,
    `report_point` INT,
    `balance` DECIMAL(15, 2) DEFAULT 0,
    `role` ENUM('user', 'admin') DEFAULT 'user',
    `status` ENUM('active', 'banned') DEFAULT 'active'
) ENGINE=InnoDB;

-- Bảng Book
CREATE TABLE `Book` (
    `book_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `avatar` VARCHAR(255) ,
    `author` VARCHAR(150),
    `verified` ENUM('pending', 'verified') DEFAULT 'pending',
    `language` VARCHAR(50),
    `edition` VARCHAR(50),
    `url` VARCHAR(255),
    `upload_id` INT,
    `price` DECIMAL(15, 2) DEFAULT 0,
    `visits` INT,
    `describe` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_book_user FOREIGN KEY (`upload_id`) REFERENCES `User`(`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Bảng Category
CREATE TABLE `Category` (
    `category_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Bảng BookCategory (N-N giữa Book và Category)
CREATE TABLE `BookCategory` (
    `book_id` INT,
    `category_id` INT,
    PRIMARY KEY (`book_id`, `category_id`),
    CONSTRAINT fk_bc_book FOREIGN KEY (`book_id`) REFERENCES `Book`(`book_id`) ON DELETE CASCADE,
    CONSTRAINT fk_bc_category FOREIGN KEY (`category_id`) REFERENCES `Category`(`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng FavoriteBooks
CREATE TABLE `FavoriteBooks` (
    `user_id` INT,
    `book_id` INT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `book_id`),
    CONSTRAINT fk_fb_user FOREIGN KEY (`user_id`) REFERENCES `User`(`user_id`) ON DELETE CASCADE,
    CONSTRAINT fk_fb_book FOREIGN KEY (`book_id`) REFERENCES `Book`(`book_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng Transaction
CREATE TABLE `Transaction` (
    `transaction_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `book_id` INT,
    `amount` DECIMAL(15,2) NOT NULL,
    `status` ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tr_user FOREIGN KEY (`user_id`) REFERENCES `User`(`user_id`) ON DELETE CASCADE,
    CONSTRAINT fk_tr_book FOREIGN KEY (`book_id`) REFERENCES `Book`(`book_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng Comment
CREATE TABLE `Comment` (
    `comment_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `book_id` INT,
    `content` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cmt_user FOREIGN KEY (`user_id`) REFERENCES `User`(`user_id`) ON DELETE CASCADE,
    CONSTRAINT fk_cmt_book FOREIGN KEY (`book_id`) REFERENCES `Book`(`book_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng Rating
CREATE TABLE `Rating` (
    `user_id` INT,
    `book_id` INT,
    `score` INT CHECK (`score` BETWEEN 1 AND 5),
    PRIMARY KEY (`user_id`, `book_id`),
    CONSTRAINT fk_rate_user FOREIGN KEY (`user_id`) REFERENCES `User`(`user_id`) ON DELETE CASCADE,
    CONSTRAINT fk_rate_book FOREIGN KEY (`book_id`) REFERENCES `Book`(`book_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng Report
CREATE TABLE `Report` (
    `report_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `book_id` INT,
    `reason` VARCHAR(255),
    `status` ENUM('pending', 'resolved') DEFAULT 'pending',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_report_user FOREIGN KEY (`user_id`) REFERENCES `User`(`user_id`) ON DELETE CASCADE,
    CONSTRAINT fk_report_book FOREIGN KEY (`book_id`) REFERENCES `Book`(`book_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng Follow
CREATE TABLE `Follow` (
    `follower_id` INT,
    `following_id` INT,
    PRIMARY KEY (`follower_id`, `following_id`),
    CONSTRAINT fk_follow_follower FOREIGN KEY (`follower_id`) REFERENCES `User`(`user_id`) ON DELETE CASCADE,
    CONSTRAINT fk_follow_following FOREIGN KEY (`following_id`) REFERENCES `User`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;
