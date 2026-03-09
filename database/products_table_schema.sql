-- Complete Products Table Schema with Photo and Video Links

CREATE TABLE IF NOT EXISTS `products` (
  `product_id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_name` VARCHAR(255) NOT NULL,
  `product_type` VARCHAR(100),
  `brand` VARCHAR(100),
  `material` VARCHAR(100),
  `price` DECIMAL(10, 2) NOT NULL,
  `general_info` LONGTEXT,
  `variants` JSON,
  `features` JSON,
  `faqs` JSON,
  `photo_link` LONGTEXT COMMENT 'JSON array of photo URLs - one URL per line',
  `video_link` LONGTEXT COMMENT 'JSON array of video URLs - one URL per line',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_product_name (product_name),
  INDEX idx_brand (brand),
  INDEX idx_product_type (product_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
