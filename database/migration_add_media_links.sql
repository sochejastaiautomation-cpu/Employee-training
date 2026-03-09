-- SQL Migration: Add Photo and Video Links to Products Table
-- Created: 2026-03-09

-- Add new columns to products table
ALTER TABLE `products`
ADD COLUMN `photo_link` LONGTEXT
COMMENT 'JSON array of photo URLs - one URL per line'
AFTER `faqs`;

ALTER TABLE `products`
ADD COLUMN `video_link` LONGTEXT
COMMENT 'JSON array of video URLs - one URL per line'
AFTER `photo_link`;

-- Optional: If you need to create the products table from scratch, use this:
-- CREATE TABLE IF NOT EXISTS `products` (
--   `product_id` INT AUTO_INCREMENT PRIMARY KEY,
--   `product_name` VARCHAR(255) NOT NULL,
--   `product_type` VARCHAR(100),
--   `brand` VARCHAR(100),
--   `material` VARCHAR(100),
--   `price` DECIMAL(10, 2) NOT NULL,
--   `general_info` LONGTEXT,
--   `variants` JSON,
--   `features` JSON,
--   `faqs` JSON,
--   `photo_link` LONGTEXT COMMENT 'JSON array of photo URLs',
--   `video_link` LONGTEXT COMMENT 'JSON array of video URLs',
--   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--   `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
