-- ==========================================================
-- Discora E-Commerce Complete Database Schema & Seed Data
-- Target System : MySQL / MariaDB (XAMPP & phpMyAdmin)
-- Database Name : discora_db
-- Charset       : utf8mb4
-- Collation     : utf8mb4_unicode_ci
-- Engine        : InnoDB
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ----------------------------------------------------------
-- 1. CREATE DATABASE
-- ----------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `discora_db`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `discora_db`;

-- ----------------------------------------------------------
-- 2. DROP EXISTING TABLES IN REVERSE DEPENDENCY ORDER
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `wishlist_items`;
DROP TABLE IF EXISTS `wishlists`;
DROP TABLE IF EXISTS `cart_items`;
DROP TABLE IF EXISTS `carts`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `platforms`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `addresses`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;

-- ----------------------------------------------------------
-- 3. CREATE TABLES
-- ----------------------------------------------------------

-- Table 1: roles
CREATE TABLE `roles` (
    `role_id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_name` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 2: users
CREATE TABLE `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT NOT NULL DEFAULT 2,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `avatar` VARCHAR(255) DEFAULT 'assets/images/users/default-avatar.png',
    `status` ENUM('Active', 'Inactive', 'Suspended') NOT NULL DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) 
        REFERENCES `roles`(`role_id`) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 3: addresses
CREATE TABLE `addresses` (
    `address_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(50) DEFAULT 'Home',
    `recipient_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `address_line1` VARCHAR(255) NOT NULL,
    `address_line2` VARCHAR(255) NULL,
    `city` VARCHAR(100) NOT NULL,
    `state` VARCHAR(100) NULL,
    `postal_code` VARCHAR(20) NOT NULL,
    `country` VARCHAR(100) DEFAULT 'United States',
    `is_default` TINYINT(1) DEFAULT 0,
    `address_type` ENUM('Shipping', 'Billing', 'Both') DEFAULT 'Both',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_addresses_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users`(`user_id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4: categories
CREATE TABLE `categories` (
    `category_id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `parent_id` INT NULL,
    `description` TEXT NULL,
    `icon` VARCHAR(100) NULL,
    `display_order` INT DEFAULT 0,
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) 
        REFERENCES `categories`(`category_id`) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 5: platforms
CREATE TABLE `platforms` (
    `platform_id` INT AUTO_INCREMENT PRIMARY KEY,
    `platform_name` VARCHAR(100) NOT NULL UNIQUE,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `brand` ENUM('PlayStation', 'Xbox', 'Nintendo', 'Multiplatform') NOT NULL,
    `description` TEXT NULL,
    `icon` VARCHAR(100) NULL,
    `display_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 6: products
CREATE TABLE `products` (
    `product_id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NULL,
    `platform_id` INT NOT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `edition` VARCHAR(100) DEFAULT 'Standard Physical Edition',
    `genre` VARCHAR(100) DEFAULT 'Action / Adventure',
    `description` LONGTEXT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `discount_price` DECIMAL(10, 2) NULL,
    `stock_quantity` INT NOT NULL DEFAULT 0,
    `product_type` ENUM('Physical Disc', 'Console', 'Accessory', 'Bundle') NOT NULL DEFAULT 'Physical Disc',
    `release_date` DATE NULL,
    `publisher` VARCHAR(150) NULL,
    `is_new_arrival` TINYINT(1) DEFAULT 0,
    `is_featured` TINYINT(1) DEFAULT 0,
    `status` ENUM('Active', 'Inactive', 'Out of Stock') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) 
        REFERENCES `categories`(`category_id`) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_products_platform` FOREIGN KEY (`platform_id`) 
        REFERENCES `platforms`(`platform_id`) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 7: product_images
CREATE TABLE `product_images` (
    `image_id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `alt_text` VARCHAR(255) NULL,
    `is_primary` TINYINT(1) DEFAULT 0,
    `display_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) 
        REFERENCES `products`(`product_id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 8: carts
CREATE TABLE `carts` (
    `cart_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `session_token` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_carts_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users`(`user_id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 9: cart_items
CREATE TABLE `cart_items` (
    `cart_item_id` INT AUTO_INCREMENT PRIMARY KEY,
    `cart_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_cart_items_cart` FOREIGN KEY (`cart_id`) 
        REFERENCES `carts`(`cart_id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_cart_items_product` FOREIGN KEY (`product_id`) 
        REFERENCES `products`(`product_id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `uk_cart_product` UNIQUE (`cart_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 10: wishlists
CREATE TABLE `wishlists` (
    `wishlist_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_wishlists_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users`(`user_id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `uk_user_wishlist` UNIQUE (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 11: wishlist_items
CREATE TABLE `wishlist_items` (
    `wishlist_item_id` INT AUTO_INCREMENT PRIMARY KEY,
    `wishlist_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_wishlist_items_wishlist` FOREIGN KEY (`wishlist_id`) 
        REFERENCES `wishlists`(`wishlist_id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_wishlist_items_product` FOREIGN KEY (`product_id`) 
        REFERENCES `products`(`product_id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `uk_wishlist_product` UNIQUE (`wishlist_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 12: orders
CREATE TABLE `orders` (
    `order_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `address_id` INT NULL,
    `order_number` VARCHAR(50) NOT NULL UNIQUE,
    `order_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `subtotal` DECIMAL(10, 2) NOT NULL,
    `discount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `shipping_fee` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `tax_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(10, 2) NOT NULL,
    `order_status` ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Pending',
    `shipping_name` VARCHAR(100) NOT NULL,
    `shipping_phone` VARCHAR(30) NOT NULL,
    `shipping_address` TEXT NOT NULL,
    `shipping_city` VARCHAR(100) NOT NULL,
    `shipping_state` VARCHAR(100) NULL,
    `shipping_postal_code` VARCHAR(20) NOT NULL,
    `shipping_country` VARCHAR(100) DEFAULT 'United States',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users`(`user_id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_orders_address` FOREIGN KEY (`address_id`) 
        REFERENCES `addresses`(`address_id`) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 13: order_items
CREATE TABLE `order_items` (
    `order_item_id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(10, 2) NOT NULL,
    `subtotal` DECIMAL(10, 2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) 
        REFERENCES `orders`(`order_id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) 
        REFERENCES `products`(`product_id`) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 14: payments
CREATE TABLE `payments` (
    `payment_id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `payment_method` ENUM('Cash on Delivery', 'Credit/Debit Card', 'Online Payment', 'PayPal', 'Bank Transfer') NOT NULL DEFAULT 'Cash on Delivery',
    `transaction_reference` VARCHAR(100) NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `payment_status` ENUM('Pending', 'Completed', 'Failed', 'Refunded') NOT NULL DEFAULT 'Pending',
    `payment_date` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) 
        REFERENCES `orders`(`order_id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 15: reviews
CREATE TABLE `reviews` (
    `review_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `rating` TINYINT NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
    `review_text` TEXT NULL,
    `review_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users`(`user_id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) 
        REFERENCES `products`(`product_id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `uk_user_product_review` UNIQUE (`user_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 16: contact_messages
CREATE TABLE `contact_messages` (
    `message_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `subject` VARCHAR(200) NOT NULL,
    `message` LONGTEXT NOT NULL,
    `status` ENUM('New', 'Read', 'Replied', 'Archived') NOT NULL DEFAULT 'New',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_contact_messages_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users`(`user_id`) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 4. CREATE PERFORMANCE INDEXES
-- ----------------------------------------------------------
CREATE INDEX `idx_users_role` ON `users` (`role_id`);
CREATE INDEX `idx_users_email` ON `users` (`email`);
CREATE INDEX `idx_addresses_user` ON `addresses` (`user_id`);
CREATE INDEX `idx_categories_slug` ON `categories` (`slug`);
CREATE INDEX `idx_categories_parent` ON `categories` (`parent_id`);
CREATE INDEX `idx_platforms_slug` ON `platforms` (`slug`);
CREATE INDEX `idx_platforms_brand` ON `platforms` (`brand`);
CREATE INDEX `idx_products_category` ON `products` (`category_id`);
CREATE INDEX `idx_products_platform` ON `products` (`platform_id`);
CREATE INDEX `idx_products_slug` ON `products` (`slug`);
CREATE INDEX `idx_products_featured` ON `products` (`is_featured`);
CREATE INDEX `idx_products_new` ON `products` (`is_new_arrival`);
CREATE INDEX `idx_products_status` ON `products` (`status`);
CREATE INDEX `idx_product_images_product` ON `product_images` (`product_id`);
CREATE INDEX `idx_carts_user` ON `carts` (`user_id`);
CREATE INDEX `idx_carts_session` ON `carts` (`session_token`);
CREATE INDEX `idx_orders_user` ON `orders` (`user_id`);
CREATE INDEX `idx_orders_status` ON `orders` (`order_status`);
CREATE INDEX `idx_order_items_order` ON `order_items` (`order_id`);
CREATE INDEX `idx_order_items_product` ON `order_items` (`product_id`);
CREATE INDEX `idx_payments_order` ON `payments` (`order_id`);
CREATE INDEX `idx_payments_status` ON `payments` (`payment_status`);
CREATE INDEX `idx_reviews_product` ON `reviews` (`product_id`);
CREATE INDEX `idx_reviews_user` ON `reviews` (`user_id`);
CREATE INDEX `idx_contact_status` ON `contact_messages` (`status`);

-- ----------------------------------------------------------
-- 5. INSERT INITIAL SEED DATA
-- ----------------------------------------------------------

-- 5.1 Roles
INSERT INTO `roles` (`role_id`, `role_name`, `description`) VALUES
(1, 'Admin', 'Full administrative access to manage products, categories, orders, customers, and system settings.'),
(2, 'Customer', 'Standard registered customer with shopping, cart, wishlist, ordering, and review privileges.');

-- 5.2 Users
-- Passwords:
-- Admin:    admin123    -> Hash: $2y$10$UhMzIm1NU2Tna.EFam/IweZa20F7//ZqzkXVyEQHrlNhyFCkucFgG
-- Customer: customer123 -> Hash: $2y$10$9BG6qFbNkQYSSDS7zvHUT.358SaMjdKXsuX1dgvNit4wHIKpwyXH.
INSERT INTO `users` (`user_id`, `role_id`, `full_name`, `username`, `email`, `password`, `phone`, `avatar`, `status`) VALUES
(1, 1, 'Discora Administrator', 'admin', 'admin@discora.com', '$2y$10$UhMzIm1NU2Tna.EFam/IweZa20F7//ZqzkXVyEQHrlNhyFCkucFgG', '+1 (555) 019-2834', 'assets/images/users/admin.png', 'Active'),
(2, 2, 'Alex Rivera', 'alexrivera', 'alex@example.com', '$2y$10$9BG6qFbNkQYSSDS7zvHUT.358SaMjdKXsuX1dgvNit4wHIKpwyXH.', '+1 (555) 345-6789', 'assets/images/users/alex.png', 'Active');

-- 5.3 Addresses
INSERT INTO `addresses` (`address_id`, `user_id`, `title`, `recipient_name`, `phone`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `country`, `is_default`, `address_type`) VALUES
(1, 2, 'Home', 'Alex Rivera', '+1 (555) 345-6789', '742 Evergreen Terrace', 'Apt 4B', 'Seattle', 'WA', '98101', 'United States', 1, 'Both');

-- 5.4 Platforms
INSERT INTO `platforms` (`platform_id`, `platform_name`, `slug`, `brand`, `description`, `icon`, `display_order`) VALUES
(1, 'PS5', 'ps5', 'PlayStation', 'Sony PlayStation 5 next-generation console discs and hardware.', 'fab fa-playstation', 1),
(2, 'PS4', 'ps4', 'PlayStation', 'Sony PlayStation 4 physical Blu-ray discs and editions.', 'fab fa-playstation', 2),
(3, 'Xbox Series X|S', 'xbox-series-xs', 'Xbox', 'Microsoft Xbox Series X and Series S optimized physical discs and hardware.', 'fab fa-xbox', 3),
(4, 'Xbox One', 'xbox-one', 'Xbox', 'Microsoft Xbox One physical discs and editions.', 'fab fa-xbox', 4);

-- 5.5 Categories
INSERT INTO `categories` (`category_id`, `category_name`, `slug`, `parent_id`, `description`, `icon`, `display_order`, `status`) VALUES
(1, 'PlayStation', 'playstation', NULL, 'Sony PlayStation platform games, consoles, and authentic accessories.', 'fab fa-playstation', 1, 'Active'),
(2, 'Xbox', 'xbox', NULL, 'Microsoft Xbox platform games, consoles, and authentic accessories.', 'fab fa-xbox', 2, 'Active'),
(3, 'Consoles', 'consoles', NULL, 'Next-generation and current-generation gaming console hardware bundles.', 'fas fa-tv', 3, 'Active'),
(4, 'Accessories', 'accessories', NULL, 'Official controllers, high-fidelity headsets, and gaming peripherals.', 'fas fa-gamepad', 4, 'Active'),
(5, 'New Arrivals', 'new-arrivals', NULL, 'Hot new video game disc releases and newly arrived gaming gear.', 'fas fa-fire', 5, 'Active'),
(6, 'PS5 Games', 'ps5-games', 1, 'Physical PS5 Blu-ray game discs and collector editions.', 'fab fa-playstation', 1, 'Active'),
(7, 'PS4 Games', 'ps4-games', 1, 'Physical PS4 Blu-ray game discs and editions.', 'fab fa-playstation', 2, 'Active'),
(8, 'Xbox Series X Games', 'xbox-series-x-games', 2, 'Physical Xbox Series X optimized game discs.', 'fab fa-xbox', 3, 'Active'),
(9, 'Xbox One Games', 'xbox-one-games', 2, 'Physical Xbox One game discs and editions.', 'fab fa-xbox', 4, 'Active');

-- 5.6 Sample Products (12 realistic products across categories & platforms)
INSERT INTO `products` (`product_id`, `category_id`, `platform_id`, `product_name`, `slug`, `edition`, `genre`, `description`, `price`, `discount_price`, `stock_quantity`, `product_type`, `release_date`, `publisher`, `is_new_arrival`, `is_featured`, `status`) VALUES
(1, 6, 1, 'Marvel\'s Spider-Man 2', 'marvels-spider-man-2-ps5', 'Standard Physical Edition', 'Action / Adventure', 'Spider-Men Peter Parker and Miles Morales face the ultimate test of strength inside and outside the mask as they fight to save the city, each other, and the ones they love from the monstrous Venom.', 69.99, NULL, 35, 'Physical Disc', '2023-10-20', 'Sony Interactive Entertainment', 1, 1, 'Active'),
(2, 6, 1, 'God of War Ragnarök', 'god-of-war-ragnarok-ps5', 'Standard Physical Edition', 'Action / Adventure', 'Join Kratos and Atreus on a mythic journey for answers before Ragnarök arrives. Together, father and son must put everything on the line as they journey to each of the Nine Realms.', 59.99, 69.99, 28, 'Physical Disc', '2022-11-09', 'Sony Interactive Entertainment', 0, 1, 'Active'),
(3, 6, 1, 'Assassin\'s Creed Mirage', 'assassins-creed-mirage-ps5', 'Launch Physical Edition', 'Action / Stealth', 'Experience the story of Basim, a cunning street thief with nightmarish visions, seeking answers and justice in the bustling streets of 9th-century Baghdad.', 49.99, NULL, 40, 'Physical Disc', '2023-10-05', 'Ubisoft', 1, 1, 'Active'),
(4, 7, 2, 'Ghost of Tsushima', 'ghost-of-tsushima-ps4', 'Standard Edition', 'Open World / Action', 'In the late 13th century, the Mongol empire has laid waste to entire nations. Jin Sakai must forge a new path, the path of the Ghost, to wage an unconventional war for Tsushima.', 39.99, 59.99, 18, 'Physical Disc', '2020-07-17', 'Sony Interactive Entertainment', 0, 0, 'Active'),
(5, 7, 2, 'The Last of Us Remastered', 'the-last-of-us-remastered-ps4', 'Hits Physical Edition', 'Action / Survival Horror', 'Winner of over 200 Game of the Year awards. Abandoned cities reclaimed by nature. A population decimated by a modern plague. Survivors are killing each other for food and weapons.', 19.99, 29.99, 22, 'Physical Disc', '2014-07-29', 'Sony Interactive Entertainment', 0, 1, 'Active'),
(6, 8, 3, 'Forza Motorsport', 'forza-motorsport-xbox-series-x', 'Standard Physical Edition', 'Racing / Simulation', 'Out-build the competition using over 800 performance upgrades in the all-new, fun, and rewarding single-player career mode. Race alongside friends in adjudicated multiplayer events.', 69.99, NULL, 30, 'Physical Disc', '2023-10-10', 'Xbox Game Studios', 1, 1, 'Active'),
(7, 8, 3, 'Halo Infinite', 'halo-infinite-xbox', 'Steelbook Physical Edition', 'First-Person Shooter', 'When all hope is lost and humanity\'s fate hangs in the balance, the Master Chief is ready to confront the most ruthless foe he has ever faced on the vast ringworld of Zeta Halo.', 39.99, 59.99, 25, 'Physical Disc', '2021-12-08', 'Xbox Game Studios', 0, 1, 'Active'),
(8, 8, 3, 'Starfield', 'starfield-xbox-series-x', 'Standard Physical Edition', 'RPG / Sci-Fi', 'In this next-generation role-playing game set amongst the stars, create any character you want and explore with unparalleled freedom as you embark on an epic journey to answer humanity\'s greatest mystery.', 69.99, NULL, 32, 'Physical Disc', '2023-09-06', 'Bethesda Softworks', 1, 0, 'Active'),
(9, 9, 4, 'Gears 5', 'gears-5-xbox-one', 'Standard Physical Edition', 'Third-Person Shooter', 'With all-out war descending, Kait Diaz breaks away to uncover her connection to the enemy and discovers the true danger to Sera – herself.', 29.99, 39.99, 15, 'Physical Disc', '2019-09-10', 'Xbox Game Studios', 0, 0, 'Active'),
(10, 3, 1, 'PlayStation 5 Console (Disc Edition)', 'ps5-console-disc-edition', 'Hardware Console Bundle', 'Gaming Hardware', 'Experience lightning-fast loading with an ultra-high speed SSD, deeper immersion with support for haptic feedback, adaptive triggers, 3D Audio, and an all-new generation of incredible PlayStation games.', 499.99, NULL, 10, 'Console', '2020-11-12', 'Sony Interactive Entertainment', 1, 1, 'Active'),
(11, 3, 3, 'Xbox Series X 1TB Console', 'xbox-series-x-1tb-console', 'Hardware Console Bundle', 'Gaming Hardware', 'The fastest, most powerful Xbox ever. Explore rich new worlds with 12 teraflops of raw graphic processing power, DirectX ray tracing, a custom SSD, and 4K gaming.', 499.99, NULL, 12, 'Console', '2020-11-10', 'Microsoft', 1, 1, 'Active'),
(12, 4, 1, 'DualSense Wireless Controller - Midnight Black', 'dualsense-wireless-controller-black', 'Official Hardware Accessory', 'Peripherals', 'Discover a deeper, highly immersive gaming experience that brings the action to life in the palms of your hands. The DualSense wireless controller offers immersive haptic feedback and dynamic triggers.', 74.99, NULL, 45, 'Accessory', '2021-06-18', 'Sony Interactive Entertainment', 1, 0, 'Active'),
(13, 4, 3, 'Xbox Wireless Controller - Robot White', 'xbox-wireless-controller-white', 'Official Hardware Accessory', 'Peripherals', 'Experience the modernized design of the Xbox Wireless Controller, featuring sculpted surfaces and refined geometry for enhanced comfort during gameplay with textured grip and hybrid D-pad.', 59.99, NULL, 50, 'Accessory', '2020-11-10', 'Microsoft', 1, 0, 'Active');

-- 5.7 Product Images
INSERT INTO `product_images` (`image_id`, `product_id`, `image_path`, `alt_text`, `is_primary`, `display_order`) VALUES
(1, 1, 'assets/images/products/spiderman2.jpg', 'Marvel\'s Spider-Man 2 PS5 Box Art', 1, 1),
(2, 2, 'assets/images/products/gow-ragnarok.png', 'God of War Ragnarök PS5 Cover', 1, 1),
(3, 3, 'assets/images/products/ac-mirage.png', 'Assassin\'s Creed Mirage PS5 Cover', 1, 1),
(4, 4, 'assets/images/products/gow-ragnarok.png', 'Ghost of Tsushima PS4 Cover', 1, 1),
(5, 5, 'assets/images/products/spiderman2.jpg', 'The Last of Us Remastered PS4 Box Art', 1, 1),
(6, 6, 'assets/images/products/forza-motorsport.png', 'Forza Motorsport Xbox Series X Cover', 1, 1),
(7, 7, 'assets/images/products/halo-infinite.png', 'Halo Infinite Xbox Cover', 1, 1),
(8, 8, 'assets/images/products/forza-motorsport.png', 'Starfield Xbox Series X Cover', 1, 1),
(9, 9, 'assets/images/products/halo-infinite.png', 'Gears 5 Xbox One Cover', 1, 1),
(10, 10, 'assets/images/products/ps5-console-bundle.png', 'PlayStation 5 Console Disc Edition Bundle', 1, 1),
(11, 11, 'assets/images/products/xbox-series-bundle.png', 'Xbox Series X 1TB Console Package', 1, 1),
(12, 12, 'assets/images/products/dualsense-black.jpg', 'DualSense Wireless Controller Midnight Black', 1, 1),
(13, 13, 'assets/images/products/xbox-controller-white.jpg', 'Xbox Wireless Controller Robot White', 1, 1);

-- 5.8 Sample Active Cart & Cart Items (For Alex Rivera)
INSERT INTO `carts` (`cart_id`, `user_id`, `session_token`) VALUES
(1, 2, 'sess_alex_test_token_8891');

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `product_id`, `quantity`) VALUES
(1, 1, 1, 1), -- Marvel's Spider-Man 2 (PS5)
(2, 1, 12, 1); -- DualSense Wireless Controller Midnight Black

-- 5.9 Sample Wishlist & Items (For Alex Rivera)
INSERT INTO `wishlists` (`wishlist_id`, `user_id`) VALUES
(1, 2);

INSERT INTO `wishlist_items` (`wishlist_item_id`, `wishlist_id`, `product_id`) VALUES
(1, 1, 2), -- God of War Ragnarök
(2, 1, 6); -- Forza Motorsport

-- 5.10 Sample Historical Order & Order Items
INSERT INTO `orders` (`order_id`, `user_id`, `address_id`, `order_number`, `order_date`, `subtotal`, `discount`, `shipping_fee`, `tax_amount`, `total_amount`, `order_status`, `shipping_name`, `shipping_phone`, `shipping_address`, `shipping_city`, `shipping_state`, `shipping_postal_code`, `shipping_country`, `notes`) VALUES
(1, 2, 1, 'ORD-2026-1001', '2026-08-10 14:32:00', 109.98, 0.00, 0.00, 8.80, 118.78, 'Delivered', 'Alex Rivera', '+1 (555) 345-6789', '742 Evergreen Terrace, Apt 4B', 'Seattle', 'WA', '98101', 'United States', 'Please leave package at the front porch.');

-- Order items store frozen purchase unit price at moment of sale
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 2, 1, 59.99, 59.99), -- God of War Ragnarök
(2, 1, 3, 1, 49.99, 49.99); -- Assassin's Creed Mirage

-- 5.11 Sample Order Payment
INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `transaction_reference`, `amount`, `payment_status`, `payment_date`) VALUES
(1, 1, 'Credit/Debit Card', 'TXN-DISCORA-9920148', 118.78, 'Completed', '2026-08-10 14:35:12');

-- 5.12 Sample Product Reviews
INSERT INTO `reviews` (`review_id`, `user_id`, `product_id`, `rating`, `review_text`, `review_status`, `created_at`) VALUES
(1, 2, 1, 5, 'Absolute masterpiece! The web-swinging speed and dual-character transitions on PS5 are breathtaking.', 'Approved', '2026-08-12 10:15:00'),
(2, 2, 2, 5, 'Phenomenal story, emotional character arcs, and deep combat mechanics. A must-play for every PS5 owner.', 'Approved', '2026-08-14 16:45:30');

-- 5.13 Sample Contact Messages
INSERT INTO `contact_messages` (`message_id`, `user_id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 2, 'Alex Rivera', 'alex@example.com', '+1 (555) 345-6789', 'Question regarding pre-orders', 'Hello Discora team, will you be stocking the upcoming Collector Editions for next month releases?', 'Replied', '2026-08-15 09:30:00'),
(2, NULL, 'Marcus Vance', 'marcus.vance@gmail.com', '+1 (555) 789-0123', 'Inquiry about Xbox Series X Trade-in', 'Do you offer physical disc trade-in credit toward console purchases?', 'New', '2026-08-18 11:20:00');

-- ----------------------------------------------------------
-- 6. COMMIT TRANSACTION & RE-ENABLE FOREIGN KEYS
-- ----------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
