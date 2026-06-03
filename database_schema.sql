-- ============================================================================
-- Simple Invoice Manager - Complete Database Schema
-- Generated from Laravel migrations and Eloquent Models
-- ============================================================================

-- Enable foreign key constraints
SET FOREIGN_KEY_CHECKS=1;

-- ============================================================================
-- CORE LARAVEL & JETSTREAM TABLES
-- ============================================================================

-- Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `current_team_id` bigint unsigned NULL DEFAULT NULL,
  `profile_photo_path` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `two_factor_secret` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `two_factor_recovery_codes` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `extra_attributes` json NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_email` (`email`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Personal Access Tokens Table
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE,
  `abilities` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_tokenable` (`tokenable_type`, `tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Teams Table
CREATE TABLE IF NOT EXISTS `teams` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_team` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Team User Pivot Table
CREATE TABLE IF NOT EXISTS `team_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `team_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `unique_team_user` (`team_id`, `user_id`),
  FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Team Invitations Table
CREATE TABLE IF NOT EXISTS `team_invitations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `team_id` bigint unsigned NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `unique_team_email` (`team_id`, `email`),
  FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SPATIE PERMISSION TABLES
-- ============================================================================

-- Roles Table
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_id` bigint unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `unique_role_guard_account` (`name`, `guard_name`, `account_id`),
  INDEX `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions Table
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `unique_permission_guard` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role Has Permissions Pivot Table
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`, `role_id`),
  FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Model Has Roles Pivot Table
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`, `model_type`, `model_id`),
  INDEX `idx_model_type_id` (`model_type`, `model_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Model Has Permissions Pivot Table
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`, `model_type`, `model_id`),
  INDEX `idx_model_type_id` (`model_type`, `model_id`),
  FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SPATIE ACTIVITY LOG TABLES
-- ============================================================================

-- Activity Log Table
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `subject_id` bigint unsigned NULL DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `causer_id` bigint unsigned NULL DEFAULT NULL,
  `properties` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `account_id` bigint unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_log_name` (`log_name`),
  INDEX `idx_subject` (`subject_type`, `subject_id`),
  INDEX `idx_causer` (`causer_type`, `causer_id`),
  INDEX `idx_account_id` (`account_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- APPLICATION CORE TABLES
-- ============================================================================

-- Accounts Table
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customers Table
CREATE TABLE IF NOT EXISTS `customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `account_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NULL DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `address` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `cf1` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cf2` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cf3` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cf4` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cf5` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cf6` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `extra_attributes` json NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_account_id` (`account_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_name` (`name`),
  INDEX `idx_email` (`email`),
  INDEX `idx_deleted_at` (`deleted_at`),
  FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Companies Table
CREATE TABLE IF NOT EXISTS `companies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `account_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `address` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `ss_image` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `logo_dark` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `show_name` tinyint(1) NULL DEFAULT 1,
  `show_address` tinyint(1) NULL DEFAULT NULL,
  `allow_transfer` tinyint(1) NULL DEFAULT NULL,
  `bank_account_details` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `qrcode` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `invoice_number` int NULL DEFAULT 0,
  `payment_number` int NULL DEFAULT 0,
  `quotation_number` int NULL DEFAULT 0,
  `extra_attributes` json NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_account_id` (`account_id`),
  INDEX `idx_name` (`name`),
  INDEX `idx_deleted_at` (`deleted_at`),
  FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products Table
CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `account_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(25, 4) NULL DEFAULT NULL,
  `details` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tax_method` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `extra_attributes` json NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_account_id` (`account_id`),
  INDEX `idx_name` (`name`),
  INDEX `idx_deleted_at` (`deleted_at`),
  FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tax Rates Table
CREATE TABLE IF NOT EXISTS `tax_rates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `account_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` decimal(25, 4) NULL DEFAULT NULL,
  `fixed` tinyint(1) NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_account_id` (`account_id`),
  INDEX `idx_deleted_at` (`deleted_at`),
  FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoices Table
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `account_id` bigint unsigned NOT NULL,
  `company_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `date` date NULL DEFAULT NULL,
  `due_date` date NULL DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `number` varchar(25) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `company_number` varchar(25) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `hash` char(36) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `note` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT 'pending',
  `total` decimal(25, 4) NULL DEFAULT NULL,
  `subtotal` decimal(25, 4) NULL DEFAULT NULL,
  `total_tax` decimal(25, 4) NULL DEFAULT NULL,
  `tax_method` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `grand_total` decimal(25, 4) NULL DEFAULT NULL,
  `shipping` decimal(25, 4) NULL DEFAULT NULL,
  `order_tax_amount` decimal(25, 4) NULL DEFAULT NULL,
  `product_tax_amount` decimal(25, 4) NULL DEFAULT NULL,
  `total_tax_amount` decimal(25, 4) NULL DEFAULT NULL,
  `order_discount` decimal(25, 4) NULL DEFAULT NULL,
  `order_discount_amount` decimal(25, 4) NULL DEFAULT NULL,
  `product_discount_amount` decimal(25, 4) NULL DEFAULT NULL,
  `total_discount_amount` decimal(25, 4) NULL DEFAULT NULL,
  `paid` decimal(25, 4) DEFAULT 0,
  `recurring` tinyint(1) NULL DEFAULT NULL,
  `repeat` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `create_before` int NULL DEFAULT NULL,
  `last_created_at` date NULL DEFAULT NULL,
  `next_create_date` date NULL DEFAULT NULL,
  `invoice_id` bigint unsigned NULL DEFAULT NULL,
  `receipt` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `extra_attributes` json NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_account_id` (`account_id`),
  INDEX `idx_company_id` (`company_id`),
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_date` (`date`),
  INDEX `idx_due_date` (`due_date`),
  INDEX `idx_status` (`status`),
  INDEX `idx_reference` (`reference`),
  INDEX `idx_number` (`number`),
  INDEX `idx_deleted_at` (`deleted_at`),
  FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoice Items Table
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `account_id` bigint unsigned NOT NULL,
  `invoice_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NULL DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `quantity` decimal(25, 4) NULL DEFAULT NULL,
  `price` decimal(25, 4) NULL DEFAULT NULL,
  `net_price` decimal(25, 4) NULL DEFAULT NULL,
  `unit_price` decimal(25, 4) NULL DEFAULT NULL,
  `tax_method` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `discount` decimal(25, 4) NULL DEFAULT NULL,
  `discount_amount` decimal(25, 4) NULL DEFAULT NULL,
  `total` decimal(25, 4) NULL DEFAULT NULL,
  `subtotal` decimal(25, 4) NULL DEFAULT NULL,
  `tax_amount` decimal(25, 4) NULL DEFAULT NULL,
  `total_discount_amount` decimal(25, 4) NULL DEFAULT NULL,
  `total_tax_amount` decimal(25, 4) NULL DEFAULT NULL,
  `extra_attributes` json NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_account_id` (`account_id`),
  INDEX `idx_invoice_id` (`invoice_id`),
  INDEX `idx_product_id` (`product_id`),
  INDEX `idx_deleted_at` (`deleted_at`),
  FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quotations Table
CREATE TABLE IF NOT EXISTS `quotations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `account_id` bigint unsigned NOT NULL,
  `company_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `date` date NULL DEFAULT NULL,
  `expiry_date` date NULL DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `number` varchar(25) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `company_number` varchar(25) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `hash` char(36) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `note` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT 'pending',
  `total` decimal(25, 4) NULL DEFAULT NULL,
  `subtotal` decimal(25, 4) NULL DEFAULT NULL,
  `total_tax` decimal(25, 4) NULL DEFAULT NULL,
  `tax_method` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `grand_total` decimal(25, 4) NULL DEFAULT NULL,
  `shipping` decimal(25, 4) NULL DEFAULT NULL,
  `order_tax_amount` decimal(25, 4) NULL DEFAULT NULL,
  `product_tax_amount` decimal(25, 4) NULL DEFAULT NULL,
  `total_tax_amount` decimal(25, 4) NULL DEFAULT NULL,
  `order_discount` decimal(25, 4) NULL DEFAULT NULL,
  `order_discount_amount` decimal(25, 4) NULL DEFAULT NULL,
  `product_discount_amount` decimal(25, 4) NULL DEFAULT NULL,
  `total_discount_amount` decimal(25, 4) NULL DEFAULT NULL,
  `extra_attributes` json NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_account_id` (`account_id`),
  INDEX `idx_company_id` (`company_id`),
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_date` (`date`),
  INDEX `idx_expiry_date` (`expiry_date`),
  INDEX `idx_status` (`status`),
  INDEX `idx_reference` (`reference`),
  INDEX `idx_number` (`number`),
  INDEX `idx_deleted_at` (`deleted_at`),
  FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quotation Items Table
CREATE TABLE IF NOT EXISTS `quotation_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `account_id` bigint unsigned NOT NULL,
  `quotation_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NULL DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `quantity` decimal(25, 4) NULL DEFAULT NULL,
  `price` decimal(25, 4) NULL DEFAULT NULL,
  `net_price` decimal(25, 4) NULL DEFAULT NULL,
  `unit_price` decimal(25, 4) NULL DEFAULT NULL,
  `tax_method` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `discount` decimal(25, 4) NULL DEFAULT NULL,
  `discount_amount` decimal(25, 4) NULL DEFAULT NULL,
  `total` decimal(25, 4) NULL DEFAULT NULL,
  `subtotal` decimal(25, 4) NULL DEFAULT NULL,
  `tax_amount` decimal(25, 4) NULL DEFAULT NULL,
  `extra_attributes` json NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_account_id` (`account_id`),
  INDEX `idx_quotation_id` (`quotation_id`),
  INDEX `idx_product_id` (`product_id`),
  INDEX `idx_deleted_at` (`deleted_at`),
  FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments Table
CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `account_id` bigint unsigned NOT NULL,
  `company_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `invoice_id` bigint unsigned NULL DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `date` date NULL DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `number` varchar(25) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `company_number` varchar(25) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `hash` char(36) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `amount` decimal(25, 4) NULL DEFAULT NULL,
  `fees` decimal(25, 4) NULL DEFAULT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `note` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `receipt_url` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `extra_attributes` json NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_account_id` (`account_id`),
  INDEX `idx_company_id` (`company_id`),
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_invoice_id` (`invoice_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_date` (`date`),
  INDEX `idx_reference` (`reference`),
  INDEX `idx_number` (`number`),
  INDEX `idx_deleted_at` (`deleted_at`),
  FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PIVOT/ASSOCIATION TABLES
-- ============================================================================

-- Invoice Tax Rates Pivot Table
CREATE TABLE IF NOT EXISTS `invoice_tax_rate` (
  `invoice_id` bigint unsigned NOT NULL,
  `tax_rate_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`invoice_id`, `tax_rate_id`),
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quotation Tax Rates Pivot Table
CREATE TABLE IF NOT EXISTS `quotation_tax_rate` (
  `quotation_id` bigint unsigned NOT NULL,
  `tax_rate_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`quotation_id`, `tax_rate_id`),
  FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment Tax Rates Pivot Table
CREATE TABLE IF NOT EXISTS `payment_tax_rate` (
  `payment_id` bigint unsigned NOT NULL,
  `tax_rate_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`payment_id`, `tax_rate_id`),
  FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoice Item Tax Rates Pivot Table
CREATE TABLE IF NOT EXISTS `invoice_item_tax_rate` (
  `invoice_item_id` bigint unsigned NOT NULL,
  `tax_rate_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`invoice_item_id`, `tax_rate_id`),
  FOREIGN KEY (`invoice_item_id`) REFERENCES `invoice_items` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quotation Item Tax Rates Pivot Table
CREATE TABLE IF NOT EXISTS `quotation_item_tax_rate` (
  `quotation_item_id` bigint unsigned NOT NULL,
  `tax_rate_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`quotation_item_id`, `tax_rate_id`),
  FOREIGN KEY (`quotation_item_id`) REFERENCES `quotation_items` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Tax Rates Pivot Table
CREATE TABLE IF NOT EXISTS `product_tax_rate` (
  `product_id` bigint unsigned NOT NULL,
  `tax_rate_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`product_id`, `tax_rate_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CONFIGURATION & METADATA TABLES
-- ============================================================================

-- Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `account_id` bigint unsigned NOT NULL,
  `tec_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tec_value` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `account_id` bigint unsigned NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_account_id` (`account_id`),
  INDEX `idx_code` (`code`),
  INDEX `idx_deleted_at` (`deleted_at`),
  FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fields Table
CREATE TABLE IF NOT EXISTS `fields` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `account_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `models` json NULL DEFAULT NULL,
  `options` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `required` tinyint(1) NULL DEFAULT NULL,
  `show` tinyint(1) NULL DEFAULT NULL,
  `order` int NULL DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_account_id` (`account_id`),
  INDEX `idx_slug` (`slug`),
  INDEX `idx_order` (`order`),
  INDEX `idx_deleted_at` (`deleted_at`),
  FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notes Table
CREATE TABLE IF NOT EXISTS `notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `account_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `default_sale` tinyint(1) NULL DEFAULT NULL,
  `default_quote` tinyint(1) NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_account_id` (`account_id`),
  INDEX `idx_deleted_at` (`deleted_at`),
  FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions Table
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `account_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `date` date NULL DEFAULT NULL,
  `amount` decimal(25, 4) NULL DEFAULT NULL,
  `data` longtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_account_id` (`account_id`),
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_date` (`date`),
  FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- JETSTREAM MEMBERSHIP TABLE
-- ============================================================================

-- Jetstream Memberships Table
CREATE TABLE IF NOT EXISTS `team_memberships` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `team_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_team_id` (`team_id`),
  INDEX `idx_user_id` (`user_id`),
  FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- FILE ATTACHMENTS TABLE
-- ============================================================================

-- Media/Attachments Table (for HasAttachments trait)
CREATE TABLE IF NOT EXISTS `media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NULL UNIQUE,
  `collection_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversions_disk` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `size` bigint unsigned NOT NULL,
  `manipulations` json NULL DEFAULT NULL,
  `custom_properties` json NULL DEFAULT NULL,
  `generated_conversions` json NULL DEFAULT NULL,
  `responsive_images` json NULL DEFAULT NULL,
  `order_column` int unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  INDEX `idx_model_type_id` (`model_type`, `model_id`),
  INDEX `idx_collection_name` (`collection_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================
