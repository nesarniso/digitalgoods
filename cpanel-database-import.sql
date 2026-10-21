-- Safe cPanel import: full schema, catalog data, and the single initial admin account.
-- No customer accounts, orders, settings, or application secrets are included.
-- Includes the product_reviews schema and all 90 migrations in this release.
SET FOREIGN_KEY_CHECKS=0;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `type` enum('shipping','billing','both') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'both',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Bangladesh',
  `division` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upazila` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `addresses_uuid_unique` (`uuid`),
  KEY `addresses_user_id_foreign` (`user_id`),
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `analytics_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `visitor_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `event_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `occurred_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `analytics_events_user_id_foreign` (`user_id`),
  KEY `analytics_events_event_type_occurred_at_index` (`event_type`,`occurred_at`),
  KEY `analytics_events_product_id_event_type_occurred_at_index` (`product_id`,`event_type`,`occurred_at`),
  KEY `analytics_events_visitor_id_index` (`visitor_id`),
  KEY `analytics_events_event_type_index` (`event_type`),
  KEY `analytics_events_occurred_at_index` (`occurred_at`),
  CONSTRAINT `analytics_events_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `analytics_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attribute_values` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` bigint unsigned NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attribute_values_attribute_id_value_unique` (`attribute_id`,`value`),
  CONSTRAINT `attribute_values_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attributes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attributes_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audit_logs_module_entity_type_entity_id_index` (`module`,`entity_type`,`entity_id`),
  KEY `audit_logs_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `audit_logs_chk_1` CHECK (json_valid(`old_value`)),
  CONSTRAINT `audit_logs_chk_2` CHECK (json_valid(`new_value`))
) ENGINE=InnoDB AUTO_INCREMENT=1358 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brands_uuid_unique` (`uuid`),
  UNIQUE KEY `brands_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_variant_id` bigint unsigned DEFAULT NULL,
  `quantity` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_items_cart_product_variant_unique` (`cart_id`,`product_id`,`product_variant_id`),
  KEY `cart_items_product_id_foreign` (`product_id`),
  KEY `cart_items_product_variant_id_foreign` (`product_variant_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `coupon_id` bigint unsigned DEFAULT NULL,
  `guest_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkout_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkout_phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkout_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkout_contacted_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `abandoned_notified_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `carts_uuid_unique` (`uuid`),
  UNIQUE KEY `carts_user_id_unique` (`user_id`),
  UNIQUE KEY `carts_guest_token_unique` (`guest_token`),
  KEY `carts_coupon_id_foreign` (`coupon_id`),
  KEY `carts_abandoned_notified_at_index` (`abandoned_notified_at`),
  KEY `carts_checkout_contacted_at_index` (`checkout_contacted_at`),
  CONSTRAINT `carts_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=311 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_uuid_unique` (`uuid`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_parent_id_sort_order_index` (`parent_id`,`sort_order`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compare_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `compare_items_user_id_product_id_unique` (`user_id`,`product_id`),
  KEY `compare_items_product_id_foreign` (`product_id`),
  CONSTRAINT `compare_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `compare_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `topic` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_number` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupon_categories` (
  `coupon_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`coupon_id`,`category_id`),
  KEY `coupon_categories_category_id_foreign` (`category_id`),
  CONSTRAINT `coupon_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_categories_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupon_products` (
  `coupon_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`coupon_id`,`product_id`),
  KEY `coupon_products_product_id_foreign` (`product_id`),
  CONSTRAINT `coupon_products_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupon_usages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `discount_amount` decimal(12,2) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `coupon_usages_user_id_foreign` (`user_id`),
  KEY `coupon_usages_coupon_id_user_id_index` (`coupon_id`,`user_id`),
  CONSTRAINT `coupon_usages_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('percentage','fixed','free_shipping') COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` decimal(12,2) DEFAULT NULL,
  `min_order_amount` decimal(12,2) DEFAULT NULL,
  `max_discount_amount` decimal(12,2) DEFAULT NULL,
  `usage_limit` int unsigned DEFAULT NULL,
  `per_user_limit` int unsigned DEFAULT NULL,
  `used_count` int unsigned NOT NULL DEFAULT '0',
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupons_uuid_unique` (`uuid`),
  UNIQUE KEY `coupons_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courier_webhook_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `courier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courier_webhook_events_courier_event_id_unique` (`courier`,`event_id`),
  CONSTRAINT `courier_webhook_events_chk_1` CHECK (json_valid(`payload`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loyalty_points` int unsigned NOT NULL DEFAULT '0',
  `referral_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referred_by_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_profiles_user_id_unique` (`user_id`),
  UNIQUE KEY `customer_profiles_referral_code_unique` (`referral_code`),
  KEY `customer_profiles_referred_by_user_id_foreign` (`referred_by_user_id`),
  CONSTRAINT `customer_profiles_referred_by_user_id_foreign` FOREIGN KEY (`referred_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `digital_license_keys` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `product_variant_id` bigint unsigned DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `status` enum('available','reserved','assigned','revoked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `order_id` bigint unsigned DEFAULT NULL,
  `order_item_id` bigint unsigned DEFAULT NULL,
  `reserved_at` timestamp NULL DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `viewed_at` timestamp NULL DEFAULT NULL,
  `download_count` int unsigned NOT NULL DEFAULT '0',
  `email_sent_at` timestamp NULL DEFAULT NULL,
  `email_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_error` text COLLATE utf8mb4_unicode_ci,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `revoked_by` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `digital_license_keys_reference_unique` (`reference`),
  KEY `digital_license_keys_order_id_foreign` (`order_id`),
  KEY `digital_license_keys_order_item_id_foreign` (`order_item_id`),
  KEY `digital_license_keys_created_by_foreign` (`created_by`),
  KEY `digital_license_keys_product_id_status_index` (`product_id`,`status`),
  KEY `digital_license_keys_secret_hash_index` (`secret_hash`),
  KEY `digital_license_keys_revoked_by_foreign` (`revoked_by`),
  KEY `digital_license_keys_product_variant_id_foreign` (`product_variant_id`),
  KEY `digital_license_variant_stock_index` (`product_id`,`product_variant_id`,`status`),
  CONSTRAINT `digital_license_keys_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `digital_license_keys_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `digital_license_keys_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `digital_license_keys_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `digital_license_keys_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `digital_license_keys_revoked_by_foreign` FOREIGN KEY (`revoked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `template_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('sent','failed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `error` text COLLATE utf8mb4_unicode_ci,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_logs_template_key_index` (`template_key`),
  KEY `email_logs_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_templates_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `employee_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reports_to_user_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_profiles_user_id_unique` (`user_id`),
  UNIQUE KEY `employee_profiles_employee_id_unique` (`employee_id`),
  KEY `employee_profiles_reports_to_user_id_foreign` (`reports_to_user_id`),
  CONSTRAINT `employee_profiles_reports_to_user_id_foreign` FOREIGN KEY (`reports_to_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entrepreneur_link_clicks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entrepreneur_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `attribution_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `session_key` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entrepreneur_link_clicks_product_id_foreign` (`product_id`),
  KEY `entrepreneur_link_clicks_entrepreneur_id_product_id_index` (`entrepreneur_id`,`product_id`),
  KEY `entrepreneur_link_clicks_attribution_code_created_at_index` (`attribution_code`,`created_at`),
  CONSTRAINT `entrepreneur_link_clicks_entrepreneur_id_foreign` FOREIGN KEY (`entrepreneur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entrepreneur_link_clicks_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entrepreneur_products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entrepreneur_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `custom_selling_price` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entrepreneur_products_entrepreneur_id_product_id_unique` (`entrepreneur_id`,`product_id`),
  KEY `entrepreneur_products_product_id_foreign` (`product_id`),
  KEY `entrepreneur_products_entrepreneur_id_status_index` (`entrepreneur_id`,`status`),
  CONSTRAINT `entrepreneur_products_entrepreneur_id_foreign` FOREIGN KEY (`entrepreneur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entrepreneur_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entrepreneur_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `entrepreneur_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `address` text COLLATE utf8mb4_unicode_ci,
  `division` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upazila` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `union_or_ward` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referred_by_user_id` bigint unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entrepreneur_profiles_user_id_unique` (`user_id`),
  UNIQUE KEY `entrepreneur_profiles_entrepreneur_code_unique` (`entrepreneur_code`),
  KEY `entrepreneur_profiles_referred_by_user_id_foreign` (`referred_by_user_id`),
  KEY `entrepreneur_profiles_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `entrepreneur_profiles_referred_by_user_id_foreign` FOREIGN KEY (`referred_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entrepreneur_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feature_flags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `feature_flags_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `installer_state` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `installed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `installer_update_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `from_version` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_version` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `backup_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `installer_update_history_user_id_foreign` (`user_id`),
  CONSTRAINT `installer_update_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `product_variant_id` bigint unsigned DEFAULT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `physical_stock` int unsigned NOT NULL DEFAULT '0',
  `reserved_stock` int unsigned NOT NULL DEFAULT '0',
  `damaged_stock` int unsigned NOT NULL DEFAULT '0',
  `low_stock_threshold` int unsigned NOT NULL DEFAULT '5',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventories_product_variant_warehouse_unique` (`product_id`,`product_variant_id`,`warehouse_id`),
  KEY `inventories_product_variant_id_foreign` (`product_variant_id`),
  KEY `inventories_warehouse_id_foreign` (`warehouse_id`),
  CONSTRAINT `inventories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventories_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventories_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `login_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'password',
  `successful` tinyint(1) NOT NULL DEFAULT '1',
  `logged_in_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `login_histories_user_id_foreign` (`user_id`),
  CONSTRAINT `login_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketing_campaigns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `contact_list_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `sent_count` int unsigned NOT NULL DEFAULT '0',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `marketing_campaigns_chk_1` CHECK (json_valid(`recipients`)),
  CONSTRAINT `marketing_campaigns_chk_2` CHECK (json_valid(`contact_list_ids`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketing_contact_lists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_count` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketing_contacts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `contact_list_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `marketing_contacts_contact_list_id_email_unique` (`contact_list_id`,`email`),
  CONSTRAINT `marketing_contacts_contact_list_id_foreign` FOREIGN KEY (`contact_list_id`) REFERENCES `marketing_contact_lists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_campaigns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_by` bigint unsigned DEFAULT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `audience` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `recipient_count` int unsigned NOT NULL DEFAULT '0',
  `sent_count` int unsigned NOT NULL DEFAULT '0',
  `failed_count` int unsigned NOT NULL DEFAULT '0',
  `queued_at` timestamp NULL DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notification_campaigns_created_by_foreign` (`created_by`),
  KEY `notification_campaigns_status_created_at_index` (`status`,`created_at`),
  KEY `notification_campaigns_status_scheduled_at_index` (`status`,`scheduled_at`),
  CONSTRAINT `notification_campaigns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_deliveries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `notification_campaign_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `push_subscription_id` bigint unsigned DEFAULT NULL,
  `device_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `clicked_at` timestamp NULL DEFAULT NULL,
  `error` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notification_deliveries_notification_campaign_id_foreign` (`notification_campaign_id`),
  KEY `notification_deliveries_status_created_at_index` (`status`,`created_at`),
  KEY `notification_deliveries_user_id_clicked_at_index` (`user_id`,`clicked_at`),
  KEY `notification_deliveries_push_subscription_id_status_index` (`push_subscription_id`,`status`),
  CONSTRAINT `notification_deliveries_notification_campaign_id_foreign` FOREIGN KEY (`notification_campaign_id`) REFERENCES `notification_campaigns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `notification_deliveries_push_subscription_id_foreign` FOREIGN KEY (`push_subscription_id`) REFERENCES `push_subscriptions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `notification_deliveries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`),
  KEY `notifications_read_at_index` (`read_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `type` enum('shipping','billing') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'shipping',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `division` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upazila` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_addresses_order_id_foreign` (`order_id`),
  CONSTRAINT `order_addresses_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `product_variant_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `variant_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `cost_price` decimal(12,2) DEFAULT NULL,
  `supplier_cost` decimal(12,2) DEFAULT NULL,
  `delivery_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `return_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `entrepreneur_profit` decimal(12,2) DEFAULT NULL,
  `entrepreneur_profit_percent` decimal(5,2) DEFAULT NULL,
  `entrepreneur_profit_flat_rate` decimal(12,2) DEFAULT NULL,
  `applicable_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `profit` decimal(12,2) NOT NULL DEFAULT '0.00',
  `profit_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `quantity` int unsigned NOT NULL,
  `line_total` decimal(12,2) NOT NULL,
  `stock_reserved` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  KEY `order_items_product_variant_id_foreign` (`product_variant_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_status_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actor_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_status_histories_actor_id_foreign` (`actor_id`),
  KEY `order_status_histories_order_id_created_at_index` (`order_id`,`created_at`),
  CONSTRAINT `order_status_histories_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_status_histories_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idempotency_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `entrepreneur_id` bigint unsigned DEFAULT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','confirmed','processing','packed','shipped','out_for_delivery','delivered','cancelled','returned','refunded','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'direct',
  `attribution_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `shipping_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(12,2) NOT NULL,
  `coupon_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_method_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_review_required` tinyint(1) NOT NULL DEFAULT '0',
  `payment_review_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `placed_at` timestamp NOT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancel_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_uuid_unique` (`uuid`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  UNIQUE KEY `orders_idempotency_key_unique` (`idempotency_key`),
  KEY `orders_user_id_status_index` (`user_id`,`status`),
  KEY `orders_status_index` (`status`),
  KEY `orders_entrepreneur_id_status_index` (`entrepreneur_id`,`status`),
  KEY `orders_attribution_code_source_index` (`attribution_code`,`source`),
  KEY `orders_payment_status_index` (`payment_status`),
  CONSTRAINT `orders_entrepreneur_id_foreign` FOREIGN KEY (`entrepreneur_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `otp_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'login',
  `attempts` tinyint unsigned NOT NULL DEFAULT '0',
  `expires_at` timestamp NOT NULL,
  `consumed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `otp_codes_identifier_purpose_index` (`identifier`,`purpose`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `is_published` tinyint(1) NOT NULL DEFAULT '1',
  `show_in_footer` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `attempts` tinyint unsigned NOT NULL DEFAULT '0',
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `password_reset_codes_email_expires_at_index` (`email`,`expires_at`),
  KEY `password_reset_codes_email_index` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `gateway` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idempotency_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gateway_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('initiated','pending','authorized','paid','failed','cancelled','refunded','partially_refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'initiated',
  `amount` decimal(12,2) NOT NULL,
  `refunded_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BDT',
  `payer_user_id` bigint unsigned DEFAULT NULL,
  `payer_mobile` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manual_transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitted_amount` decimal(12,2) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `raw_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `failure_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_transactions_uuid_unique` (`uuid`),
  UNIQUE KEY `payment_transactions_idempotency_key_unique` (`idempotency_key`),
  UNIQUE KEY `payment_transactions_gateway_reference_unique` (`gateway_reference`),
  UNIQUE KEY `payment_transactions_manual_transaction_id_unique` (`manual_transaction_id`),
  KEY `payment_transactions_payer_user_id_foreign` (`payer_user_id`),
  KEY `payment_transactions_gateway_status_index` (`gateway`,`status`),
  KEY `payment_transactions_order_id_foreign` (`order_id`),
  KEY `payment_transactions_gateway_manual_transaction_id_index` (`gateway`,`manual_transaction_id`),
  CONSTRAINT `payment_transactions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_transactions_payer_user_id_foreign` FOREIGN KEY (`payer_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_transactions_chk_1` CHECK (json_valid(`raw_response`))
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=704 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `product_variant_id` bigint unsigned DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_thumbnail` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_foreign` (`product_id`),
  KEY `product_images_product_variant_id_foreign` (`product_variant_id`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_images_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned NOT NULL,
  `rating` tinyint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `verified_purchase` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_reviews_product_id_user_id_order_id_unique` (`product_id`,`user_id`,`order_id`),
  KEY `product_reviews_user_id_foreign` (`user_id`),
  KEY `product_reviews_order_id_foreign` (`order_id`),
  KEY `product_reviews_product_id_status_index` (`product_id`,`status`),
  CONSTRAINT `product_reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_tag` (
  `product_id` bigint unsigned NOT NULL,
  `tag_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`tag_id`),
  KEY `product_tag_tag_id_foreign` (`tag_id`),
  CONSTRAINT `product_tag_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_variant_attribute_value` (
  `product_variant_id` bigint unsigned NOT NULL,
  `attribute_value_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`product_variant_id`,`attribute_value_id`),
  KEY `product_variant_attribute_value_attribute_value_id_foreign` (`attribute_value_id`),
  CONSTRAINT `product_variant_attribute_value_attribute_value_id_foreign` FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_variant_attribute_value_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_price` decimal(12,2) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `sale_price` decimal(12,2) DEFAULT NULL,
  `weight` decimal(10,3) DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variants_sku_unique` (`sku`),
  UNIQUE KEY `product_variants_barcode_unique` (`barcode`),
  KEY `product_variants_product_id_foreign` (`product_id`),
  CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vendor_id` bigint unsigned DEFAULT NULL,
  `category_id` bigint unsigned NOT NULL,
  `brand_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_type` enum('simple','variable','digital') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'simple',
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `short_description` text COLLATE utf8mb4_unicode_ci,
  `purchase_price` decimal(12,2) DEFAULT NULL,
  `entrepreneur_profit_percent` decimal(5,2) DEFAULT NULL,
  `entrepreneur_profit_flat_rate` decimal(12,2) DEFAULT NULL,
  `regular_price` decimal(12,2) NOT NULL,
  `sale_price` decimal(12,2) DEFAULT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `weight` decimal(10,3) DEFAULT NULL,
  `length` decimal(10,3) DEFAULT NULL,
  `width` decimal(10,3) DEFAULT NULL,
  `height` decimal(10,3) DEFAULT NULL,
  `minimum_order_quantity` int unsigned NOT NULL DEFAULT '1',
  `maximum_order_quantity` int unsigned DEFAULT NULL,
  `status` enum('draft','pending_review','published','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `published_at` timestamp NULL DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_uuid_unique` (`uuid`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  UNIQUE KEY `products_barcode_unique` (`barcode`),
  KEY `products_brand_id_foreign` (`brand_id`),
  KEY `products_created_by_foreign` (`created_by`),
  KEY `products_updated_by_foreign` (`updated_by`),
  KEY `products_category_id_status_index` (`category_id`,`status`),
  KEY `products_vendor_id_status_index` (`vendor_id`,`status`),
  KEY `products_featured_index` (`featured`),
  KEY `products_status_published_at_index` (`status`,`published_at`),
  CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `products_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=400 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `push_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `token` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `device_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `push_subscriptions_token_hash_unique` (`token_hash`),
  KEY `push_subscriptions_user_id_revoked_at_index` (`user_id`,`revoked_at`),
  CONSTRAINT `push_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pwa_devices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `device_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `platform` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `installed_at` timestamp NULL DEFAULT NULL,
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pwa_devices_device_id_unique` (`device_id`),
  KEY `pwa_devices_user_id_foreign` (`user_id`),
  KEY `pwa_devices_last_seen_at_index` (`last_seen_at`),
  CONSTRAINT `pwa_devices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pwa_install_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `platform` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pwa_install_events_user_id_foreign` (`user_id`),
  KEY `pwa_install_events_event_created_at_index` (`event`,`created_at`),
  KEY `pwa_install_events_device_id_event_index` (`device_id`,`event`),
  CONSTRAINT `pwa_install_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `restock_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inventory_id` bigint unsigned NOT NULL,
  `requested_quantity` int unsigned NOT NULL,
  `status` enum('pending','approved','rejected','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `requested_by` bigint unsigned DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `restock_requests_uuid_unique` (`uuid`),
  KEY `restock_requests_inventory_id_foreign` (`inventory_id`),
  KEY `restock_requests_requested_by_foreign` (`requested_by`),
  KEY `restock_requests_approved_by_foreign` (`approved_by`),
  CONSTRAINT `restock_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `restock_requests_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `restock_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `return_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` bigint unsigned NOT NULL,
  `order_item_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `quantity` int unsigned NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_note` text COLLATE utf8mb4_unicode_ci,
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `status` enum('requested','approved','rejected','received','refunded','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'requested',
  `requested_at` timestamp NOT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `return_requests_uuid_unique` (`uuid`),
  UNIQUE KEY `return_requests_request_number_unique` (`request_number`),
  KEY `return_requests_order_item_id_foreign` (`order_item_id`),
  KEY `return_requests_order_id_status_index` (`order_id`,`status`),
  KEY `return_requests_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `return_requests_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `return_requests_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `return_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=324 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`),
  KEY `settings_group_index` (`group`)
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` bigint unsigned NOT NULL,
  `shipment_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `courier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `tracking_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending_dispatch','picked_up','in_transit','out_for_delivery','delivered','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_dispatch',
  `delivery_fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `picked_up_at` timestamp NULL DEFAULT NULL,
  `dispatched_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `failed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shipments_uuid_unique` (`uuid`),
  UNIQUE KEY `shipments_shipment_number_unique` (`shipment_number`),
  KEY `shipments_order_id_status_index` (`order_id`,`status`),
  KEY `shipments_courier_index` (`courier`),
  CONSTRAINT `shipments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shipments_chk_1` CHECK (json_valid(`metadata`))
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipping_methods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('flat','free','weight_based','price_based') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shipping_methods_uuid_unique` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipping_rates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shipping_method_id` bigint unsigned NOT NULL,
  `shipping_zone_id` bigint unsigned DEFAULT NULL,
  `base_rate` decimal(12,2) NOT NULL DEFAULT '0.00',
  `per_kg_rate` decimal(12,2) DEFAULT NULL,
  `free_above_amount` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shipping_rates_method_zone_unique` (`shipping_method_id`,`shipping_zone_id`),
  KEY `shipping_rates_shipping_zone_id_foreign` (`shipping_zone_id`),
  CONSTRAINT `shipping_rates_shipping_method_id_foreign` FOREIGN KEY (`shipping_method_id`) REFERENCES `shipping_methods` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shipping_rates_shipping_zone_id_foreign` FOREIGN KEY (`shipping_zone_id`) REFERENCES `shipping_zones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipping_zones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `division` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` text COLLATE utf8mb4_unicode_ci,
  `area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shipping_zones_uuid_unique` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_id` bigint unsigned NOT NULL,
  `type` enum('adjustment','reservation','release','sale','restock','damaged','returned','transfer_in','transfer_out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `physical_stock_after` int unsigned NOT NULL,
  `reserved_stock_after` int unsigned NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actor_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `stock_movements_actor_id_foreign` (`actor_id`),
  KEY `stock_movements_inventory_id_created_at_index` (`inventory_id`,`created_at`),
  KEY `stock_movements_reference_type_reference_id_index` (`reference_type`,`reference_id`),
  CONSTRAINT `stock_movements_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_movements_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `subject` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_tickets_user_id_foreign` (`user_id`),
  CONSTRAINT `support_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tags_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_uuid_unique` (`uuid`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_phone_unique` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wallet_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entrepreneur_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'posted',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wallet_transactions_entrepreneur_id_status_created_at_index` (`entrepreneur_id`,`status`,`created_at`),
  KEY `wallet_transactions_reference_type_reference_id_index` (`reference_type`,`reference_id`),
  CONSTRAINT `wallet_transactions_entrepreneur_id_foreign` FOREIGN KEY (`entrepreneur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `warehouses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `division` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `warehouses_uuid_unique` (`uuid`),
  UNIQUE KEY `warehouses_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wishlist_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wishlist_items_user_id_product_id_unique` (`user_id`,`product_id`),
  KEY `wishlist_items_product_id_foreign` (`product_id`),
  CONSTRAINT `wishlist_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `withdrawals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entrepreneur_id` bigint unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `requested_at` timestamp NOT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `withdrawals_entrepreneur_id_status_index` (`entrepreneur_id`,`status`),
  CONSTRAINT `withdrawals_entrepreneur_id_foreign` FOREIGN KEY (`entrepreneur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2026_08_20_000002_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2026_08_20_000003_create_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2026_08_20_000004_create_feature_flags_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2026_08_21_000001_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2026_08_21_000002_create_customer_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2026_08_21_000003_create_employee_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_08_21_000004_create_login_histories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_08_21_000005_create_otp_codes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_08_22_000001_create_audit_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_08_23_000001_create_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2026_08_23_000002_create_brands_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2026_08_23_000003_create_tags_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2026_08_23_000004_create_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2026_08_23_000005_create_product_tag_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2026_08_23_000006_create_attributes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2026_08_23_000007_create_product_variants_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2026_08_23_000009_create_product_images_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2026_08_24_000001_create_warehouses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2026_08_24_000002_create_inventories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2026_08_24_000003_create_stock_movements_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2026_08_24_000004_create_restock_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2026_08_25_000001_create_carts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2026_08_25_000002_create_cart_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2026_08_25_000003_create_wishlist_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2026_08_25_000004_create_compare_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2026_08_26_000001_create_coupons_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2026_08_26_000002_create_coupon_scope_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2026_08_26_000003_create_coupon_usages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2026_08_26_000004_add_coupon_id_to_carts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2026_08_27_000001_create_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2026_08_27_000002_create_shipping_zones_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2026_08_27_000003_create_shipping_methods_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2026_08_27_000004_create_shipping_rates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2026_08_28_000001_create_payment_transactions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2026_08_29_000001_create_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2026_08_29_000002_create_order_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2026_08_29_000003_create_order_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2026_08_29_000004_create_order_status_histories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2026_08_29_000005_add_order_id_to_payment_transactions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2026_09_07_000001_create_shipments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2026_09_07_000002_create_courier_webhook_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2026_09_07_000003_create_return_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2026_09_07_000004_create_product_reviews_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_09_07_000005_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2026_09_07_000006_enhance_product_media_seo',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2026_09_07_000007_create_email_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2026_09_07_000008_create_marketing_campaigns_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2026_09_07_000009_add_error_message_to_marketing_campaigns_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2026_09_07_000010_create_marketing_contact_lists_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2026_09_07_000011_add_contact_lists_to_marketing_campaigns_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2026_09_08_000001_create_pages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2026_09_08_000002_create_contact_messages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2026_09_08_000003_create_support_tickets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2026_09_08_000004_create_pwa_install_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2026_09_08_000005_create_pwa_devices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2026_09_08_000006_create_push_subscriptions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2026_09_08_000007_create_push_notification_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2026_09_08_000008_add_push_delivery_metadata',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2026_09_08_000009_add_scheduled_at_to_notification_campaigns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2026_09_08_000010_add_abandoned_cart_notification_at',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2026_09_08_000011_create_installer_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2026_09_11_000001_create_entrepreneur_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2026_09_11_000002_add_area_to_employee_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2026_09_11_000002_create_entrepreneur_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2026_09_11_000003_add_entrepreneur_attribution_to_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2026_09_11_000004_create_wallet_transactions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2026_09_11_000005_create_withdrawals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2026_09_11_000006_add_profit_snapshot_to_order_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2026_09_11_000007_create_entrepreneur_link_clicks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2026_09_11_000008_add_landed_cost_snapshots_to_order_items',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2026_09_11_000009_add_profit_percent_to_products',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2026_09_11_000010_add_flat_profit_rates',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2026_09_12_000001_add_manual_payment_details',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2026_09_12_000002_seed_default_shipping_zones',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2026_09_12_000003_expand_shipping_zone_district_column',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2026_09_12_000004_add_upazila_to_addresses',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2026_09_14_000001_add_checkout_contact_to_carts_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2026_09_16_000001_create_digital_license_keys_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2026_09_16_000002_add_digital_delivery_tracking',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2026_09_16_000004_create_email_logs_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2026_09_15_000001_add_payment_status_to_orders_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2026_09_15_000002_add_catalog_read_indexes',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2026_09_15_000003_repair_payment_status_on_orders_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2026_09_16_000003_add_payment_review_to_orders_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2026_09_16_000005_add_variant_to_digital_license_keys_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2026_09_18_000001_create_password_reset_codes_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2026_09_18_000002_create_analytics_events_table',15);
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (307,'super_admin','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (308,'admin','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (309,'manager','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (310,'product_manager','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (311,'order_manager','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (312,'inventory_manager','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (313,'marketing_manager','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (314,'support_agent','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (315,'accountant','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (316,'vendor','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (317,'entrepreneur','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (318,'marketing_officer','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (319,'boosting_team','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (320,'warehouse_manager','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (321,'finance_manager','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (322,'deliveryman','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (323,'customer','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (667,'product.view','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (668,'product.create','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (669,'product.update','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (670,'product.delete','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (671,'order.view','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (672,'order.create','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (673,'order.update','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (674,'order.cancel','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (675,'order.refund','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (676,'vendor.view','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (677,'vendor.approve','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (678,'vendor.suspend','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (679,'customer.view','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (680,'customer.update','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (681,'customer.block','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (682,'coupon.view','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (683,'coupon.create','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (684,'coupon.update','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (685,'coupon.delete','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (686,'payment.view','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (687,'payment.manage','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (688,'report.view','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (689,'setting.manage','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (690,'employee.view','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (691,'employee.manage','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (692,'role.manage','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (693,'entrepreneur.view','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (694,'entrepreneur.register','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (695,'entrepreneur.verify','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (696,'entrepreneur.suspend','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (697,'entrepreneur.marketplace','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (698,'entrepreneur.order.create','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (699,'entrepreneur.order.view','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (700,'entrepreneur.wallet.view','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (701,'withdrawal.request','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (702,'withdrawal.approve','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (703,'withdrawal.reject','web','2026-09-11 12:13:22','2026-09-11 12:13:22');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (667,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (668,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (669,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (670,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (671,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (672,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (673,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (674,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (675,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (676,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (677,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (678,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (679,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (680,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (681,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (682,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (683,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (684,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (685,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (686,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (687,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (688,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (689,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (690,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (691,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (692,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (693,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (694,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (695,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (696,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (697,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (698,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (699,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (700,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (701,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (702,307);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (703,307);
INSERT INTO `feature_flags` (`id`, `key`, `enabled`, `description`, `created_at`, `updated_at`) VALUES (1,'digital_products',1,'Digital/downloadable products.','2026-09-16 09:17:03','2026-09-16 09:17:03');
INSERT INTO `shipping_zones` (`id`, `uuid`, `name`, `country`, `division`, `district`, `area`, `is_active`, `created_at`, `updated_at`) VALUES (5,'adf7c324-6a19-4722-aaac-dc568f103aee','bd',NULL,NULL,'Bagerhat, Bandarban, Barguna, Barishal, Bhola, Bogura, Brahmanbaria, Chandpur, Chattogram, Chuadanga, Cox\'s Bazar, Cumilla, Dhaka, Dinajpur, Faridpur, Feni, Gaibandha, Gazipur, Gopalganj, Habiganj, Jamalpur, Jashore, Jhalokati, Jhenaidah, Joypurhat, Khagrachhari, Khulna, Kishoreganj, Kurigram, Kushtia, Lakshmipur, Lalmonirhat, Madaripur, Magura, Manikganj, Meherpur, Moulvibazar, Munshiganj, Mymensingh, Naogaon, Narail, Narayanganj, Narsingdi, Natore, Nawabganj, Netrokona, Nilphamari, Noakhali, Pabna, Panchagarh, Patuakhali, Pirojpur, Rajbari, Rajshahi, Rangamati, Rangpur, Satkhira, Shariatpur, Sherpur, Sirajganj, Sunamganj, Sylhet, Tangail, Thakurgaon',NULL,1,'2026-09-14 17:47:50','2026-09-14 17:47:50');
INSERT INTO `shipping_methods` (`id`, `uuid`, `name`, `type`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (5,'2dc74c5d-7477-4cd5-955f-02e7474d7363','Free','flat',1,0,'2026-09-14 17:46:42','2026-09-14 17:46:42');
INSERT INTO `shipping_rates` (`id`, `shipping_method_id`, `shipping_zone_id`, `base_rate`, `per_kg_rate`, `free_above_amount`, `created_at`, `updated_at`) VALUES (8,5,NULL,0.00,NULL,NULL,'2026-09-14 17:48:00','2026-09-14 17:48:00');
INSERT INTO `categories` (`id`, `uuid`, `parent_id`, `name`, `slug`, `description`, `image_path`, `banner_path`, `icon_path`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES (34,'5b0b7027-b332-4a2f-84ac-63b3a45fad87',NULL,'Electronics','electronics',NULL,NULL,NULL,NULL,1,1,1,'Electronics products','Shop Electronics products from our trusted store.','2026-09-12 01:54:32','2026-09-14 14:01:17','2026-09-14 14:01:17');
INSERT INTO `categories` (`id`, `uuid`, `parent_id`, `name`, `slug`, `description`, `image_path`, `banner_path`, `icon_path`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES (35,'ad0b36cf-2984-4aa9-8a42-493d5c5e372c',NULL,'Fashion','fashion',NULL,NULL,NULL,NULL,2,1,1,'Fashion products','Shop Fashion products from our trusted store.','2026-09-12 01:54:32','2026-09-14 14:01:19','2026-09-14 14:01:19');
INSERT INTO `categories` (`id`, `uuid`, `parent_id`, `name`, `slug`, `description`, `image_path`, `banner_path`, `icon_path`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES (36,'49728285-db12-46b4-93fe-86b6ae81a6d3',NULL,'Home & Living','home-living',NULL,NULL,NULL,NULL,3,1,1,'Home & Living products','Shop Home & Living products from our trusted store.','2026-09-12 01:54:32','2026-09-14 14:01:21','2026-09-14 14:01:21');
INSERT INTO `categories` (`id`, `uuid`, `parent_id`, `name`, `slug`, `description`, `image_path`, `banner_path`, `icon_path`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES (37,'066ad5b5-cec6-48af-b9e7-8645efb8de28',NULL,'Beauty & Care','beauty-care',NULL,NULL,NULL,NULL,4,1,0,'Beauty & Care products','Shop Beauty & Care products from our trusted store.','2026-09-12 01:54:32','2026-09-14 14:01:24','2026-09-14 14:01:24');
INSERT INTO `categories` (`id`, `uuid`, `parent_id`, `name`, `slug`, `description`, `image_path`, `banner_path`, `icon_path`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES (38,'f6a4806d-90ab-4dac-9f25-83ae86e0a6d1',NULL,'Uncategorized','uncategorized',NULL,NULL,NULL,NULL,15,1,0,'Uncategorized','Browse Uncategorized products.','2026-09-12 01:54:32','2026-09-14 14:01:26','2026-09-14 14:01:26');
INSERT INTO `categories` (`id`, `uuid`, `parent_id`, `name`, `slug`, `description`, `image_path`, `banner_path`, `icon_path`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES (39,'e589f913-e40f-4154-a538-36a7479faeae',NULL,'অন্যান্য','onzanz',NULL,NULL,NULL,NULL,31,1,1,'অন্যান্য','Browse অন্যান্য products.','2026-09-12 01:54:32','2026-09-14 14:18:28',NULL);
INSERT INTO `categories` (`id`, `uuid`, `parent_id`, `name`, `slug`, `description`, `image_path`, `banner_path`, `icon_path`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES (40,'c20f778b-5d91-49a5-a670-3545d5aa8fdc',NULL,'আমাজন','amajn',NULL,NULL,NULL,NULL,146,1,1,'আমাজন','Browse আমাজন products.','2026-09-12 01:54:32','2026-09-14 14:19:02',NULL);
INSERT INTO `categories` (`id`, `uuid`, `parent_id`, `name`, `slug`, `description`, `image_path`, `banner_path`, `icon_path`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES (41,'c728824a-bd75-4bb1-9e74-8f03330c76b3',NULL,'এ আই','e-ai',NULL,NULL,NULL,NULL,43,1,1,'এ আই','Browse এ আই products.','2026-09-12 01:54:32','2026-09-14 14:18:33',NULL);
INSERT INTO `categories` (`id`, `uuid`, `parent_id`, `name`, `slug`, `description`, `image_path`, `banner_path`, `icon_path`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES (42,'cbc07d4f-e67f-4d73-a0aa-030f6ea7e1ed',NULL,'এন্টারটেইনমেন্ট এবং স্ট্রিমিং','entarteinment-ebng-striming',NULL,NULL,NULL,NULL,29,1,1,'এন্টারটেইনমেন্ট এবং স্ট্রিমিং','Browse এন্টারটেইনমেন্ট এবং স্ট্রিমিং products.','2026-09-12 01:54:32','2026-09-14 14:18:20',NULL);
INSERT INTO `categories` (`id`, `uuid`, `parent_id`, `name`, `slug`, `description`, `image_path`, `banner_path`, `icon_path`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES (43,'f56a4391-a9fa-4a87-ad10-4948c0696651',NULL,'গিফট কার্ড','gift-kard',NULL,NULL,NULL,NULL,44,1,1,'গিফট কার্ড','Browse গিফট কার্ড products.','2026-09-12 01:54:32','2026-09-14 14:18:36',NULL);
INSERT INTO `categories` (`id`, `uuid`, `parent_id`, `name`, `slug`, `description`, `image_path`, `banner_path`, `icon_path`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES (44,'bd932447-91d1-4f3e-9ba7-a7ba4ab8eee2',NULL,'গেমস','gems',NULL,NULL,NULL,NULL,45,1,1,'গেমস','Browse গেমস products.','2026-09-12 01:54:32','2026-09-14 14:18:43',NULL);
INSERT INTO `categories` (`id`, `uuid`, `parent_id`, `name`, `slug`, `description`, `image_path`, `banner_path`, `icon_path`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES (45,'c53a9b5e-adac-402f-b168-b602a82c396e',NULL,'নেটফ্লিক্স','netfliks',NULL,NULL,NULL,NULL,145,1,0,'নেটফ্লিক্স','Browse নেটফ্লিক্স products.','2026-09-12 01:54:32','2026-09-14 14:39:12',NULL);
INSERT INTO `categories` (`id`, `uuid`, `parent_id`, `name`, `slug`, `description`, `image_path`, `banner_path`, `icon_path`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES (46,'54bc1890-e261-4cbc-bfdc-523def9a6df4',NULL,'প্রোডাক্টিভিটি এবং এডুকেশন','prodaktiviti-ebng-edukesn',NULL,NULL,NULL,NULL,30,1,1,'প্রোডাক্টিভিটি এবং এডুকেশন','Browse প্রোডাক্টিভিটি এবং এডুকেশন products.','2026-09-12 01:54:32','2026-09-14 14:18:24',NULL);
INSERT INTO `categories` (`id`, `uuid`, `parent_id`, `name`, `slug`, `description`, `image_path`, `banner_path`, `icon_path`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES (47,'3ad9ae5e-2909-42f0-80e5-bb0477d2b3b8',NULL,'ভিপিএন','vipien',NULL,NULL,NULL,NULL,147,1,1,'ভিপিএন','Browse ভিপিএন products.','2026-09-12 01:54:32','2026-09-14 14:19:06',NULL);
INSERT INTO `categories` (`id`, `uuid`, `parent_id`, `name`, `slug`, `description`, `image_path`, `banner_path`, `icon_path`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES (48,'a75c700c-fbb1-4052-9c81-66ea820eeb5e',NULL,'মিউজিক','miujik',NULL,NULL,NULL,NULL,46,1,1,'মিউজিক','Browse মিউজিক products.','2026-09-12 01:54:32','2026-09-14 14:18:47',NULL);
INSERT INTO `categories` (`id`, `uuid`, `parent_id`, `name`, `slug`, `description`, `image_path`, `banner_path`, `icon_path`, `sort_order`, `is_active`, `is_featured`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES (49,'a94075f3-b08d-4b7c-ba7c-ba33840d30f3',NULL,'সেরা পণ্য','sera-pnz',NULL,NULL,NULL,NULL,144,1,0,'সেরা পণ্য','Browse সেরা পণ্য products.','2026-09-12 01:54:32','2026-09-14 14:18:52','2026-09-14 14:18:52');
INSERT INTO `brands` (`id`, `uuid`, `name`, `slug`, `logo_path`, `description`, `website`, `is_active`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'ee1fc021-4a95-4afa-9008-77fb9e305536','Core Essentials','core-essentials',NULL,'Demo brand for the single-vendor storefront.',NULL,1,NULL,NULL,'2026-09-11 15:12:27','2026-09-11 15:12:27',NULL);
INSERT INTO `attributes` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES (1,'Screen','screen','2026-09-11 15:12:27','2026-09-15 07:57:17');
INSERT INTO `attributes` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES (2,'Size','size','2026-09-11 15:12:28','2026-09-11 15:12:28');
INSERT INTO `attributes` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES (3,'Storage','storage','2026-09-11 15:12:28','2026-09-11 15:12:28');
INSERT INTO `attributes` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES (4,'Month','month','2026-09-12 06:33:22','2026-09-12 06:33:22');
INSERT INTO `attribute_values` (`id`, `attribute_id`, `value`, `slug`, `created_at`, `updated_at`) VALUES (5,2,'S','s','2026-09-11 15:12:28','2026-09-11 15:12:28');
INSERT INTO `attribute_values` (`id`, `attribute_id`, `value`, `slug`, `created_at`, `updated_at`) VALUES (6,2,'M','m','2026-09-11 15:12:28','2026-09-11 15:12:28');
INSERT INTO `attribute_values` (`id`, `attribute_id`, `value`, `slug`, `created_at`, `updated_at`) VALUES (7,2,'L','l','2026-09-11 15:12:28','2026-09-11 15:12:28');
INSERT INTO `attribute_values` (`id`, `attribute_id`, `value`, `slug`, `created_at`, `updated_at`) VALUES (8,2,'XL','xl','2026-09-11 15:12:28','2026-09-11 15:12:28');
INSERT INTO `attribute_values` (`id`, `attribute_id`, `value`, `slug`, `created_at`, `updated_at`) VALUES (9,3,'64GB','64gb','2026-09-11 15:12:28','2026-09-11 15:12:28');
INSERT INTO `attribute_values` (`id`, `attribute_id`, `value`, `slug`, `created_at`, `updated_at`) VALUES (10,3,'128GB','128gb','2026-09-11 15:12:28','2026-09-11 15:12:28');
INSERT INTO `attribute_values` (`id`, `attribute_id`, `value`, `slug`, `created_at`, `updated_at`) VALUES (11,3,'256GB','256gb','2026-09-11 15:12:28','2026-09-11 15:12:28');
INSERT INTO `attribute_values` (`id`, `attribute_id`, `value`, `slug`, `created_at`, `updated_at`) VALUES (12,4,'1 Month','1-month','2026-09-12 06:33:37','2026-09-12 06:33:37');
INSERT INTO `attribute_values` (`id`, `attribute_id`, `value`, `slug`, `created_at`, `updated_at`) VALUES (13,4,'2 Month','2-month','2026-09-12 06:33:43','2026-09-12 06:33:43');
INSERT INTO `attribute_values` (`id`, `attribute_id`, `value`, `slug`, `created_at`, `updated_at`) VALUES (14,4,'3 Month','3-month','2026-09-12 06:33:53','2026-09-12 06:33:53');
INSERT INTO `attribute_values` (`id`, `attribute_id`, `value`, `slug`, `created_at`, `updated_at`) VALUES (15,1,'1','1','2026-09-15 07:55:26','2026-09-15 07:55:26');
INSERT INTO `attribute_values` (`id`, `attribute_id`, `value`, `slug`, `created_at`, `updated_at`) VALUES (16,1,'2','2','2026-09-15 07:55:31','2026-09-15 07:55:31');
INSERT INTO `attribute_values` (`id`, `attribute_id`, `value`, `slug`, `created_at`, `updated_at`) VALUES (17,1,'3','3','2026-09-15 07:55:35','2026-09-15 07:55:35');
INSERT INTO `attribute_values` (`id`, `attribute_id`, `value`, `slug`, `created_at`, `updated_at`) VALUES (18,1,'4','4','2026-09-15 07:55:39','2026-09-15 07:55:39');
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (203,'097c1834-b2b6-408b-bee9-d157be4f30ac',NULL,34,NULL,'Core Wireless Headphones','core-wireless-headphones','CORE-HEADPHONES',NULL,'simple','piece','Comfortable wireless headphones with clear sound and a long-lasting battery.','Wireless headphones for everyday listening.',NULL,NULL,NULL,2490.00,1990.00,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',1,'2026-09-07 05:00:55','Core Wireless Headphones | Ecommerce Core','Wireless headphones for everyday listening.',NULL,NULL,'2026-09-12 01:54:32','2026-09-14 13:57:17','2026-09-14 13:57:17');
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (204,'439d2a1d-4055-4034-9d67-b6e34c3945c7',NULL,34,NULL,'Smartphone 128GB','smartphone-128gb','CORE-PHONE-128',NULL,'simple','piece','A fast, reliable smartphone with a bright display and 128GB storage.','Reliable smartphone with 128GB storage.',NULL,NULL,NULL,28990.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',1,'2026-09-07 05:00:55','Smartphone 128GB | Ecommerce Core','Reliable smartphone with 128GB storage.',NULL,NULL,'2026-09-12 01:54:32','2026-09-14 13:57:09','2026-09-14 13:57:09');
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (205,'c6e610e2-a2dc-42b8-8d53-a5d224b6ea0c',NULL,35,NULL,'Premium Cotton T-Shirt','premium-cotton-t-shirt','CORE-TSHIRT',NULL,'simple','piece','Soft premium cotton t-shirt with a comfortable regular fit.','Soft cotton t-shirt for everyday wear.',NULL,NULL,NULL,890.00,690.00,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',1,'2026-09-07 05:00:55','Premium Cotton T-Shirt | Ecommerce Core','Soft cotton t-shirt for everyday wear.',NULL,NULL,'2026-09-12 01:54:32','2026-09-14 13:57:22','2026-09-14 13:57:22');
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (206,'11bf5453-ee97-4974-8afe-948d1c035259',NULL,36,NULL,'Minimal Table Lamp','minimal-table-lamp','CORE-LAMP',NULL,'simple','piece','A warm, minimal table lamp for bedrooms, desks, and reading corners.','Warm light for a modern home.',NULL,NULL,NULL,1490.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 05:00:55','Minimal Table Lamp | Ecommerce Core','Warm light for a modern home.',NULL,NULL,'2026-09-12 01:54:32','2026-09-14 13:57:28','2026-09-14 13:57:28');
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (207,'27adebc7-8e21-40ad-a5c0-bbbe7460fa18',NULL,37,NULL,'Daily Care Gift Set','daily-care-gift-set','CORE-CARE-SET',NULL,'simple','piece','A practical daily care gift set for a simple self-care routine.','A thoughtful everyday care gift set.',NULL,NULL,NULL,1790.00,1490.00,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 05:00:55','Daily Care Gift Set | Ecommerce Core','A thoughtful everyday care gift set.',NULL,NULL,'2026-09-12 01:54:32','2026-09-14 13:57:04','2026-09-14 13:57:04');
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (208,'abe4e91f-973e-446f-92fc-1a4c46b243ac',NULL,41,NULL,'জেমিনি','jemini','EFLIX-15677',NULL,'simple','piece','জেমিনি subscription or digital service.','জেমিনি',NULL,NULL,NULL,49.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:13:55','জেমিনি','জেমিনি',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (209,'61735c8e-2465-4705-b4b7-37c4e04c5981',NULL,41,NULL,'ক্লড এআই','kld-eai','EFLIX-15658',NULL,'simple','piece','ক্লড এআই subscription or digital service.','ক্লড এআই',NULL,NULL,NULL,179.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:14:02','ক্লড এআই','ক্লড এআই',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (210,'d8dbc6f2-7ad6-4c67-99f0-8723cb219d33',NULL,41,NULL,'জিপিটি','jipiti','EFLIX-15641',NULL,'simple','piece','জিপিটি subscription or digital service.','জিপিটি',NULL,NULL,NULL,69.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:14:08','জিপিটি','জিপিটি',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (211,'c787ce3a-430a-4fb9-832a-2d6c2d29d4fa',NULL,39,NULL,'রেপ্লিট প্রো ১ মাস','replit-pro-1-mas','EFLIX-13776',NULL,'simple','piece','রেপ্লিট প্রো ১ মাস subscription or digital service.','রেপ্লিট প্রো ১ মাস',NULL,NULL,NULL,499.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:14:11','রেপ্লিট প্রো ১ মাস','রেপ্লিট প্রো ১ মাস',NULL,48,'2026-09-12 01:54:32','2026-09-14 13:57:56',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (212,'835f118d-540c-457d-8949-8e550395c8fe',NULL,46,NULL,'ল্যাভেবল ১ মাস','lzavebl-1-mas','EFLIX-13730',NULL,'simple','piece','ল্যাভেবল ১ মাস subscription or digital service.','ল্যাভেবল ১ মাস',NULL,NULL,NULL,3000.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:14:16','ল্যাভেবল ১ মাস','ল্যাভেবল ১ মাস',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (213,'1705866f-5ec7-4b2b-82c0-3e8ad9b925fd',NULL,43,NULL,'আইক্লাউড','aiklaud','EFLIX-13636',NULL,'simple','piece','আইক্লাউড subscription or digital service.','আইক্লাউড',NULL,NULL,NULL,700.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:14:23','আইক্লাউড','আইক্লাউড',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (214,'fb71e717-af69-42e7-8e45-43b774d5257b',NULL,42,NULL,'আইডিওগ্রাম ১ মাস','aidioogram-1-mas','EFLIX-13618',NULL,'simple','piece','আইডিওগ্রাম ১ মাস subscription or digital service.','আইডিওগ্রাম ১ মাস',NULL,NULL,NULL,450.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:14:30','আইডিওগ্রাম ১ মাস','আইডিওগ্রাম ১ মাস',NULL,48,'2026-09-12 01:54:32','2026-09-14 13:57:42',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (215,'58913da3-2ded-4093-85a4-bb33060c148e',NULL,46,NULL,'ক্যাপকাট প্রো ১ মাস','kzapkat-pro-1-mas','EFLIX-13534',NULL,'simple','piece','ক্যাপকাট প্রো ১ মাস subscription or digital service.','ক্যাপকাট প্রো ১ মাস',NULL,NULL,NULL,850.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:14:36','ক্যাপকাট প্রো ১ মাস','ক্যাপকাট প্রো ১ মাস',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (216,'b628b5cc-be04-44a5-b3c4-b9f0e8f04669',NULL,41,NULL,'চ্যাটজিপিটি গো  অ্যাকাউন্ট ১ বছর','czatjipiti-go-azkaunt-1-bchr','EFLIX-13017',NULL,'simple','piece','চ্যাটজিপিটি গো  অ্যাকাউন্ট ১ বছর subscription or digital service.','চ্যাটজিপিটি গো  অ্যাকাউন্ট ১ বছর',NULL,NULL,NULL,399.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:14:43','চ্যাটজিপিটি গো  অ্যাকাউন্ট ১ বছর','চ্যাটজিপিটি গো  অ্যাকাউন্ট ১ বছর',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (217,'e1f33fee-b1e8-42da-bbf3-57aa8fc391f3',NULL,46,NULL,'২টিবি গুগল ড্রাইভ ও জেমিনি অ্যাডভান্সড ১ বছর','2tibi-gugl-draiv-oo-jemini-azdvansd-1-bchr','EFLIX-12905',NULL,'simple','piece','২টিবি গুগল ড্রাইভ ও জেমিনি অ্যাডভান্সড ১ বছর subscription or digital service.','২টিবি গুগল ড্রাইভ ও জেমিনি অ্যাডভান্সড ১ বছর',NULL,NULL,NULL,2450.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'draft',0,'2026-09-07 13:14:48','২টিবি গুগল ড্রাইভ ও জেমিনি অ্যাডভান্সড ১ বছর','২টিবি গুগল ড্রাইভ ও জেমিনি অ্যাডভান্সড ১ বছর',NULL,48,'2026-09-12 01:54:32','2026-09-15 20:48:52',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (218,'a32b5661-a1cd-45cc-b495-686d1d4024a4',NULL,41,NULL,'জেমিনি অ্যাডভান্সড ১ মাস','jemini-azdvansd-1-mas','EFLIX-11777',NULL,'simple','piece','জেমিনি অ্যাডভান্সড ১ মাস subscription or digital service.','জেমিনি অ্যাডভান্সড ১ মাস',NULL,NULL,NULL,499.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:14:54','জেমিনি অ্যাডভান্সড ১ মাস','জেমিনি অ্যাডভান্সড ১ মাস',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (219,'8a721ce6-e5aa-4803-8111-46ecdc5b57ad',NULL,46,NULL,'গুগল ড্রাইভ ও জেমিনি অ্যাডভান্সড ১০ মাস','gugl-draiv-oo-jemini-azdvansd-10-mas','EFLIX-10650',NULL,'simple','piece','গুগল ড্রাইভ ও জেমিনি অ্যাডভান্সড ১০ মাস subscription or digital service.','গুগল ড্রাইভ ও জেমিনি অ্যাডভান্সড ১০ মাস',NULL,NULL,NULL,2500.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:14:57','গুগল ড্রাইভ ও জেমিনি অ্যাডভান্সড ১০ মাস','গুগল ড্রাইভ ও জেমিনি অ্যাডভান্সড ১০ মাস',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (220,'ed1b5c44-4150-48e4-a21a-78ef798a153f',NULL,41,NULL,'ম্যানুস এআই ১ মাস পার্সোনাল','mzanus-eai-1-mas-parsonal','EFLIX-10147',NULL,'simple','piece','ম্যানুস এআই ১ মাস পার্সোনাল subscription or digital service.','ম্যানুস এআই ১ মাস পার্সোনাল',NULL,NULL,NULL,2049.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:14:59','ম্যানুস এআই ১ মাস পার্সোনাল','ম্যানুস এআই ১ মাস পার্সোনাল',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (221,'02263fbb-f785-4349-adf1-b8c88f41d935',NULL,42,NULL,'কম্বো প্যাক (নেটফ্লিক্স + ইউটিউব প্রিমিয়াম) ১ মাস','kmbo-pzak-netfliks-iutiub-primiyam-1-mas','EFLIX-7830',NULL,'simple','piece','কম্বো প্যাক (নেটফ্লিক্স + ইউটিউব প্রিমিয়াম) ১ মাস subscription or digital service.','কম্বো প্যাক (নেটফ্লিক্স + ইউটিউব প্রিমিয়াম) ১ মাস',NULL,NULL,NULL,399.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'draft',0,'2026-09-07 13:15:04','কম্বো প্যাক (নেটফ্লিক্স + ইউটিউব প্রিমিয়াম) ১ মাস','কম্বো প্যাক (নেটফ্লিক্স + ইউটিউব প্রিমিয়াম) ১ মাস',NULL,48,'2026-09-12 01:54:32','2026-09-14 22:53:01',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (222,'ce9b13fc-856e-4b2b-8707-52a942fa8b64',NULL,47,NULL,'পিআইএ ভিপিএন ৮ মাস','piaie-vipien-8-mas','EFLIX-6824',NULL,'simple','piece','পিআইএ ভিপিএন ৮ মাস subscription or digital service.','পিআইএ ভিপিএন ৮ মাস',NULL,NULL,NULL,250.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:15:09','পিআইএ ভিপিএন ৮ মাস','পিআইএ ভিপিএন ৮ মাস',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (223,'baebc9e7-2bd6-4547-82be-5eb213bbb2f6',NULL,47,NULL,'আভিরা ভিপিএন ৩ মাস','avira-vipien-3-mas','EFLIX-6819',NULL,'simple','piece','আভিরা ভিপিএন ৩ মাস subscription or digital service.','আভিরা ভিপিএন ৩ মাস',NULL,NULL,NULL,149.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:15:13','আভিরা ভিপিএন ৩ মাস','আভিরা ভিপিএন ৩ মাস',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (224,'edf7315d-d6fc-4d70-9b06-7531211caea3',NULL,39,NULL,'টেলিগ্রাম প্রিমিয়াম ১ বছর','teligram-primizam-1-bchr','EFLIX-6693',NULL,'simple','piece','টেলিগ্রাম প্রিমিয়াম ১ বছর subscription or digital service.','টেলিগ্রাম প্রিমিয়াম ১ বছর',NULL,NULL,NULL,4650.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:15:17','টেলিগ্রাম প্রিমিয়াম ১ বছর','টেলিগ্রাম প্রিমিয়াম ১ বছর',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (225,'273d1ea1-4c65-4b8a-bf6b-899847e02a4a',NULL,47,NULL,'নর্ড ভিপিএন','nrd-vipien','EFLIX-6688',NULL,'simple','piece','নর্ড ভিপিএন subscription or digital service.','নর্ড ভিপিএন',NULL,NULL,NULL,149.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:15:22','নর্ড ভিপিএন','নর্ড ভিপিএন',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (226,'543520a3-31d5-4ab2-a775-4678550161d9',NULL,39,NULL,'ট্রু কলার ১ বছর','tru-klar-1-bchr','EFLIX-6686',NULL,'simple','piece','ট্রু কলার ১ বছর subscription or digital service.','ট্রু কলার ১ বছর',NULL,NULL,NULL,1399.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:15:28','ট্রু কলার ১ বছর','ট্রু কলার ১ বছর',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (227,'507aea9d-95bd-4613-a51d-3a1a9ae43b3c',NULL,39,NULL,'আই ডি এম লাইফ টাইম','ai-di-em-laif-taim','EFLIX-6684',NULL,'simple','piece','আই ডি এম লাইফ টাইম subscription or digital service.','আই ডি এম লাইফ টাইম',NULL,NULL,NULL,2450.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:15:34','আই ডি এম লাইফ টাইম','আই ডি এম লাইফ টাইম',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (228,'0e5bf016-8a55-4a07-8611-bdde613be8e3',NULL,48,NULL,'রুন মিউজিক ১ মাস','run-miujik-1-mas','EFLIX-6679',NULL,'simple','piece','রুন মিউজিক ১ মাস subscription or digital service.','রুন মিউজিক ১ মাস',NULL,NULL,NULL,1250.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:15:40','রুন মিউজিক ১ মাস','রুন মিউজিক ১ মাস',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (229,'2c727057-69d1-4d93-b6a9-7415a57bf42e',NULL,48,NULL,'সং স্টার প্লাস ১ মাস','sng-star-plas-1-mas','EFLIX-6676',NULL,'simple','piece','সং স্টার প্লাস ১ মাস subscription or digital service.','সং স্টার প্লাস ১ মাস',NULL,NULL,NULL,650.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:15:46','সং স্টার প্লাস ১ মাস','সং স্টার প্লাস ১ মাস',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (230,'87492778-5a42-4ac6-98e8-74e674f831d2',NULL,46,NULL,'হ্যালোটক','hzalotk','EFLIX-6672',NULL,'simple','piece','হ্যালোটক subscription or digital service.','হ্যালোটক',NULL,NULL,NULL,1300.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:15:52','হ্যালোটক','হ্যালোটক',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (231,'d1ab45f4-40d0-432a-8632-3d36f7e38b3a',NULL,46,NULL,'টানটান','tantan','EFLIX-6668',NULL,'simple','piece','টানটান subscription or digital service.','টানটান',NULL,NULL,NULL,399.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:15:58','টানটান','টানটান',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (232,'924148d2-46aa-4775-afab-07fe98aa91da',NULL,46,NULL,'টিন্ডার ১ মাস','tindar-1-mas','EFLIX-6666',NULL,'simple','piece','টিন্ডার ১ মাস subscription or digital service.','টিন্ডার ১ মাস',NULL,NULL,NULL,990.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:16:05','টিন্ডার ১ মাস','টিন্ডার ১ মাস',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (233,'948465ca-6c98-45f8-9e58-9791bfc261cf',NULL,46,NULL,'ফ্লাইট রাডার বিজনেস প্রিমিয়াম ১ মাস','flait-radar-bijnes-primizam-1-mas','EFLIX-6664',NULL,'simple','piece','ফ্লাইট রাডার বিজনেস প্রিমিয়াম ১ মাস subscription or digital service.','ফ্লাইট রাডার বিজনেস প্রিমিয়াম ১ মাস',NULL,NULL,NULL,650.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:16:11','ফ্লাইট রাডার বিজনেস প্রিমিয়াম ১ মাস','ফ্লাইট রাডার বিজনেস প্রিমিয়াম ১ মাস',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (234,'e4588c71-11bc-4d89-9db2-24d5d6552f9f',NULL,46,NULL,'আলো মুভস প্রিমিয়াম ২ মাস','alo-muvs-primizam-2-mas','EFLIX-6662',NULL,'simple','piece','আলো মুভস প্রিমিয়াম ২ মাস subscription or digital service.','আলো মুভস প্রিমিয়াম ২ মাস',NULL,NULL,NULL,800.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:16:15','আলো মুভস প্রিমিয়াম ২ মাস','আলো মুভস প্রিমিয়াম ২ মাস',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (235,'667b82fe-36c9-4bb8-bc56-35b95b92813c',NULL,46,NULL,'ব্লিঙ্কিস্ট প্রিমিয়াম','blinkist-primizam','EFLIX-6656',NULL,'simple','piece','ব্লিঙ্কিস্ট প্রিমিয়াম subscription or digital service.','ব্লিঙ্কিস্ট প্রিমিয়াম',NULL,NULL,NULL,140.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:16:21','ব্লিঙ্কিস্ট প্রিমিয়াম','ব্লিঙ্কিস্ট প্রিমিয়াম',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (236,'70982260-cb97-4c09-9380-93d50425324f',NULL,46,NULL,'কাহুট! ১ মাস','kahut-1-mas','EFLIX-6654',NULL,'simple','piece','কাহুট! ১ মাস subscription or digital service.','কাহুট! ১ মাস',NULL,NULL,NULL,1600.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:16:28','কাহুট! ১ মাস','কাহুট! ১ মাস',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (237,'5b2c50d8-3736-4a93-a807-11e3e9f549b5',NULL,46,NULL,'মাইন্ডভ্যালি ১ মাস','maindvzali-1-mas','EFLIX-6652',NULL,'simple','piece','মাইন্ডভ্যালি ১ মাস subscription or digital service.','মাইন্ডভ্যালি ১ মাস',NULL,NULL,NULL,500.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:16:32','মাইন্ডভ্যালি ১ মাস','মাইন্ডভ্যালি ১ মাস',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (238,'169a2eac-dacf-4b00-ba1e-41eafa8e5ff2',NULL,46,NULL,'প্রেস রিডার','pres-ridar','EFLIX-6648',NULL,'simple','piece','প্রেস রিডার subscription or digital service.','প্রেস রিডার',NULL,NULL,NULL,800.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:16:36','প্রেস রিডার','প্রেস রিডার',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (239,'e7a2c791-9a85-4f78-8adb-3a0e915c1cae',NULL,46,NULL,'চেস.কম ১ মাস','ceskm-1-mas','EFLIX-6646',NULL,'simple','piece','চেস.কম ১ মাস subscription or digital service.','চেস.কম ১ মাস',NULL,NULL,NULL,630.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:16:40','চেস.কম ১ মাস','চেস.কম ১ মাস',NULL,NULL,'2026-09-12 01:54:32','2026-09-12 01:54:32',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (240,'7ed68c5b-00a7-4bef-b912-945f7024b89e',NULL,46,NULL,'ফরচুন প্রো ১ মাস','frcun-pro-1-mas','EFLIX-6644',NULL,'simple','piece','ফরচুন প্রো ১ মাস subscription or digital service.','ফরচুন প্রো ১ মাস',NULL,NULL,NULL,400.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:16:45','ফরচুন প্রো ১ মাস','ফরচুন প্রো ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (241,'6f488565-14ee-43cd-a3b7-805c3576ea48',NULL,46,NULL,'এক্সমাইন্ড প্রো ১ বছর','xmaind-pro-1-bchr','EFLIX-6642',NULL,'simple','piece','এক্সমাইন্ড প্রো ১ বছর subscription or digital service.','এক্সমাইন্ড প্রো ১ বছর',NULL,NULL,NULL,990.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:16:51','এক্সমাইন্ড প্রো ১ বছর','এক্সমাইন্ড প্রো ১ বছর',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (242,'aa0c91b2-afc1-45f3-a142-671c0b7df54f',NULL,46,NULL,'টুনলি এন্টারপ্রাইজ ১ মাস','tunli-entarpraij-1-mas','EFLIX-6638',NULL,'simple','piece','টুনলি এন্টারপ্রাইজ ১ মাস subscription or digital service.','টুনলি এন্টারপ্রাইজ ১ মাস',NULL,NULL,NULL,2050.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:16:57','টুনলি এন্টারপ্রাইজ ১ মাস','টুনলি এন্টারপ্রাইজ ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (243,'c47dc3d1-c742-4dae-9799-88a16183e674',NULL,46,NULL,'অ্যাডোবি এক্সপ্রেস ১ মাস','azdobi-xpres-1-mas','EFLIX-6616',NULL,'simple','piece','অ্যাডোবি এক্সপ্রেস ১ মাস subscription or digital service.','অ্যাডোবি এক্সপ্রেস ১ মাস',NULL,NULL,NULL,350.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:17:03','অ্যাডোবি এক্সপ্রেস ১ মাস','অ্যাডোবি এক্সপ্রেস ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (244,'9ae3f4c4-b93a-44be-a7c0-452fcea84210',NULL,46,NULL,'অ্যাডোবি ক্রিয়েটিভ ক্লাউড','azdobi-krizetiv-klaud','EFLIX-6614',NULL,'simple','piece','অ্যাডোবি ক্রিয়েটিভ ক্লাউড subscription or digital service.','অ্যাডোবি ক্রিয়েটিভ ক্লাউড',NULL,NULL,NULL,3000.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:17:08','অ্যাডোবি ক্রিয়েটিভ ক্লাউড','অ্যাডোবি ক্রিয়েটিভ ক্লাউড',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (245,'fa9c38e1-1073-4164-bfc9-714250eeb399',NULL,46,NULL,'গিটহাব কপিলট ১ মাস','githab-kpilt-1-mas','EFLIX-6612',NULL,'simple','piece','গিটহাব কপিলট ১ মাস subscription or digital service.','গিটহাব কপিলট ১ মাস',NULL,NULL,NULL,480.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:17:14','গিটহাব কপিলট ১ মাস','গিটহাব কপিলট ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (246,'b2258af9-0dda-4f54-82d6-0b57f9ff62f5',NULL,46,NULL,'আইকনফাইন্ডার ১ মাস','aiknfaindar-1-mas','EFLIX-6608',NULL,'simple','piece','আইকনফাইন্ডার ১ মাস subscription or digital service.','আইকনফাইন্ডার ১ মাস',NULL,NULL,NULL,650.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:17:20','আইকনফাইন্ডার ১ মাস','আইকনফাইন্ডার ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (247,'88982645-95d2-4c96-9a64-034c43f04a44',NULL,46,NULL,'স্ক্রিমিং ফ্রগ ১ বছর','skriming-frg-1-bchr','EFLIX-6606',NULL,'simple','piece','স্ক্রিমিং ফ্রগ ১ বছর subscription or digital service.','স্ক্রিমিং ফ্রগ ১ বছর',NULL,NULL,NULL,1070.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:17:26','স্ক্রিমিং ফ্রগ ১ বছর','স্ক্রিমিং ফ্রগ ১ বছর',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (248,'cb212198-1d5a-4d95-b305-2f13df88ce35',NULL,46,NULL,'বিগস্পাই প্রো ১ মাস','bigspai-pro-1-mas','EFLIX-6604',NULL,'simple','piece','বিগস্পাই প্রো ১ মাস subscription or digital service.','বিগস্পাই প্রো ১ মাস',NULL,NULL,NULL,750.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:17:32','বিগস্পাই প্রো ১ মাস','বিগস্পাই প্রো ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (249,'d07d6923-e161-44d9-a2f1-eda040cc77e2',NULL,46,NULL,'প্লেএইচটি ক্রিয়েটর ১ মাস','pleeicti-krizetr-1-mas','EFLIX-6602',NULL,'simple','piece','প্লেএইচটি ক্রিয়েটর ১ মাস subscription or digital service.','প্লেএইচটি ক্রিয়েটর ১ মাস',NULL,NULL,NULL,620.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:17:38','প্লেএইচটি ক্রিয়েটর ১ মাস','প্লেএইচটি ক্রিয়েটর ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (250,'f8d950a3-c2c6-4042-b720-b61717c48c0d',NULL,46,NULL,'ওয়েভ.ভিডিও ক্রিয়েটর ১ মাস','oozevvidioo-krizetr-1-mas','EFLIX-6596',NULL,'simple','piece','ওয়েভ.ভিডিও ক্রিয়েটর ১ মাস subscription or digital service.','ওয়েভ.ভিডিও ক্রিয়েটর ১ মাস',NULL,NULL,NULL,620.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:17:42','ওয়েভ.ভিডিও ক্রিয়েটর ১ মাস','ওয়েভ.ভিডিও ক্রিয়েটর ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (251,'3f5c8786-e8cb-4263-9c06-20cbf68e2328',NULL,46,NULL,'মার্কেটমিউজ ১ মাস','marketmiuj-1-mas','EFLIX-6594',NULL,'simple','piece','মার্কেটমিউজ ১ মাস subscription or digital service.','মার্কেটমিউজ ১ মাস',NULL,NULL,NULL,860.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:17:48','মার্কেটমিউজ ১ মাস','মার্কেটমিউজ ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (252,'133a3c18-9f0c-46fb-889e-242eda2bea1d',NULL,46,NULL,'হুটসুইট ১ মাস','hutsuit-1-mas','EFLIX-6587',NULL,'simple','piece','হুটসুইট ১ মাস subscription or digital service.','হুটসুইট ১ মাস',NULL,NULL,NULL,780.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:17:53','হুটসুইট ১ মাস','হুটসুইট ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (253,'bf84643c-8e3c-436d-8e66-7ebd7ea7c5c3',NULL,46,NULL,'অরিজিনালিটি এআই ১ মাস','orijinaliti-eai-1-mas','EFLIX-6585',NULL,'simple','piece','অরিজিনালিটি এআই ১ মাস subscription or digital service.','অরিজিনালিটি এআই ১ মাস',NULL,NULL,NULL,550.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:18:00','অরিজিনালিটি এআই ১ মাস','অরিজিনালিটি এআই ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (254,'6df4d071-d300-425b-84a7-abca46deacb7',NULL,46,NULL,'ফিলমোরা ১৩ লাইফ টাইম','filmora-13-laif-taim','EFLIX-6583',NULL,'simple','piece','ফিলমোরা ১৩ লাইফ টাইম subscription or digital service.','ফিলমোরা ১৩ লাইফ টাইম',NULL,NULL,NULL,1470.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:18:06','ফিলমোরা ১৩ লাইফ টাইম','ফিলমোরা ১৩ লাইফ টাইম',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (255,'391e9363-6067-40a7-bedf-da377dddc5e8',NULL,46,NULL,'ট্রাই হ্যাক মি ১ মাস','trai-hzak-mi-1-mas','EFLIX-6580',NULL,'simple','piece','ট্রাই হ্যাক মি ১ মাস subscription or digital service.','ট্রাই হ্যাক মি ১ মাস',NULL,NULL,NULL,480.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:18:12','ট্রাই হ্যাক মি ১ মাস','ট্রাই হ্যাক মি ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (256,'7a875265-9111-424c-bf51-1b567933e983',NULL,46,NULL,'ফ্রীপিক ১ মাস','freepik-1-mas','EFLIX-6576',NULL,'simple','piece','ফ্রীপিক ১ মাস subscription or digital service.','ফ্রীপিক ১ মাস',NULL,NULL,NULL,780.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:18:19','ফ্রীপিক ১ মাস','ফ্রীপিক ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (257,'01b60014-ef13-4f11-bca6-299a655f3495',NULL,46,NULL,'এনভাটো এলিমেন্টস ১ মাস','envato-eliments-1-mas','EFLIX-6570',NULL,'simple','piece','এনভাটো এলিমেন্টস ১ মাস subscription or digital service.','এনভাটো এলিমেন্টস ১ মাস',NULL,NULL,NULL,780.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:18:22','এনভাটো এলিমেন্টস ১ মাস','এনভাটো এলিমেন্টস ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (258,'912ef832-c8e1-4d96-a842-853c4570b87e',NULL,46,NULL,'ইলেভেন ল্যাবস ১ মাস','ileven-lzabs-1-mas','EFLIX-6566',NULL,'simple','piece','ইলেভেন ল্যাবস ১ মাস subscription or digital service.','ইলেভেন ল্যাবস ১ মাস',NULL,NULL,NULL,780.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:18:29','ইলেভেন ল্যাবস ১ মাস','ইলেভেন ল্যাবস ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (259,'34feeabe-ce93-42eb-8ec3-78e9b47b4ddf',NULL,46,NULL,'ভিড আই কিউ ১ মাস','vid-ai-kiu-1-mas','EFLIX-6561',NULL,'simple','piece','ভিড আই কিউ ১ মাস subscription or digital service.','ভিড আই কিউ ১ মাস',NULL,NULL,NULL,550.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:18:35','ভিড আই কিউ ১ মাস','ভিড আই কিউ ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (260,'b65adfa1-d675-4b67-94a2-0796ce5b56b1',NULL,46,NULL,'পিএনজি ট্রি ১ বছর','pienji-tri-1-bchr','EFLIX-6558',NULL,'simple','piece','পিএনজি ট্রি ১ বছর subscription or digital service.','পিএনজি ট্রি ১ বছর',NULL,NULL,NULL,1450.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:18:41','পিএনজি ট্রি ১ বছর','পিএনজি ট্রি ১ বছর',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (261,'877eecc0-0d0b-44c6-a43b-94af3dded5e4',NULL,46,NULL,'মাইকমনস্টার ৩ মাস','maikmnstar-3-mas','EFLIX-6555',NULL,'simple','piece','মাইকমনস্টার ৩ মাস subscription or digital service.','মাইকমনস্টার ৩ মাস',NULL,NULL,NULL,700.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:18:48','মাইকমনস্টার ৩ মাস','মাইকমনস্টার ৩ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (262,'7d8b9b73-5163-4bfb-b9b1-39796f7b4464',NULL,46,NULL,'ওয়েলসেড ল্যাবস, মেকার ১ মাস','oozelsed-lzabs-mekar-1-mas','EFLIX-6553',NULL,'simple','piece','ওয়েলসেড ল্যাবস, মেকার ১ মাস subscription or digital service.','ওয়েলসেড ল্যাবস, মেকার ১ মাস',NULL,NULL,NULL,570.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:18:54','ওয়েলসেড ল্যাবস, মেকার ১ মাস','ওয়েলসেড ল্যাবস, মেকার ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (263,'fd606b54-7ddc-4b82-a4a1-bc5f1e5d142b',NULL,46,NULL,'হিলিয়াম 10 ১ মাস','hilizam-10-1-mas','EFLIX-6551',NULL,'simple','piece','হিলিয়াম 10 ১ মাস subscription or digital service.','হিলিয়াম 10 ১ মাস',NULL,NULL,NULL,600.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:19:00','হিলিয়াম 10 ১ মাস','হিলিয়াম 10 ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (264,'5feee7e9-8a05-4086-81e4-1262a3ea9d51',NULL,46,NULL,'কিটল ১ মাস','kitl-1-mas','EFLIX-6547',NULL,'simple','piece','কিটল ১ মাস subscription or digital service.','কিটল ১ মাস',NULL,NULL,NULL,820.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:19:04','কিটল ১ মাস','কিটল ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (265,'947d2387-bcee-4480-bdc0-1ecae77e1003',NULL,46,NULL,'ফিগমা ১ মাস','figma-1-mas','EFLIX-6544',NULL,'simple','piece','ফিগমা ১ মাস subscription or digital service.','ফিগমা ১ মাস',NULL,NULL,NULL,850.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:19:10','ফিগমা ১ মাস','ফিগমা ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (266,'4d4e84f6-10e5-460c-9ded-ac3b5193365e',NULL,46,NULL,'মোজ প্রো ১ মাস','moj-pro-1-mas','EFLIX-6541',NULL,'simple','piece','মোজ প্রো ১ মাস subscription or digital service.','মোজ প্রো ১ মাস',NULL,NULL,NULL,550.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:19:14','মোজ প্রো ১ মাস','মোজ প্রো ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (267,'38ccc67b-edfe-4c71-be5a-9936016be0d8',NULL,46,NULL,'রাইটসোনিক ১ মাস','raitsonik-1-mas','EFLIX-6539',NULL,'simple','piece','রাইটসোনিক ১ মাস subscription or digital service.','রাইটসোনিক ১ মাস',NULL,NULL,NULL,430.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:19:19','রাইটসোনিক ১ মাস','রাইটসোনিক ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (268,'de695d76-2649-4ab6-ab58-5cefdf4c9b60',NULL,46,NULL,'ফ্ল্যাটিকন পার্সোনাল ১ মাস','flzatikn-parsonal-1-mas','EFLIX-6460',NULL,'simple','piece','ফ্ল্যাটিকন পার্সোনাল ১ মাস subscription or digital service.','ফ্ল্যাটিকন পার্সোনাল ১ মাস',NULL,NULL,NULL,550.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:19:25','ফ্ল্যাটিকন পার্সোনাল ১ মাস','ফ্ল্যাটিকন পার্সোনাল ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (269,'cd2fed70-642f-41cc-9f89-cc8a32b5450b',NULL,46,NULL,'ফ্রিপিক পার্সোনাল ১ মাস','fripik-parsonal-1-mas','EFLIX-6458',NULL,'simple','piece','ফ্রিপিক পার্সোনাল ১ মাস subscription or digital service.','ফ্রিপিক পার্সোনাল ১ মাস',NULL,NULL,NULL,700.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:19:31','ফ্রিপিক পার্সোনাল ১ মাস','ফ্রিপিক পার্সোনাল ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (270,'7b69ac3f-04bf-4f66-ad7e-7999b591a549',NULL,46,NULL,'অল উইন্ডোজ কী','ol-uindoj-kee','EFLIX-6456',NULL,'simple','piece','অল উইন্ডোজ কী subscription or digital service.','অল উইন্ডোজ কী',NULL,NULL,NULL,470.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:19:34','অল উইন্ডোজ কী','অল উইন্ডোজ কী',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (271,'4377897b-64ad-43ad-bbef-4b64f6aaf586',NULL,46,NULL,'রিজুম কোচ ১ মাস','rijum-koc-1-mas','EFLIX-6454',NULL,'simple','piece','রিজুম কোচ ১ মাস subscription or digital service.','রিজুম কোচ ১ মাস',NULL,NULL,NULL,550.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:19:38','রিজুম কোচ ১ মাস','রিজুম কোচ ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (272,'a4ba9286-ec47-429e-920e-4a96c67f1f04',NULL,46,NULL,'স্টকচার্ট ১ মাস','stkcart-1-mas','EFLIX-6452',NULL,'simple','piece','স্টকচার্ট ১ মাস subscription or digital service.','স্টকচার্ট ১ মাস',NULL,NULL,NULL,660.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:19:44','স্টকচার্ট ১ মাস','স্টকচার্ট ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (273,'0e150a12-04ab-402a-a744-73822c4e129a',NULL,46,NULL,'তাবনিন প্রো ১৪ দিন','tabnin-pro-14-din','EFLIX-6428',NULL,'simple','piece','তাবনিন প্রো ১৪ দিন subscription or digital service.','তাবনিন প্রো ১৪ দিন',NULL,NULL,NULL,180.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:19:49','তাবনিন প্রো ১৪ দিন','তাবনিন প্রো ১৪ দিন',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (274,'33c40b5c-f071-461a-8a70-c992fa1e1f5b',NULL,46,NULL,'ব্রাইন.এফএম ৩ মাস','brainefem-3-mas','EFLIX-6426',NULL,'simple','piece','ব্রাইন.এফএম ৩ মাস subscription or digital service.','ব্রাইন.এফএম ৩ মাস',NULL,NULL,NULL,1250.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:19:55','ব্রাইন.এফএম ৩ মাস','ব্রাইন.এফএম ৩ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (275,'33ee2b71-1ff1-41f0-9e40-ab4b207b77a5',NULL,46,NULL,'ওভারলিফ','oovarlif','EFLIX-6424',NULL,'simple','piece','ওভারলিফ subscription or digital service.','ওভারলিফ',NULL,NULL,NULL,280.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:20:02','ওভারলিফ','ওভারলিফ',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (276,'60ccc56b-57aa-4755-9f47-88763dadf864',NULL,46,NULL,'ক্রাঞ্চবেস ১ মাস','krancbes-1-mas','EFLIX-6422',NULL,'simple','piece','ক্রাঞ্চবেস ১ মাস subscription or digital service.','ক্রাঞ্চবেস ১ মাস',NULL,NULL,NULL,780.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:20:08','ক্রাঞ্চবেস ১ মাস','ক্রাঞ্চবেস ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (277,'1276ca0c-3489-41eb-b142-e144ffb63d40',NULL,46,NULL,'প্রেজি ১ মাস','preji-1-mas','EFLIX-6419',NULL,'simple','piece','প্রেজি ১ মাস subscription or digital service.','প্রেজি ১ মাস',NULL,NULL,NULL,820.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:20:15','প্রেজি ১ মাস','প্রেজি ১ মাস',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (278,'da937158-4925-4f39-848b-b9edf818fa50',NULL,46,NULL,'বায়োরেন্ডার ১৪ দিন','bazorendar-14-din','EFLIX-6417',NULL,'simple','piece','বায়োরেন্ডার ১৪ দিন subscription or digital service.','বায়োরেন্ডার ১৪ দিন',NULL,NULL,NULL,550.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:20:20','বায়োরেন্ডার ১৪ দিন','বায়োরেন্ডার ১৪ দিন',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (279,'d7100210-dd2d-4f51-8b7d-c4d396f58965',NULL,46,NULL,'ডিজাইন.এআই ৭ দিন','dijaineai-7-din','EFLIX-6415',NULL,'simple','piece','ডিজাইন.এআই ৭ দিন subscription or digital service.','ডিজাইন.এআই ৭ দিন',NULL,NULL,NULL,195.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:20:26','ডিজাইন.এআই ৭ দিন','ডিজাইন.এআই ৭ দিন',NULL,NULL,'2026-09-12 01:54:33','2026-09-12 01:54:33',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (280,'a73aed2a-0946-4423-acd1-c82e23793f98',NULL,46,NULL,'লিঙ্কডইন প্রিমিয়াম পার্সোনাল','linkdin-primizam-parsonal','EFLIX-6394',NULL,'simple','piece','লিঙ্কডইন প্রিমিয়াম পার্সোনাল subscription or digital service.','লিঙ্কডইন প্রিমিয়াম পার্সোনাল',NULL,NULL,NULL,2950.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:20:32','লিঙ্কডইন প্রিমিয়াম পার্সোনাল','লিঙ্কডইন প্রিমিয়াম পার্সোনাল',NULL,NULL,'2026-09-12 01:54:34','2026-09-12 01:54:34',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (281,'bb2a0ab9-bfa7-4e47-8343-44e5f18b7974',NULL,41,NULL,'আর ওয়াই টি আর ১ মাস','ar-oozai-ti-ar-1-mas','EFLIX-6388',NULL,'simple','piece','আর ওয়াই টি আর ১ মাস subscription or digital service.','আর ওয়াই টি আর ১ মাস',NULL,NULL,NULL,790.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:20:38','আর ওয়াই টি আর ১ মাস','আর ওয়াই টি আর ১ মাস',NULL,NULL,'2026-09-12 01:54:34','2026-09-12 01:54:34',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (282,'7dd6c091-cfbf-44bb-a4b0-30c8f2060d99',NULL,41,NULL,'ব্রেইনলি প্লাস ১ মাস','breinli-plas-1-mas','EFLIX-6385',NULL,'simple','piece','ব্রেইনলি প্লাস ১ মাস subscription or digital service.','ব্রেইনলি প্লাস ১ মাস',NULL,NULL,NULL,200.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:20:44','ব্রেইনলি প্লাস ১ মাস','ব্রেইনলি প্লাস ১ মাস',NULL,NULL,'2026-09-12 01:54:35','2026-09-12 01:54:35',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (283,'aad668e7-d591-4716-9206-1cbbecbe3724',NULL,41,NULL,'স্পিচিফাই','spicifai','EFLIX-6373',NULL,'simple','piece','স্পিচিফাই subscription or digital service.','স্পিচিফাই',NULL,NULL,NULL,370.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:20:50','স্পিচিফাই','স্পিচিফাই',NULL,NULL,'2026-09-12 01:54:35','2026-09-12 01:54:35',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (284,'10ef28d0-d93f-4b1d-8ff2-093bc71f9f70',NULL,41,NULL,'টোম এআই ১ মাস','tom-eai-1-mas','EFLIX-6371',NULL,'simple','piece','টোম এআই ১ মাস subscription or digital service.','টোম এআই ১ মাস',NULL,NULL,NULL,390.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:20:55','টোম এআই ১ মাস','টোম এআই ১ মাস',NULL,NULL,'2026-09-12 01:54:36','2026-09-12 01:54:36',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (285,'a0441bd2-f165-41f9-b837-52c77f900fa9',NULL,41,NULL,'স্পেসলি এআই ৩ দিন','spesli-eai-3-din','EFLIX-6369',NULL,'simple','piece','স্পেসলি এআই ৩ দিন subscription or digital service.','স্পেসলি এআই ৩ দিন',NULL,NULL,NULL,480.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:21:01','স্পেসলি এআই ৩ দিন','স্পেসলি এআই ৩ দিন',NULL,NULL,'2026-09-12 01:54:38','2026-09-12 01:54:38',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (286,'26e1f440-dc01-4854-8cde-66e29d512bde',NULL,41,NULL,'সাইডার এআই ১ মাস','saidar-eai-1-mas','EFLIX-6365',NULL,'simple','piece','সাইডার এআই ১ মাস subscription or digital service.','সাইডার এআই ১ মাস',NULL,NULL,NULL,350.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:21:07','সাইডার এআই ১ মাস','সাইডার এআই ১ মাস',NULL,NULL,'2026-09-12 01:54:40','2026-09-12 01:54:40',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (287,'58890353-a01a-4dbc-8b96-4c0aa789e8a0',NULL,41,NULL,'লিটচার্ট এ প্লাস ১ মাস','litcart-e-plas-1-mas','EFLIX-6363',NULL,'simple','piece','লিটচার্ট এ প্লাস ১ মাস subscription or digital service.','লিটচার্ট এ প্লাস ১ মাস',NULL,NULL,NULL,660.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:21:12','লিটচার্ট এ প্লাস ১ মাস','লিটচার্ট এ প্লাস ১ মাস',NULL,NULL,'2026-09-12 01:54:40','2026-09-12 01:54:40',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (288,'3671b480-19dc-4670-bdd0-8d0686355846',NULL,41,NULL,'জেনি এআই ১ মাস','jeni-eai-1-mas','EFLIX-6361',NULL,'simple','piece','জেনি এআই ১ মাস subscription or digital service.','জেনি এআই ১ মাস',NULL,NULL,NULL,650.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:21:18','জেনি এআই ১ মাস','জেনি এআই ১ মাস',NULL,NULL,'2026-09-12 01:54:41','2026-09-12 01:54:41',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (289,'c4eb4980-d461-49bd-8160-6dc6e194c37e',NULL,41,NULL,'ফটোরুম প্রো ১ মাস','ftorum-pro-1-mas','EFLIX-6359',NULL,'simple','piece','ফটোরুম প্রো ১ মাস subscription or digital service.','ফটোরুম প্রো ১ মাস',NULL,NULL,NULL,550.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:21:22','ফটোরুম প্রো ১ মাস','ফটোরুম প্রো ১ মাস',NULL,NULL,'2026-09-12 01:54:42','2026-09-12 01:54:42',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (290,'b39bfcfe-be4b-4a7c-b966-eaf88bf6b2fd',NULL,41,NULL,'হুমাটা এআই ১৪ দিন ৫০০ পেজ','humata-eai-14-din-500-pej','EFLIX-6103',NULL,'simple','piece','হুমাটা এআই ১৪ দিন ৫০০ পেজ subscription or digital service.','হুমাটা এআই ১৪ দিন ৫০০ পেজ',NULL,NULL,NULL,650.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:21:25','হুমাটা এআই ১৪ দিন ৫০০ পেজ','হুমাটা এআই ১৪ দিন ৫০০ পেজ',NULL,NULL,'2026-09-12 01:54:43','2026-09-12 01:54:43',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (291,'b7f08248-b15d-4734-ac17-c60b706a9d50',NULL,41,NULL,'অথর এআই ১ মাস','othr-eai-1-mas','EFLIX-6099',NULL,'simple','piece','অথর এআই ১ মাস subscription or digital service.','অথর এআই ১ মাস',NULL,NULL,NULL,880.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:21:28','অথর এআই ১ মাস','অথর এআই ১ মাস',NULL,NULL,'2026-09-12 01:54:43','2026-09-12 01:54:43',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (292,'a5201100-51e0-47ce-9d1f-6d6b1788b032',NULL,41,NULL,'ক্রিয়েটিভ ফ্যাব্রিকা','krizetiv-fzabrika','EFLIX-6095',NULL,'simple','piece','ক্রিয়েটিভ ফ্যাব্রিকা subscription or digital service.','ক্রিয়েটিভ ফ্যাব্রিকা',NULL,NULL,NULL,380.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:21:32','ক্রিয়েটিভ ফ্যাব্রিকা','ক্রিয়েটিভ ফ্যাব্রিকা',NULL,NULL,'2026-09-12 01:54:45','2026-09-12 01:54:45',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (293,'7fadd9a4-fd3e-4a4f-8989-e544c2f2095f',NULL,41,NULL,'ক্রিস্প এআই ১ মাস','krisp-eai-1-mas','EFLIX-6093',NULL,'simple','piece','ক্রিস্প এআই ১ মাস subscription or digital service.','ক্রিস্প এআই ১ মাস',NULL,NULL,NULL,660.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:21:38','ক্রিস্প এআই ১ মাস','ক্রিস্প এআই ১ মাস',NULL,NULL,'2026-09-12 01:54:46','2026-09-12 01:54:46',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (294,'3b811a9d-ee98-4a4d-8e9e-1278fa66f6a7',NULL,41,NULL,'ক্রেয়া এআই ১ মাস','kreya-eai-1-mas','EFLIX-6090',NULL,'simple','piece','ক্রেয়া এআই ১ মাস subscription or digital service.','ক্রেয়া এআই ১ মাস',NULL,NULL,NULL,660.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:21:44','ক্রেয়া এআই ১ মাস','ক্রেয়া এআই ১ মাস',NULL,NULL,'2026-09-12 01:54:46','2026-09-12 01:54:46',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (295,'3d646f90-b3ac-45ec-a734-3bed1a38a1e0',NULL,41,NULL,'বাইপাস এআই','baipas-eai','EFLIX-6060',NULL,'simple','piece','বাইপাস এআই subscription or digital service.','বাইপাস এআই',NULL,NULL,NULL,200.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:21:49','বাইপাস এআই','বাইপাস এআই',NULL,NULL,'2026-09-12 01:54:47','2026-09-12 01:54:47',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (296,'5151042f-5d2b-4458-9309-67f2902a36b3',NULL,41,NULL,'অ্যাডোব ফায়ারফ্লাইন ১ মাস','azdob-fazarflain-1-mas','EFLIX-6058',NULL,'simple','piece','অ্যাডোব ফায়ারফ্লাইন ১ মাস subscription or digital service.','অ্যাডোব ফায়ারফ্লাইন ১ মাস',NULL,NULL,NULL,350.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:21:53','অ্যাডোব ফায়ারফ্লাইন ১ মাস','অ্যাডোব ফায়ারফ্লাইন ১ মাস',NULL,NULL,'2026-09-12 01:54:48','2026-09-12 01:54:48',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (297,'b9a3908b-064c-44f8-8756-dfae1c6234b8',NULL,41,NULL,'ক্রাফট প্রো ১ বছর','kraft-pro-1-bchr','EFLIX-6054',NULL,'simple','piece','ক্রাফট প্রো ১ বছর subscription or digital service.','ক্রাফট প্রো ১ বছর',NULL,NULL,NULL,490.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:21:57','ক্রাফট প্রো ১ বছর','ক্রাফট প্রো ১ বছর',NULL,NULL,'2026-09-12 01:54:50','2026-09-12 01:54:50',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (298,'7fcf9785-c179-4c96-8771-af9cc7089c6f',NULL,41,NULL,'নেবুলা প্রিমিয়াম','nebula-primiyam','EFLIX-6049',NULL,'simple','piece','নেবুলা প্রিমিয়াম subscription or digital service.','নেবুলা প্রিমিয়াম',NULL,NULL,NULL,1500.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:22:04','নেবুলা প্রিমিয়াম','নেবুলা প্রিমিয়াম',NULL,NULL,'2026-09-12 01:54:50','2026-09-12 01:54:50',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (299,'97d33130-36a6-44c4-8756-b5efac6d5b19',NULL,41,NULL,'নভেল এআই ১ মাস পার্সোনাল','nvel-eai-1-mas-parsonal','EFLIX-6034',NULL,'simple','piece','নভেল এআই ১ মাস পার্সোনাল subscription or digital service.','নভেল এআই ১ মাস পার্সোনাল',NULL,NULL,NULL,1300.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:22:09','নভেল এআই ১ মাস পার্সোনাল','নভেল এআই ১ মাস পার্সোনাল',NULL,NULL,'2026-09-12 01:54:51','2026-09-12 01:54:51',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (300,'78f8b417-2eee-4b61-8814-4a3e9a00c159',NULL,41,NULL,'লুডউইগ.গুরু ১ মাস','luduigguru-1-mas','EFLIX-6031',NULL,'simple','piece','লুডউইগ.গুরু ১ মাস subscription or digital service.','লুডউইগ.গুরু ১ মাস',NULL,NULL,NULL,220.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:22:15','লুডউইগ.গুরু ১ মাস','লুডউইগ.গুরু ১ মাস',NULL,NULL,'2026-09-12 01:54:51','2026-09-12 01:54:51',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (301,'42642a91-06d2-40ef-b47b-8091c0956656',NULL,41,NULL,'অটার এআই ১ মাস পার্সোনাল','otar-eai-1-mas-parsonal','EFLIX-6029',NULL,'simple','piece','অটার এআই ১ মাস পার্সোনাল subscription or digital service.','অটার এআই ১ মাস পার্সোনাল',NULL,NULL,NULL,1220.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:22:19','অটার এআই ১ মাস পার্সোনাল','অটার এআই ১ মাস পার্সোনাল',NULL,NULL,'2026-09-12 01:54:54','2026-09-12 01:54:54',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (302,'297b9c48-4c29-4847-8a13-a9204f00053d',NULL,41,NULL,'নৌশন এআই ১ মাস','nousn-eai-1-mas','EFLIX-6027',NULL,'simple','piece','নৌশন এআই ১ মাস subscription or digital service.','নৌশন এআই ১ মাস',NULL,NULL,NULL,190.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:22:25','নৌশন এআই ১ মাস','নৌশন এআই ১ মাস',NULL,NULL,'2026-09-12 01:54:56','2026-09-12 01:54:56',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (303,'e3f5c13d-9d74-4488-8fb4-994db4660c62',NULL,41,NULL,'বিউটিফুল.এআই','biutifuleai','EFLIX-6023',NULL,'simple','piece','বিউটিফুল.এআই subscription or digital service.','বিউটিফুল.এআই',NULL,NULL,NULL,250.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:22:31','বিউটিফুল.এআই','বিউটিফুল.এআই',NULL,NULL,'2026-09-12 01:54:57','2026-09-12 01:54:57',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (304,'7538b64b-a29a-4cda-b786-539fba182771',NULL,41,NULL,'কপি.এআই','kpieai','EFLIX-6019',NULL,'simple','piece','কপি.এআই subscription or digital service.','কপি.এআই',NULL,NULL,NULL,550.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:22:34','কপি.এআই','কপি.এআই',NULL,NULL,'2026-09-12 01:55:02','2026-09-12 01:55:02',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (305,'efeefe6c-1af6-4901-abca-cb6a9632bc35',NULL,46,NULL,'কুইজলেট প্লাস ১ বছর','kuijlet-plas-1-bchr','EFLIX-6001',NULL,'simple','piece','কুইজলেট প্লাস ১ বছর subscription or digital service.','কুইজলেট প্লাস ১ বছর',NULL,NULL,NULL,2800.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:22:40','কুইজলেট প্লাস ১ বছর','কুইজলেট প্লাস ১ বছর',NULL,NULL,'2026-09-12 01:55:03','2026-09-12 01:55:03',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (306,'edb3870c-59db-4a23-93d4-03abb8b823cf',NULL,46,NULL,'প্লুরালসাইট ১ মাস','pluralsait-1-mas','EFLIX-5998',NULL,'simple','piece','প্লুরালসাইট ১ মাস subscription or digital service.','প্লুরালসাইট ১ মাস',NULL,NULL,NULL,480.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:22:44','প্লুরালসাইট ১ মাস','প্লুরালসাইট ১ মাস',NULL,NULL,'2026-09-12 01:55:04','2026-09-12 01:55:04',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (307,'4139435e-1445-4a37-9fb9-90e517ebb402',NULL,46,NULL,'বিজনেস ইনসাইডার','bijnes-insaidar','EFLIX-5994',NULL,'simple','piece','বিজনেস ইনসাইডার subscription or digital service.','বিজনেস ইনসাইডার',NULL,NULL,NULL,800.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:22:51','বিজনেস ইনসাইডার','বিজনেস ইনসাইডার',NULL,NULL,'2026-09-12 01:55:06','2026-09-12 01:55:06',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (308,'485085c5-ca7c-4952-be4b-b610e5e1c251',NULL,46,NULL,'ব্রিলিয়ান্ট ১ মাস','brilizant-1-mas','EFLIX-5992',NULL,'simple','piece','ব্রিলিয়ান্ট ১ মাস subscription or digital service.','ব্রিলিয়ান্ট ১ মাস',NULL,NULL,NULL,290.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:22:59','ব্রিলিয়ান্ট ১ মাস','ব্রিলিয়ান্ট ১ মাস',NULL,NULL,'2026-09-12 01:55:06','2026-09-12 01:55:06',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (309,'797651b4-3fcb-4b0f-963c-707e4a4ea112',NULL,46,NULL,'একাডেমিয়া.এডু','ekademizaedu','EFLIX-5988',NULL,'simple','piece','একাডেমিয়া.এডু subscription or digital service.','একাডেমিয়া.এডু',NULL,NULL,NULL,1220.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:23:04','একাডেমিয়া.এডু','একাডেমিয়া.এডু',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (310,'1190dc87-3e11-40c5-ab9f-8f67a005cf60',NULL,42,NULL,'লিব্বি ১ বছর','libbi-1-bchr','EFLIX-5976',NULL,'simple','piece','লিব্বি ১ বছর subscription or digital service.','লিব্বি ১ বছর',NULL,NULL,NULL,1500.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:23:10','লিব্বি ১ বছর','লিব্বি ১ বছর',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (311,'c50ff3ac-fcc8-4057-abdf-99706f306852',NULL,42,NULL,'ওয়ান্ড্রিয়াম দ্য গ্রেট কোর্স ১ মাস','oozandrizam-dz-gret-kors-1-mas','EFLIX-5974',NULL,'simple','piece','ওয়ান্ড্রিয়াম দ্য গ্রেট কোর্স ১ মাস subscription or digital service.','ওয়ান্ড্রিয়াম দ্য গ্রেট কোর্স ১ মাস',NULL,NULL,NULL,880.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:23:14','ওয়ান্ড্রিয়াম দ্য গ্রেট কোর্স ১ মাস','ওয়ান্ড্রিয়াম দ্য গ্রেট কোর্স ১ মাস',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (312,'c0e8c4b5-9d76-45d3-91fb-5218e0f7156b',NULL,42,NULL,'ম্যাথওয়ে','mzathwe','EFLIX-5969',NULL,'simple','piece','ম্যাথওয়ে subscription or digital service.','ম্যাথওয়ে',NULL,NULL,NULL,680.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:23:19','ম্যাথওয়ে','ম্যাথওয়ে',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (313,'b1197fc2-00be-4f70-bf34-fa3154412f29',NULL,46,NULL,'জিনজার ১ মাস','jinjar-1-mas','EFLIX-5965',NULL,'simple','piece','জিনজার ১ মাস subscription or digital service.','জিনজার ১ মাস',NULL,NULL,NULL,600.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:23:23','জিনজার ১ মাস','জিনজার ১ মাস',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (314,'e13c4ee3-2f72-4f8b-b9dd-7b349bc85b8c',NULL,46,NULL,'ল্যাকচারিওর মেডিকেল ১ মাস','lzakcarioor-medikel-1-mas','EFLIX-5963',NULL,'simple','piece','ল্যাকচারিওর মেডিকেল ১ মাস subscription or digital service.','ল্যাকচারিওর মেডিকেল ১ মাস',NULL,NULL,NULL,749.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:23:29','ল্যাকচারিওর মেডিকেল ১ মাস','ল্যাকচারিওর মেডিকেল ১ মাস',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (315,'00a14e52-288a-4b97-bf24-34854e48c48c',NULL,46,NULL,'মাস্টার ক্লাস','mastar-klas','EFLIX-5956',NULL,'simple','piece','মাস্টার ক্লাস subscription or digital service.','মাস্টার ক্লাস',NULL,NULL,NULL,600.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:23:35','মাস্টার ক্লাস','মাস্টার ক্লাস',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (316,'153d5e89-7288-49d1-9c02-1a8f053189e6',NULL,46,NULL,'মর্নিং স্টার','mrning-star','EFLIX-5678',NULL,'simple','piece','মর্নিং স্টার subscription or digital service.','মর্নিং স্টার',NULL,NULL,NULL,1400.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:23:41','মর্নিং স্টার','মর্নিং স্টার',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (317,'bfa0c873-7ade-4858-9e1b-a7f0eac1a003',NULL,46,NULL,'স্টাডি.কম','stadikm','EFLIX-5671',NULL,'simple','piece','স্টাডি.কম subscription or digital service.','স্টাডি.কম',NULL,NULL,NULL,440.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:23:47','স্টাডি.কম','স্টাডি.কম',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (318,'67f8d565-d59f-428e-8140-8ce84cb3c823',NULL,46,NULL,'ডেটা ক্যাম্প','deta-kzamp','EFLIX-5665',NULL,'simple','piece','ডেটা ক্যাম্প subscription or digital service.','ডেটা ক্যাম্প',NULL,NULL,NULL,180.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:23:54','ডেটা ক্যাম্প','ডেটা ক্যাম্প',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (319,'c3443a63-07fb-4ff7-b232-8e4fa58782cd',NULL,46,NULL,'এলসা স্পিক','elsa-spik','EFLIX-5660',NULL,'simple','piece','এলসা স্পিক subscription or digital service.','এলসা স্পিক',NULL,NULL,NULL,3000.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:24:01','এলসা স্পিক','এলসা স্পিক',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (320,'a6e82b86-0acb-4f1e-9713-6da71aa2f191',NULL,46,NULL,'কোডেকাডেমি','kodekademi','EFLIX-5656',NULL,'simple','piece','কোডেকাডেমি subscription or digital service.','কোডেকাডেমি',NULL,NULL,NULL,420.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:24:07','কোডেকাডেমি','কোডেকাডেমি',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (321,'7a937b0b-cce5-4f8f-a447-cf60cbb5daee',NULL,46,NULL,'ফ্লুয়েন্ট ইউ','fluyent-iu','EFLIX-5652',NULL,'simple','piece','ফ্লুয়েন্ট ইউ subscription or digital service.','ফ্লুয়েন্ট ইউ',NULL,NULL,NULL,1200.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:24:14','ফ্লুয়েন্ট ইউ','ফ্লুয়েন্ট ইউ',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (322,'53f8adf7-db4b-4a4a-a50e-e560a7ed39bd',NULL,46,NULL,'ম্যাকগ্রা হিল কানেক্ট ১ মাস','mzakgra-hil-kanekt-1-mas','EFLIX-5649',NULL,'simple','piece','ম্যাকগ্রা হিল কানেক্ট ১ মাস subscription or digital service.','ম্যাকগ্রা হিল কানেক্ট ১ মাস',NULL,NULL,NULL,270.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:24:20','ম্যাকগ্রা হিল কানেক্ট ১ মাস','ম্যাকগ্রা হিল কানেক্ট ১ মাস',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (323,'2b12d639-092d-48ad-a8bf-a508ffc76885',NULL,46,NULL,'পিয়ারসন মাইল্যাব ১ মাস','piyarsn-mailzab-1-mas','EFLIX-5647',NULL,'simple','piece','পিয়ারসন মাইল্যাব ১ মাস subscription or digital service.','পিয়ারসন মাইল্যাব ১ মাস',NULL,NULL,NULL,250.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:24:26','পিয়ারসন মাইল্যাব ১ মাস','পিয়ারসন মাইল্যাব ১ মাস',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (324,'5c05f414-23fd-4903-a0c6-7588f26abb6a',NULL,46,NULL,'কোর্স হিরো ১ মাস','kors-hiro-1-mas','EFLIX-5631',NULL,'simple','piece','কোর্স হিরো ১ মাস subscription or digital service.','কোর্স হিরো ১ মাস',NULL,NULL,NULL,890.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:24:32','কোর্স হিরো ১ মাস','কোর্স হিরো ১ মাস',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (325,'ffa71a41-6992-4794-b095-cf66a4a0364c',NULL,46,NULL,'স্কিলশেয়ার','skilsezar','EFLIX-5623',NULL,'simple','piece','স্কিলশেয়ার subscription or digital service.','স্কিলশেয়ার',NULL,NULL,NULL,270.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:24:39','স্কিলশেয়ার','স্কিলশেয়ার',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (326,'c7122015-2222-4a27-8268-6b29c89b8d23',NULL,46,NULL,'চেগ স্টাডি ১ মাস পার্সোনাল','ceg-stadi-1-mas-parsonal','EFLIX-5620',NULL,'simple','piece','চেগ স্টাডি ১ মাস পার্সোনাল subscription or digital service.','চেগ স্টাডি ১ মাস পার্সোনাল',NULL,NULL,NULL,1350.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:24:44','চেগ স্টাডি ১ মাস পার্সোনাল','চেগ স্টাডি ১ মাস পার্সোনাল',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (327,'fcea9c91-f243-46bd-990e-38b090cc2a39',NULL,46,NULL,'কোর্সেরা','korsera','EFLIX-5618',NULL,'simple','piece','কোর্সেরা subscription or digital service.','কোর্সেরা',NULL,NULL,NULL,900.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:24:50','কোর্সেরা','কোর্সেরা',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (328,'bc9a2b82-bc13-4fe6-9285-47b4478aa3ee',NULL,42,NULL,'বঙ্গ বিডি','bngo-bidi','EFLIX-5610',NULL,'simple','piece','বঙ্গ বিডি subscription or digital service.','বঙ্গ বিডি',NULL,NULL,NULL,220.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:24:54','বঙ্গ বিডি','বঙ্গ বিডি',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (329,'68f4b9c6-fd88-4728-a78d-1df47216582b',NULL,42,NULL,'নাউ টিভি ১ মাস পার্সোনাল','nau-tivi-1-mas-parsonal','EFLIX-5608',NULL,'simple','piece','নাউ টিভি ১ মাস পার্সোনাল subscription or digital service.','নাউ টিভি ১ মাস পার্সোনাল',NULL,NULL,NULL,1600.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:24:57','নাউ টিভি ১ মাস পার্সোনাল','নাউ টিভি ১ মাস পার্সোনাল',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (330,'044db2e2-c4b1-4678-9537-9cd71dca18a9',NULL,42,NULL,'স্লিং টিভি ( ব্লু + অরেঞ্জ) ১ মাস','sling-tivi-blu-orenj-1-mas','EFLIX-5595',NULL,'simple','piece','স্লিং টিভি ( ব্লু + অরেঞ্জ) ১ মাস subscription or digital service.','স্লিং টিভি ( ব্লু + অরেঞ্জ) ১ মাস',NULL,NULL,NULL,680.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:25:06','স্লিং টিভি ( ব্লু + অরেঞ্জ) ১ মাস','স্লিং টিভি ( ব্লু + অরেঞ্জ) ১ মাস',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (331,'16b92a5f-bd5f-496e-97cd-e673f9746556',NULL,42,NULL,'টেনিস টিভি','tenis-tivi','EFLIX-5590',NULL,'simple','piece','টেনিস টিভি subscription or digital service.','টেনিস টিভি',NULL,NULL,NULL,680.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:25:11','টেনিস টিভি','টেনিস টিভি',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (332,'f063b89f-dee2-4118-ae38-b71aa6544641',NULL,42,NULL,'আল্ট বালাজী ১ মাস','alt-balajee-1-mas','EFLIX-5582',NULL,'simple','piece','আল্ট বালাজী ১ মাস subscription or digital service.','আল্ট বালাজী ১ মাস',NULL,NULL,NULL,190.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:25:17','আল্ট বালাজী ১ মাস','আল্ট বালাজী ১ মাস',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (333,'851cd0a1-6448-4cea-90a2-fc5140a9e47c',NULL,42,NULL,'ক্লিক','klik','EFLIX-5577',NULL,'simple','piece','ক্লিক subscription or digital service.','ক্লিক',NULL,NULL,NULL,180.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:25:21','ক্লিক','ক্লিক',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (334,'13e1ef87-0f50-46e0-b596-25cd3583412c',NULL,42,NULL,'অ্যাপল টিভি প্লাস ৩ মাস','azpl-tivi-plas-3-mas','EFLIX-5565',NULL,'simple','piece','অ্যাপল টিভি প্লাস ৩ মাস subscription or digital service.','অ্যাপল টিভি প্লাস ৩ মাস',NULL,NULL,NULL,750.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:25:26','অ্যাপল টিভি প্লাস ৩ মাস','অ্যাপল টিভি প্লাস ৩ মাস',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (335,'74502811-bdc4-4b1c-bfb0-195738de8600',NULL,42,NULL,'এইচবিও গো ১ মাস','eicbioo-go-1-mas','EFLIX-5559',NULL,'simple','piece','এইচবিও গো ১ মাস subscription or digital service.','এইচবিও গো ১ মাস',NULL,NULL,NULL,349.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:25:32','এইচবিও গো ১ মাস','এইচবিও গো ১ মাস',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (336,'e195eb77-38a1-499e-9bac-9673ca90fb29',NULL,40,NULL,'কম্বো প্যাক (নেটফ্লিক্স +আমাজন )','kmbo-pzak-netfliks-amajn','EFLIX-5509',NULL,'simple','piece','কম্বো প্যাক (নেটফ্লিক্স +আমাজন ) subscription or digital service','কম্বো প্যাক (নেটফ্লিক্স +আমাজন )',NULL,NULL,NULL,429.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:25:37','কম্বো প্যাক (নেটফ্লিক্স +আমাজন )','কম্বো প্যাক (নেটফ্লিক্স +আমাজন )',NULL,48,'2026-09-12 01:55:11','2026-09-14 22:55:36',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (337,'449936d5-78f0-4cbf-9838-d4f3b2920dfa',NULL,40,NULL,'অ্যামাজন প্রাইম ভিডিও','azmajn-praim-vidioo','EFLIX-5499',NULL,'simple','piece','অ্যামাজন প্রাইম ভিডিও subscription or digital service.','অ্যামাজন প্রাইম ভিডিও',NULL,NULL,NULL,119.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:25:41','অ্যামাজন প্রাইম ভিডিও','অ্যামাজন প্রাইম ভিডিও',NULL,48,'2026-09-12 01:55:11','2026-09-12 06:27:19',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (338,'4563e285-4da7-482e-a4ae-26a1835859e4',NULL,45,NULL,'নেটফ্লিক্স 4K প্রিমিয়াম অ্যাকাউন্ট','netfliks-4k-primizam-azkaunt','EFLIX-5360',NULL,'digital','piece','নেটফ্লিক্স 4K প্রিমিয়াম অ্যাকাউন্ট subscription or digital service.','নেটফ্লিক্স 4K প্রিমিয়াম অ্যাকাউন্ট',NULL,NULL,NULL,389.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:25:44','নেটফ্লিক্স 4K প্রিমিয়াম অ্যাকাউন্ট','নেটফ্লিক্স 4K প্রিমিয়াম অ্যাকাউন্ট',NULL,48,'2026-09-12 01:55:11','2026-09-15 06:17:23',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (339,'72346cd3-d58a-49dc-8589-86879899f236',NULL,46,NULL,'হেডস্পেস প্লাস ১ মাস','hedspes-plas-1-mas','EFLIX-3925',NULL,'simple','piece','হেডস্পেস প্লাস ১ মাস subscription or digital service.','হেডস্পেস প্লাস ১ মাস',NULL,NULL,NULL,184.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:25:54','হেডস্পেস প্লাস ১ মাস','হেডস্পেস প্লাস ১ মাস',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (340,'3ebfe4ea-babe-4cc9-90c4-6cadd83a03e5',NULL,46,NULL,'গুরু ফোকাস প্রিমিয়াম','guru-fokas-primiyam','EFLIX-3923',NULL,'simple','piece','গুরু ফোকাস প্রিমিয়াম subscription or digital service.','গুরু ফোকাস প্রিমিয়াম',NULL,NULL,NULL,1799.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:26:00','গুরু ফোকাস প্রিমিয়াম','গুরু ফোকাস প্রিমিয়াম',NULL,NULL,'2026-09-12 01:55:11','2026-09-12 01:55:11',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (341,'912300a3-16cb-4dd5-8ca8-70c4a2fa6b8f',NULL,46,NULL,'দ্য নিউ ইয়র্ক টাইমস ১ মাস','dz-niu-izrk-taims-1-mas','EFLIX-3921',NULL,'simple','piece','দ্য নিউ ইয়র্ক টাইমস ১ মাস subscription or digital service.','দ্য নিউ ইয়র্ক টাইমস ১ মাস',NULL,NULL,NULL,449.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:26:06','দ্য নিউ ইয়র্ক টাইমস ১ মাস','দ্য নিউ ইয়র্ক টাইমস ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (342,'69f6bb98-3005-4bd4-9ff5-80ddb54f8f49',NULL,46,NULL,'ফাইনান্সিয়াল টাইমস প্রিমিয়াম ১ মাস','fainansiyal-taims-primiyam-1-mas','EFLIX-3920',NULL,'simple','piece','ফাইনান্সিয়াল টাইমস প্রিমিয়াম ১ মাস subscription or digital service.','ফাইনান্সিয়াল টাইমস প্রিমিয়াম ১ মাস',NULL,NULL,NULL,299.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:26:13','ফাইনান্সিয়াল টাইমস প্রিমিয়াম ১ মাস','ফাইনান্সিয়াল টাইমস প্রিমিয়াম ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (343,'871aa2a7-5d88-4eb4-b835-830e0c5dc023',NULL,46,NULL,'সিকিং আলফা ১ মাস','siking-alpha-1-mas','EFLIX-3905',NULL,'simple','piece','সিকিং আলফা ১ মাস subscription or digital service.','সিকিং আলফা ১ মাস',NULL,NULL,NULL,299.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:26:19','সিকিং আলফা ১ মাস','সিকিং আলফা ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (344,'f7e6dfe0-81d4-4ca5-a33b-1c85086b8ea8',NULL,46,NULL,'জুম  প্রো ১ মাস','jum-pro-1-mas','EFLIX-3904',NULL,'simple','piece','জুম  প্রো ১ মাস subscription or digital service.','জুম  প্রো ১ মাস',NULL,NULL,NULL,849.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:26:23','জুম  প্রো ১ মাস','জুম  প্রো ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (345,'bf574e13-02b0-486f-8112-9cc51497fe41',NULL,39,NULL,'কাম মেডিটেশন ১ মাস','kam-meditesn-1-mas','EFLIX-3899',NULL,'simple','piece','কাম মেডিটেশন ১ মাস subscription or digital service.','কাম মেডিটেশন ১ মাস',NULL,NULL,NULL,449.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:26:32','কাম মেডিটেশন ১ মাস','কাম মেডিটেশন ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (346,'ed2ad0fd-eed1-4f6f-ad8b-28a3c6fa7470',NULL,39,NULL,'অডিবল প্রিমিয়াম প্লাস মেম্বেরশিপ ১ মাস + ১ ক্রেডিট','odibl-primizam-plas-membersip-1-mas-1-kredit','EFLIX-3887',NULL,'simple','piece','অডিবল প্রিমিয়াম প্লাস মেম্বেরশিপ ১ মাস + ১ ক্রেডিট subscription or digital service.','অডিবল প্রিমিয়াম প্লাস মেম্বেরশিপ ১ মাস + ১ ক্রেডিট',NULL,NULL,NULL,329.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:26:36','অডিবল প্রিমিয়াম প্লাস মেম্বেরশিপ ১ মাস + ১ ক্রেডিট','অডিবল প্রিমিয়াম প্লাস মেম্বেরশিপ ১ মাস + ১ ক্রেডিট',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (347,'96f433cf-7676-4007-b390-e00a11f3983e',NULL,39,NULL,'আই ক্লাউড ১ বছর ১০০ জিবি','ai-klaud-1-bchr-100-jibi','EFLIX-3865',NULL,'simple','piece','আই ক্লাউড ১ বছর ১০০ জিবি subscription or digital service.','আই ক্লাউড ১ বছর ১০০ জিবি',NULL,NULL,NULL,2050.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:26:43','আই ক্লাউড ১ বছর ১০০ জিবি','আই ক্লাউড ১ বছর ১০০ জিবি',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (348,'4ed61896-692c-4428-8f92-fb68c39a16c1',NULL,44,NULL,'প্লে স্টেশন প্লাস এসেনশিয়াল ১ মাস','ple-stesn-plas-esenshiyal-1-mas','EFLIX-3864',NULL,'simple','piece','প্লে স্টেশন প্লাস এসেনশিয়াল ১ মাস subscription or digital service.','প্লে স্টেশন প্লাস এসেনশিয়াল ১ মাস',NULL,NULL,NULL,1699.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:26:48','প্লে স্টেশন প্লাস এসেনশিয়াল ১ মাস','প্লে স্টেশন প্লাস এসেনশিয়াল ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (349,'46b3ed72-7354-4eee-b7aa-8107aee2e3be',NULL,44,NULL,'ইএ প্লে  প্রো ১ মাস','ie-ple-pro-1-mas','EFLIX-3863',NULL,'simple','piece','ইএ প্লে  প্রো ১ মাস subscription or digital service.','ইএ প্লে  প্রো ১ মাস',NULL,NULL,NULL,1449.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:26:55','ইএ প্লে  প্রো ১ মাস','ইএ প্লে  প্রো ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (350,'1a18c7ed-b889-40cd-8f9a-8a93918130e0',NULL,44,NULL,'এক্সবক্স  এক্স জি পি ইউ গেম পাস ১ মাস','xbks-x-ji-pi-iu-gem-pas-1-mas','EFLIX-3862',NULL,'simple','piece','এক্সবক্স  এক্স জি পি ইউ গেম পাস ১ মাস subscription or digital service.','এক্সবক্স  এক্স জি পি ইউ গেম পাস ১ মাস',NULL,NULL,NULL,820.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:27:26','এক্সবক্স  এক্স জি পি ইউ গেম পাস ১ মাস','এক্সবক্স  এক্স জি পি ইউ গেম পাস ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (351,'8c5cc450-d95f-429e-8cea-b4e2868cbab9',NULL,44,NULL,'নিন্টেন্ডো সুইচ অনলাইন ১ মাস','nintendo-suic-onlain-1-mas','EFLIX-3857',NULL,'simple','piece','নিন্টেন্ডো সুইচ অনলাইন ১ মাস subscription or digital service.','নিন্টেন্ডো সুইচ অনলাইন ১ মাস',NULL,NULL,NULL,999.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:27:56','নিন্টেন্ডো সুইচ অনলাইন ১ মাস','নিন্টেন্ডো সুইচ অনলাইন ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (352,'d82ab64a-ddf6-4e8a-96af-f88efaf8a278',NULL,48,NULL,'আলটিমেট গিটার প্রো  ১ মাস','altimet-gitar-pro-1-mas','EFLIX-3853',NULL,'simple','piece','আলটিমেট গিটার প্রো  ১ মাস subscription or digital service.','আলটিমেট গিটার প্রো  ১ মাস',NULL,NULL,NULL,249.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:28:02','আলটিমেট গিটার প্রো  ১ মাস','আলটিমেট গিটার প্রো  ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (353,'a8027e9c-397d-4bc6-8017-3c38f2777a41',NULL,48,NULL,'কোবুজ প্রিমিয়াম  ১ মাস','kobuj-primiyam-1-mas','EFLIX-3852',NULL,'simple','piece','কোবুজ প্রিমিয়াম  ১ মাস subscription or digital service.','কোবুজ প্রিমিয়াম  ১ মাস',NULL,NULL,NULL,349.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:28:08','কোবুজ প্রিমিয়াম  ১ মাস','কোবুজ প্রিমিয়াম  ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (354,'9bce3614-32b0-4d5a-a0ce-5eced40c79cf',NULL,48,NULL,'ডিজার প্রিমিয়াম ১ মাস','dijar-primiyam-1-mas','EFLIX-3848',NULL,'simple','piece','ডিজার প্রিমিয়াম ১ মাস subscription or digital service.','ডিজার প্রিমিয়াম ১ মাস',NULL,NULL,NULL,229.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:28:14','ডিজার প্রিমিয়াম ১ মাস','ডিজার প্রিমিয়াম ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (355,'1226f8c7-6bc1-4fab-9cf4-c2119315454a',NULL,48,NULL,'টাইডাল মিউজিক ১ মাস','taidal-miujik-1-mas','EFLIX-3841',NULL,'simple','piece','টাইডাল মিউজিক ১ মাস subscription or digital service.','টাইডাল মিউজিক ১ মাস',NULL,NULL,NULL,129.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:28:21','টাইডাল মিউজিক ১ মাস','টাইডাল মিউজিক ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (356,'7e68c630-64b1-4795-8389-9dee3ea142dd',NULL,46,NULL,'ডুডলি এন্টারপ্রাইজ ১ মাস','dudli-entarpraij-1-mas','EFLIX-3839',NULL,'simple','piece','ডুডলি এন্টারপ্রাইজ ১ মাস subscription or digital service.','ডুডলি এন্টারপ্রাইজ ১ মাস',NULL,NULL,NULL,499.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:28:26','ডুডলি এন্টারপ্রাইজ ১ মাস','ডুডলি এন্টারপ্রাইজ ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (357,'f02a31ba-d6cd-4181-8b23-6c717b49acf7',NULL,46,NULL,'অফিস ৩৬৫  ১ বছর','ofis-365-1-bchr','EFLIX-3835',NULL,'simple','piece','অফিস ৩৬৫  ১ বছর subscription or digital service.','অফিস ৩৬৫  ১ বছর',NULL,NULL,NULL,1050.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:28:33','অফিস ৩৬৫  ১ বছর','অফিস ৩৬৫  ১ বছর',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (358,'afeba8ff-7730-444c-bb6c-e1d7d3f15d7b',NULL,46,NULL,'আই লাভ পিডিএফ ১ মাস','ai-lav-pidief-1-mas','EFLIX-3833',NULL,'simple','piece','আই লাভ পিডিএফ ১ মাস subscription or digital service.','আই লাভ পিডিএফ ১ মাস',NULL,NULL,NULL,219.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:28:38','আই লাভ পিডিএফ ১ মাস','আই লাভ পিডিএফ ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (359,'2f0a4e09-3a8f-470b-a75e-9fa8126f3e55',NULL,46,NULL,'স্মল পিডিএফ প্রো ১ মাস','sml-pidief-pro-1-mas','EFLIX-3832',NULL,'simple','piece','স্মল পিডিএফ প্রো ১ মাস subscription or digital service.','স্মল পিডিএফ প্রো ১ মাস',NULL,NULL,NULL,99.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:28:44','স্মল পিডিএফ প্রো ১ মাস','স্মল পিডিএফ প্রো ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (360,'1816c435-7404-465b-bed0-08d97ba8eeb8',NULL,39,NULL,'ট্রেডিংভিউ প্রিমিয়াম ১ মাস','tredingviu-primiyam-1-mas','EFLIX-3831',NULL,'simple','piece','ট্রেডিংভিউ প্রিমিয়াম ১ মাস subscription or digital service.','ট্রেডিংভিউ প্রিমিয়াম ১ মাস',NULL,NULL,NULL,799.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:28:50','ট্রেডিংভিউ প্রিমিয়াম ১ মাস','ট্রেডিংভিউ প্রিমিয়াম ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (361,'aabf19c2-5105-45b4-a0c8-4b2fdaf85845',NULL,46,NULL,'টোডোইস্ট প্রিমিয়াম ৩ মাস','todoist-primiyam-3-mas','EFLIX-3830',NULL,'simple','piece','টোডোইস্ট প্রিমিয়াম ৩ মাস subscription or digital service.','টোডোইস্ট প্রিমিয়াম ৩ মাস',NULL,NULL,NULL,1099.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:28:56','টোডোইস্ট প্রিমিয়াম ৩ মাস','টোডোইস্ট প্রিমিয়াম ৩ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (362,'4aaf2246-890d-4e80-964c-e4fe0abb5928',NULL,39,NULL,'স্লিপ সাইকেল প্রিমিয়াম ৩ মাস','slip-saikel-primiyam-3-mas','EFLIX-3829',NULL,'simple','piece','স্লিপ সাইকেল প্রিমিয়াম ৩ মাস subscription or digital service.','স্লিপ সাইকেল প্রিমিয়াম ৩ মাস',NULL,NULL,NULL,379.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:28:59','স্লিপ সাইকেল প্রিমিয়াম ৩ মাস','স্লিপ সাইকেল প্রিমিয়াম ৩ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (363,'cf42ae79-26ca-4e49-ba40-f0bd35bc3dc1',NULL,46,NULL,'ক্যানভা প্রো','kzanva-pro','EFLIX-3818',NULL,'simple','piece','ক্যানভা প্রো subscription or digital service.','ক্যানভা প্রো',NULL,NULL,NULL,80.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:29:05','ক্যানভা প্রো','ক্যানভা প্রো',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (364,'2ce014cb-c374-41b0-b6ca-928e7daa3bfc',NULL,42,NULL,'ইএসপিএন+ ১ মাস','iespien-1-mas','EFLIX-3817',NULL,'simple','piece','ইএসপিএন+ ১ মাস subscription or digital service.','ইএসপিএন+ ১ মাস',NULL,NULL,NULL,299.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:29:11','ইএসপিএন+ ১ মাস','ইএসপিএন+ ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (365,'f01f0693-7324-45d0-a870-5e0c1c657685',NULL,42,NULL,'চরকি','crki','EFLIX-3816',NULL,'simple','piece','চরকি subscription or digital service.','চরকি',NULL,NULL,NULL,99.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:29:15','চরকি','চরকি',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (366,'ff2686ce-164c-4d30-a805-c068bef7e5d6',NULL,42,NULL,'এমএলবি​ টিভি মেজর লীগ বেসবল  ১ মাস','emelbi-tivi-mejr-leeg-besbl-1-mas','EFLIX-3815',NULL,'simple','piece','এমএলবি​ টিভি মেজর লীগ বেসবল  ১ মাস subscription or digital service.','এমএলবি​ টিভি মেজর লীগ বেসবল  ১ মাস',NULL,NULL,NULL,449.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:29:43','এমএলবি​ টিভি মেজর লীগ বেসবল  ১ মাস','এমএলবি​ টিভি মেজর লীগ বেসবল  ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (367,'083f5512-efd5-49e1-8ed5-76fcb3dec01b',NULL,42,NULL,'এনবিএ লিগ পাস ১ মাস','enbie-lig-pas-1-mas','EFLIX-3814',NULL,'simple','piece','এনবিএ লিগ পাস ১ মাস subscription or digital service.','এনবিএ লিগ পাস ১ মাস',NULL,NULL,NULL,599.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:29:48','এনবিএ লিগ পাস ১ মাস','এনবিএ লিগ পাস ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (368,'842b7771-ea26-461d-914c-5727e79cd538',NULL,42,NULL,'স্ট্যান প্রিমিয়াম ১ মাস','stzan-primizam-1-mas','EFLIX-3812',NULL,'simple','piece','স্ট্যান প্রিমিয়াম ১ মাস subscription or digital service.','স্ট্যান প্রিমিয়াম ১ মাস',NULL,NULL,NULL,680.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:29:54','স্ট্যান প্রিমিয়াম ১ মাস','স্ট্যান প্রিমিয়াম ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (369,'8a853950-cba9-43e3-88ae-76de8a316153',NULL,42,NULL,'এফ ওয়ান টিভি প্রো ১ মাস','ef-wan-tivi-pro-1-mas','EFLIX-3811',NULL,'simple','piece','এফ ওয়ান টিভি প্রো ১ মাস subscription or digital service.','এফ ওয়ান টিভি প্রো ১ মাস',NULL,NULL,NULL,379.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:29:59','এফ ওয়ান টিভি প্রো ১ মাস','এফ ওয়ান টিভি প্রো ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (370,'0c1dd175-36c2-4b68-aed1-80e70a87f5a4',NULL,42,NULL,'স্টারজ ১ মাস','starj-1-mas','EFLIX-3810',NULL,'simple','piece','স্টারজ ১ মাস subscription or digital service.','স্টারজ ১ মাস',NULL,NULL,NULL,300.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:30:05','স্টারজ ১ মাস','স্টারজ ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (371,'cf314c4b-01d2-4d10-b5f8-a9aa484ce6f0',NULL,42,NULL,'ভিকি রাকুটেন প্লাস ১ মাস','viki-rakuten-plas-1-mas','EFLIX-3808',NULL,'simple','piece','ভিকি রাকুটেন প্লাস ১ মাস subscription or digital service.','ভিকি রাকুটেন প্লাস ১ মাস',NULL,NULL,NULL,449.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:30:11','ভিকি রাকুটেন প্লাস ১ মাস','ভিকি রাকুটেন প্লাস ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (372,'7d316226-1579-46a9-bc50-690edd0d8cca',NULL,42,NULL,'ক্রাঞ্চিরোল ১ মাস','krancirol-1-mas','EFLIX-3796',NULL,'simple','piece','ক্রাঞ্চিরোল ১ মাস subscription or digital service.','ক্রাঞ্চিরোল ১ মাস',NULL,NULL,NULL,300.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:30:16','ক্রাঞ্চিরোল ১ মাস','ক্রাঞ্চিরোল ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (373,'2a12323c-d935-4742-b345-a532b62d622f',NULL,39,NULL,'অ্যাপল ওয়ান','azpl-wan','EFLIX-359',NULL,'simple','piece','অ্যাপল ওয়ান subscription or digital service.','অ্যাপল ওয়ান',NULL,NULL,NULL,999.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:30:22','অ্যাপল ওয়ান','অ্যাপল ওয়ান',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (374,'67ecba2a-479b-4706-ab58-45ce6a3df00a',NULL,46,NULL,'কুইল বট প্রিমিয়াম ১ মাস','kuil-bt-primiyam-1-mas','EFLIX-357',NULL,'simple','piece','কুইল বট প্রিমিয়াম ১ মাস subscription or digital service.','কুইল বট প্রিমিয়াম ১ মাস',NULL,NULL,NULL,169.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:30:28','কুইল বট প্রিমিয়াম ১ মাস','কুইল বট প্রিমিয়াম ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (375,'149cb900-16ef-4ca2-817f-3d9aaed344f8',NULL,46,NULL,'টার্নিটিন ১ মাস','tarnitin-1-mas','EFLIX-355',NULL,'simple','piece','টার্নিটিন ১ মাস subscription or digital service.','টার্নিটিন ১ মাস',NULL,NULL,NULL,4999.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:30:32','টার্নিটিন ১ মাস','টার্নিটিন ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (376,'b976b013-e796-434b-ac4f-3f28e7779be4',NULL,42,NULL,'পিকক টিভি ১ মাস','pikk-tivi-1-mas','EFLIX-353',NULL,'simple','piece','পিকক টিভি ১ মাস subscription or digital service.','পিকক টিভি ১ মাস',NULL,NULL,NULL,249.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:31:32','পিকক টিভি ১ মাস','পিকক টিভি ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (377,'6a796c7a-6249-4637-94d8-6b95e7b22bd2',NULL,42,NULL,'এ এম সি + ১ মাস','e-em-si-1-mas','EFLIX-351',NULL,'simple','piece','এ এম সি + ১ মাস subscription or digital service.','এ এম সি + ১ মাস',NULL,NULL,NULL,249.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:31:37','এ এম সি + ১ মাস','এ এম সি + ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (378,'aee9e56d-0207-476e-8a9d-015633ebd0e7',NULL,42,NULL,'কিউরিওসিটি স্ট্রিম ৩ মাস','kiurioositi-strim-3-mas','EFLIX-349',NULL,'simple','piece','কিউরিওসিটি স্ট্রিম ৩ মাস subscription or digital service.','কিউরিওসিটি স্ট্রিম ৩ মাস',NULL,NULL,NULL,899.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:31:43','কিউরিওসিটি স্ট্রিম ৩ মাস','কিউরিওসিটি স্ট্রিম ৩ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (379,'4a0aa986-fa23-4692-93dc-411d662889c4',NULL,46,NULL,'মেডিটেশন ১ মাস','meditesn-1-mas','EFLIX-347',NULL,'simple','piece','মেডিটেশন ১ মাস subscription or digital service.','মেডিটেশন ১ মাস',NULL,NULL,NULL,399.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:31:48','মেডিটেশন ১ মাস','মেডিটেশন ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (380,'bf0a95dd-a7bb-4a25-bbe2-aadca1157f88',NULL,39,NULL,'গুগল ড্রাইভ ১ বছর','gugl-draiv-1-bchr','EFLIX-345',NULL,'simple','piece','গুগল ড্রাইভ ১ বছর subscription or digital service.','গুগল ড্রাইভ ১ বছর',NULL,NULL,NULL,2199.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:31:54','গুগল ড্রাইভ ১ বছর','গুগল ড্রাইভ ১ বছর',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (381,'4c3f67be-8d6a-4da4-b644-35fbea08e628',NULL,43,NULL,'100 আই টিউনস গিফট কার্ড','100-ai-tiuns-gift-kard','EFLIX-343',NULL,'simple','piece','100 আই টিউনস গিফট কার্ড subscription or digital service.','100 আই টিউনস গিফট কার্ড',NULL,NULL,NULL,13000.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:32:00','100 আই টিউনস গিফট কার্ড','100 আই টিউনস গিফট কার্ড',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (382,'b2d2e9ac-b71f-49de-9080-f824e94eaf19',NULL,46,NULL,'ডুয়োলিংগো সুপার','duzolinggo-supar','EFLIX-341',NULL,'simple','piece','ডুয়োলিংগো সুপার subscription or digital service.','ডুয়োলিংগো সুপার',NULL,NULL,NULL,450.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:32:04','ডুয়োলিংগো সুপার','ডুয়োলিংগো সুপার',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (383,'266f80e7-0d8c-401f-aaf3-4f6dafc6c1e4',NULL,46,NULL,'ইউডেমি কোর্স','iudemi-kors','EFLIX-339',NULL,'simple','piece','ইউডেমি কোর্স subscription or digital service.','ইউডেমি কোর্স',NULL,NULL,NULL,599.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:32:40','ইউডেমি কোর্স','ইউডেমি কোর্স',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (384,'9d080c1b-3e33-4643-8580-8b9b5003142b',NULL,42,NULL,'প্যারামাউন্ট প্লাস ১ মাস','pzaramaunt-plas-1-mas','EFLIX-337',NULL,'simple','piece','প্যারামাউন্ট প্লাস ১ মাস subscription or digital service.','প্যারামাউন্ট প্লাস ১ মাস',NULL,NULL,NULL,199.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:33:37','প্যারামাউন্ট প্লাস ১ মাস','প্যারামাউন্ট প্লাস ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (385,'827f688d-65ec-4904-a9aa-174f2c3e5bcb',NULL,42,NULL,'শো-টাইম ১ মাস','so-taim-1-mas','EFLIX-335',NULL,'simple','piece','শো-টাইম ১ মাস subscription or digital service.','শো-টাইম ১ মাস',NULL,NULL,NULL,269.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:33:43','শো-টাইম ১ মাস','শো-টাইম ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (386,'3df759a2-0455-49db-afe7-698f631c5107',NULL,42,NULL,'এমাজন প্রাইম ভিডিও লিমিটেড টাইম অফার ৬ মাস','emajn-praim-vidioo-limited-taim-ofar-6-mas','EFLIX-334',NULL,'simple','piece','এমাজন প্রাইম ভিডিও লিমিটেড টাইম অফার ৬ মাস subscription or digital service.','এমাজন প্রাইম ভিডিও লিমিটেড টাইম অফার ৬ মাস',NULL,NULL,NULL,1699.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:33:48','এমাজন প্রাইম ভিডিও লিমিটেড টাইম অফার ৬ মাস','এমাজন প্রাইম ভিডিও লিমিটেড টাইম অফার ৬ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (387,'d48f2595-0a19-4aec-9d34-f09a1b2dc5ab',NULL,42,NULL,'ইউটিউব প্রিমিয়াম','iutiub-primiyam','EFLIX-332',NULL,'simple','piece','ইউটিউব প্রিমিয়াম subscription or digital service.','ইউটিউব প্রিমিয়াম',NULL,NULL,NULL,16.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:33:52','ইউটিউব প্রিমিয়াম','ইউটিউব প্রিমিয়াম',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (388,'477ef9cc-3612-45cc-8737-60d40ff8257b',NULL,43,NULL,'75 TL নেটফ্লিক্স গিফট কার্ড','75-tl-netfliks-gift-kard','EFLIX-328',NULL,'simple','piece','75 TL নেটফ্লিক্স গিফট কার্ড subscription or digital service.','75 TL নেটফ্লিক্স গিফট কার্ড',NULL,NULL,NULL,950.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:33:58','75 TL নেটফ্লিক্স গিফট কার্ড','75 TL নেটফ্লিক্স গিফট কার্ড',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (389,'0d678794-7ca8-4f53-a63c-ddd8a91eb81d',NULL,41,NULL,'চ্যাটজিপিটি প্লাস  অ্যাকাউন্ট','czatjipiti-plas-azkaunt','EFLIX-326',NULL,'simple','piece','চ্যাটজিপিটি প্লাস  অ্যাকাউন্ট subscription or digital service.','চ্যাটজিপিটি প্লাস  অ্যাকাউন্ট',NULL,NULL,NULL,699.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:34:04','চ্যাটজিপিটি প্লাস  অ্যাকাউন্ট','চ্যাটজিপিটি প্লাস  অ্যাকাউন্ট',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (390,'0a6394e4-1195-413b-a4c8-138c4c262dee',NULL,46,NULL,'গ্রামারলি প্রিমিয়াম ১ মাস','gramarli-primiyam-1-mas','EFLIX-324',NULL,'simple','piece','গ্রামারলি প্রিমিয়াম ১ মাস subscription or digital service.','গ্রামারলি প্রিমিয়াম ১ মাস',NULL,NULL,NULL,349.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:34:10','গ্রামারলি প্রিমিয়াম ১ মাস','গ্রামারলি প্রিমিয়াম ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (391,'11f29756-6a24-4482-a4d8-55bac75b1f89',NULL,42,NULL,'মুবি এক মাস','mubi-ek-mas','EFLIX-317',NULL,'simple','piece','মুবি এক মাস subscription or digital service.','মুবি এক মাস',NULL,NULL,NULL,249.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:34:26','মুবি এক মাস','মুবি এক মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (392,'94dc3280-6c65-4c12-b01a-7f9916851df7',NULL,42,NULL,'এইচবিও ম্যাক্স ১ মাস','eicbioo-mzaks-1-mas','EFLIX-315',NULL,'simple','piece','এইচবিও ম্যাক্স ১ মাস subscription or digital service.','এইচবিও ম্যাক্স ১ মাস',NULL,NULL,NULL,249.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:34:49','এইচবিও ম্যাক্স ১ মাস','এইচবিও ম্যাক্স ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (393,'f5053109-a1a8-44f6-8880-b5eb01e2dd2d',NULL,47,NULL,'সার্ফ-সার্ক প্রিমিয়াম ভিপিএন  ১ ডিভাইস','sarf-sark-primiyam-vipien-1-divais','EFLIX-298',NULL,'simple','piece','সার্ফ-সার্ক প্রিমিয়াম ভিপিএন  ১ ডিভাইস subscription or digital service.','সার্ফ-সার্ক প্রিমিয়াম ভিপিএন  ১ ডিভাইস',NULL,NULL,NULL,159.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:35:12','সার্ফ-সার্ক প্রিমিয়াম ভিপিএন  ১ ডিভাইস','সার্ফ-সার্ক প্রিমিয়াম ভিপিএন  ১ ডিভাইস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (394,'7c182d52-6a19-4f18-a797-079a343165cb',NULL,42,NULL,'ডিজনি প্লাস ১ মাস','dijni-plas-1-mas','EFLIX-293',NULL,'simple','piece','ডিজনি প্লাস ১ মাস subscription or digital service.','ডিজনি প্লাস ১ মাস',NULL,NULL,NULL,239.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:35:20','ডিজনি প্লাস ১ মাস','ডিজনি প্লাস ১ মাস',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (395,'5c1df561-5fc1-4d8d-a8d6-87d7fb30e6fb',NULL,42,NULL,'হই!চই!','hici','EFLIX-280',NULL,'simple','piece','হই!চই! subscription or digital service.','হই!চই!',NULL,NULL,NULL,119.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:35:57','হই!চই!','হই!চই!',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (396,'ed445d09-8993-453c-84d5-bfda0c370e38',NULL,48,NULL,'স্পোটিফাই পারসোনাল একাউন্ট','spotifai-parsonal-ekaunt','EFLIX-266',NULL,'simple','piece','স্পোটিফাই পারসোনাল একাউন্ট subscription or digital service.','স্পোটিফাই পারসোনাল একাউন্ট',NULL,NULL,NULL,179.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:36:43','স্পোটিফাই পারসোনাল একাউন্ট','স্পোটিফাই পারসোনাল একাউন্ট',NULL,NULL,'2026-09-12 01:55:12','2026-09-12 01:55:12',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (397,'91ff2cba-f107-4cba-af6a-07fadc4c891b',NULL,42,NULL,'হুলু','hulu','EFLIX-262',NULL,'simple','piece','হুলু ১ মাস subscription or digital service.','হুলু ১ মাস',NULL,NULL,NULL,269.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-07 13:36:54','হুলু ১ মাস','হুলু ১ মাস',NULL,48,'2026-09-12 01:55:12','2026-09-12 06:39:07',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (398,'3292e3e0-8185-4549-af25-ef325f28be41',NULL,45,NULL,'নেটফ্লিক্স 4K প্রিমিয়াম অ্যাকাউন্ট','netflix-4k-premium-account-1','EFLIX-5360a',NULL,'simple',NULL,'নেটফ্লিক্স 4K প্রিমিয়াম অ্যাকাউন্ট','নেটফ্লিক্স 4K প্রিমিয়াম অ্যাকাউন্ট',300.00,NULL,NULL,389.00,NULL,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-15 08:00:34',NULL,NULL,48,48,'2026-09-15 07:59:36','2026-09-18 14:51:19',NULL);
INSERT INTO `products` (`id`, `uuid`, `vendor_id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `barcode`, `product_type`, `unit`, `description`, `short_description`, `purchase_price`, `entrepreneur_profit_percent`, `entrepreneur_profit_flat_rate`, `regular_price`, `sale_price`, `tax_rate`, `weight`, `length`, `width`, `height`, `minimum_order_quantity`, `maximum_order_quantity`, `status`, `featured`, `published_at`, `meta_title`, `meta_description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (399,'31c229bc-659d-4e66-91ac-cd4af35e6705',NULL,45,NULL,'নেটফ্লিক্স 4K প্রিমিয়াম অ্যাকাউন্ট ২ মাস ১ স্ক্রিন','netflix-4k-premium-account-2m-1','netflix2m1',NULL,'simple',NULL,NULL,NULL,NULL,NULL,NULL,792.00,792.00,0.00,NULL,NULL,NULL,NULL,1,NULL,'published',0,'2026-09-16 12:38:38',NULL,NULL,48,48,'2026-09-16 12:32:53','2026-09-16 12:38:38',NULL);
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `barcode`, `purchase_price`, `price`, `sale_price`, `weight`, `image_path`, `is_default`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,397,'EFLIX262-VAR-1',NULL,NULL,200.00,NULL,NULL,NULL,0,'2026-09-12 06:35:21','2026-09-12 06:35:21',NULL);
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `barcode`, `purchase_price`, `price`, `sale_price`, `weight`, `image_path`, `is_default`, `created_at`, `updated_at`, `deleted_at`) VALUES (2,397,'EFLIX262-VAR-2',NULL,NULL,400.00,NULL,NULL,NULL,0,'2026-09-12 06:37:56','2026-09-12 06:37:56',NULL);
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `barcode`, `purchase_price`, `price`, `sale_price`, `weight`, `image_path`, `is_default`, `created_at`, `updated_at`, `deleted_at`) VALUES (3,397,'EFLIX262-VAR-3',NULL,NULL,600.00,NULL,NULL,NULL,0,'2026-09-12 06:38:10','2026-09-12 06:38:10',NULL);
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `barcode`, `purchase_price`, `price`, `sale_price`, `weight`, `image_path`, `is_default`, `created_at`, `updated_at`, `deleted_at`) VALUES (4,338,'EFLIX5360-VAR-1',NULL,NULL,350.00,NULL,NULL,NULL,0,'2026-09-14 22:49:07','2026-09-14 22:49:07',NULL);
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `barcode`, `purchase_price`, `price`, `sale_price`, `weight`, `image_path`, `is_default`, `created_at`, `updated_at`, `deleted_at`) VALUES (5,338,'EFLIX5360-VAR-2',NULL,NULL,389.00,NULL,NULL,NULL,0,'2026-09-15 06:14:03','2026-09-15 06:14:03',NULL);
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `barcode`, `purchase_price`, `price`, `sale_price`, `weight`, `image_path`, `is_default`, `created_at`, `updated_at`, `deleted_at`) VALUES (6,338,'EFLIX5360-VAR-3',NULL,NULL,778.00,NULL,NULL,NULL,0,'2026-09-15 06:16:32','2026-09-15 06:16:32',NULL);
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `barcode`, `purchase_price`, `price`, `sale_price`, `weight`, `image_path`, `is_default`, `created_at`, `updated_at`, `deleted_at`) VALUES (7,398,'EFLIX5360A-VAR-1',NULL,NULL,350.00,NULL,NULL,NULL,0,'2026-09-15 08:00:07','2026-09-15 08:00:07',NULL);
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `barcode`, `purchase_price`, `price`, `sale_price`, `weight`, `image_path`, `is_default`, `created_at`, `updated_at`, `deleted_at`) VALUES (8,398,'EFLIX5360A-VAR-2',NULL,NULL,700.00,NULL,NULL,NULL,0,'2026-09-15 08:00:26','2026-09-15 08:00:26',NULL);
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `barcode`, `purchase_price`, `price`, `sale_price`, `weight`, `image_path`, `is_default`, `created_at`, `updated_at`, `deleted_at`) VALUES (9,398,'EFLIX5360A-VAR-3',NULL,NULL,792.00,NULL,NULL,NULL,0,'2026-09-16 08:20:28','2026-09-16 08:20:28',NULL);
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `barcode`, `purchase_price`, `price`, `sale_price`, `weight`, `image_path`, `is_default`, `created_at`, `updated_at`, `deleted_at`) VALUES (10,398,'EFLIX5360A-VAR-4',NULL,NULL,1169.00,NULL,NULL,NULL,0,'2026-09-16 08:21:32','2026-09-16 08:21:32',NULL);
INSERT INTO `product_variant_attribute_value` (`product_variant_id`, `attribute_value_id`) VALUES (1,12);
INSERT INTO `product_variant_attribute_value` (`product_variant_id`, `attribute_value_id`) VALUES (5,12);
INSERT INTO `product_variant_attribute_value` (`product_variant_id`, `attribute_value_id`) VALUES (7,12);
INSERT INTO `product_variant_attribute_value` (`product_variant_id`, `attribute_value_id`) VALUES (8,12);
INSERT INTO `product_variant_attribute_value` (`product_variant_id`, `attribute_value_id`) VALUES (2,13);
INSERT INTO `product_variant_attribute_value` (`product_variant_id`, `attribute_value_id`) VALUES (6,13);
INSERT INTO `product_variant_attribute_value` (`product_variant_id`, `attribute_value_id`) VALUES (9,13);
INSERT INTO `product_variant_attribute_value` (`product_variant_id`, `attribute_value_id`) VALUES (3,14);
INSERT INTO `product_variant_attribute_value` (`product_variant_id`, `attribute_value_id`) VALUES (10,14);
INSERT INTO `product_variant_attribute_value` (`product_variant_id`, `attribute_value_id`) VALUES (7,15);
INSERT INTO `product_variant_attribute_value` (`product_variant_id`, `attribute_value_id`) VALUES (9,15);
INSERT INTO `product_variant_attribute_value` (`product_variant_id`, `attribute_value_id`) VALUES (10,15);
INSERT INTO `product_variant_attribute_value` (`product_variant_id`, `attribute_value_id`) VALUES (8,16);
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (3,208,NULL,'products/208/3638cd4b-51bc-4ddb-ac4c-d6950e9a94f2.webp','জেমিনি','জেমিনি',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:55');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (4,209,NULL,'products/209/9febfccc-71da-45de-b7ed-d0c2d1cdba95.webp','ক্লড এআই','ক্লড এআই',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:55');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (5,210,NULL,'products/210/064333a8-3959-4bb1-a8a0-4d09f6c3bf66.webp','জিপিটি','জিপিটি',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:55');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (6,211,NULL,'products/211/1c0d6f78-2428-4219-8773-8c07dfa4cbe9.webp','রেপ্লিট প্রো ১ মাস','রেপ্লিট প্রো ১ মাস',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:56');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (7,212,NULL,'products/212/bcc55526-e482-473a-bed4-d5ce2b1502fe.webp','ল্যাভেবল ১ মাস','ল্যাভেবল ১ মাস',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:56');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (8,213,NULL,'products/213/fb43b829-6dde-4323-90f7-e9160ebb6050.webp','আইক্লাউড','আইক্লাউড',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:56');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (9,214,NULL,'products/214/5fdccb8e-5dd1-42c7-a54c-8e005ec2b5e1.webp','আইডিওগ্রাম ১ মাস','আইডিওগ্রাম ১ মাস',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:56');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (10,215,NULL,'products/215/735fb658-37f0-4116-977c-8b550e9b3085.webp','ক্যাপকাট প্রো ১ মাস','ক্যাপকাট প্রো ১ মাস',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:56');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (11,216,NULL,'products/216/383a6945-f2a9-499d-8cff-dbb085d68206.webp','চ্যাটজিপিটি গো  অ্যাকাউন্ট ১ বছর','চ্যাটজিপিটি গো  অ্যাকাউন্ট ১ বছর',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:57');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (12,217,NULL,'products/217/1a0b0082-4ef1-4039-95f5-b7d3074b6a47.webp','২টিবি গুগল ড্রাইভ ও জেমিনি অ্যাডভান্সড ১ বছর','২টিবি গুগল ড্রাইভ ও জেমিনি অ্যাডভান্সড ১ বছর',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:57');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (13,218,NULL,'products/218/a006d58a-8dfc-40a0-b26c-cc1ddc22d19b.webp','জেমিনি অ্যাডভান্সড ১ মাস','জেমিনি অ্যাডভান্সড ১ মাস',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:57');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (14,219,NULL,'products/219/7018477c-bda9-44b4-a248-25a4bc5bae83.webp','গুগল ড্রাইভ ও জেমিনি অ্যাডভান্সড ১০ মাস','গুগল ড্রাইভ ও জেমিনি অ্যাডভান্সড ১০ মাস',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:57');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (15,220,NULL,'products/220/d2519f3b-6988-4b7a-9a67-ca8f865bf4eb.webp','ম্যানুস এআই ১ মাস পার্সোনাল','ম্যানুস এআই ১ মাস পার্সোনাল',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:58');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (16,221,NULL,'products/221/d635c6a5-3dcf-46de-b7a3-4d91ee07bb7d.webp','কম্বো প্যাক (নেটফ্লিক্স + ইউটিউব প্রিমিয়াম) ১ মাস','কম্বো প্যাক (নেটফ্লিক্স + ইউটিউব প্রিমিয়াম) ১ মাস',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:58');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (17,222,NULL,'products/222/3ffd01a8-6d2a-43c0-9cf2-80cedf71b830.webp','পিআইএ ভিপিএন ৮ মাস','পিআইএ ভিপিএন ৮ মাস',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:58');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (18,223,NULL,'products/223/9997d78f-e305-46d4-8bd6-1d8423a9a2cb.webp','আভিরা ভিপিএন ৩ মাস','আভিরা ভিপিএন ৩ মাস',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:58');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (19,224,NULL,'products/224/763f6099-4267-4aab-8605-4166af0cb646.webp','টেলিগ্রাম প্রিমিয়াম ১ বছর','টেলিগ্রাম প্রিমিয়াম ১ বছর',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:59');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (20,225,NULL,'products/225/8733c475-1b80-45af-b8fc-a25a7962cbbc.webp','নর্ড ভিপিএন','নর্ড ভিপিএন',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:59');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (21,226,NULL,'products/226/6d963012-fd79-4422-85e9-c1907ebb2e2e.webp','ট্রু কলার ১ বছর','ট্রু কলার ১ বছর',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:59');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (22,227,NULL,'products/227/bd1eae48-c281-4217-85be-4aea08e015e4.webp','আই ডি এম লাইফ টাইম','আই ডি এম লাইফ টাইম',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:59');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (23,228,NULL,'products/228/1815f60e-8eef-459a-a282-971c01833bf4.webp','রুন মিউজিক ১ মাস','রুন মিউজিক ১ মাস',1,0,'2026-09-12 01:54:32','2026-10-09 02:00:59');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (24,229,NULL,'products/229/0b783a9d-e1b2-43bd-adf8-67a37e0d096a.webp','সং স্টার প্লাস ১ মাস','সং স্টার প্লাস ১ মাস',1,0,'2026-09-12 01:54:32','2026-10-09 02:01:00');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (25,230,NULL,'products/230/2c33559f-6483-4eab-a175-f27c1764c943.webp','হ্যালোটক','হ্যালোটক',1,0,'2026-09-12 01:54:32','2026-10-09 02:01:00');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (26,231,NULL,'products/231/779c9785-c67f-4549-b667-b0759f6c229b.webp','টানটান','টানটান',1,0,'2026-09-12 01:54:32','2026-10-09 02:01:00');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (27,232,NULL,'products/232/854a37f9-f34a-4b00-8b24-5551dddf4d10.webp','টিন্ডার ১ মাস','টিন্ডার ১ মাস',1,0,'2026-09-12 01:54:32','2026-10-09 02:01:00');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (28,233,NULL,'products/233/ae408b52-4129-48f7-93d5-4c16fcf4a8cc.webp','ফ্লাইট রাডার বিজনেস প্রিমিয়াম ১ মাস','ফ্লাইট রাডার বিজনেস প্রিমিয়াম ১ মাস',1,0,'2026-09-12 01:54:32','2026-10-09 02:01:01');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (29,234,NULL,'products/234/8ae10fca-c8ce-4fe8-85fd-b49f4768b180.webp','আলো মুভস প্রিমিয়াম ২ মাস','আলো মুভস প্রিমিয়াম ২ মাস',1,0,'2026-09-12 01:54:32','2026-10-09 02:01:01');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (30,235,NULL,'products/235/4dc79c01-8900-47d5-822c-5607ce5c1dae.webp','ব্লিঙ্কিস্ট প্রিমিয়াম','ব্লিঙ্কিস্ট প্রিমিয়াম',1,0,'2026-09-12 01:54:32','2026-10-09 02:01:01');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (31,236,NULL,'products/236/bf25bade-8169-4af0-aecc-8d6ab2d65ce7.webp','কাহুট! ১ মাস','কাহুট! ১ মাস',1,0,'2026-09-12 01:54:32','2026-10-09 02:01:01');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (32,237,NULL,'products/237/58924548-0953-492c-8df4-a99a06f499cb.webp','মাইন্ডভ্যালি ১ মাস','মাইন্ডভ্যালি ১ মাস',1,0,'2026-09-12 01:54:32','2026-10-09 02:01:02');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (33,238,NULL,'products/238/47ef8388-88c2-4a44-9064-fee101ad0218.webp','প্রেস রিডার','প্রেস রিডার',1,0,'2026-09-12 01:54:32','2026-10-09 02:01:02');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (34,239,NULL,'products/239/eaef080f-e97a-4f85-95f3-543e14f9be2c.webp','চেস.কম ১ মাস','চেস.কম ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:02');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (35,240,NULL,'products/240/68200fe8-c4d0-41a0-bc98-ffdc8823fd74.webp','ফরচুন প্রো ১ মাস','ফরচুন প্রো ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:02');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (36,241,NULL,'products/241/60eae787-024e-4149-b3c8-4427190b72b0.webp','এক্সমাইন্ড প্রো ১ বছর','এক্সমাইন্ড প্রো ১ বছর',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:03');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (37,242,NULL,'products/242/cf8e8b58-7147-4202-8e0a-e43447edb2fe.webp','টুনলি এন্টারপ্রাইজ ১ মাস','টুনলি এন্টারপ্রাইজ ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:03');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (38,243,NULL,'products/243/79aee95d-3755-49d2-95ec-c7300622fd5d.webp','অ্যাডোবি এক্সপ্রেস ১ মাস','অ্যাডোবি এক্সপ্রেস ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:03');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (39,244,NULL,'products/244/e966ab11-dc49-4142-adde-136ad516b17d.webp','অ্যাডোবি ক্রিয়েটিভ ক্লাউড','অ্যাডোবি ক্রিয়েটিভ ক্লাউড',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:03');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (40,245,NULL,'products/245/47b340a6-754e-4c1c-ac06-c39beb13b6c1.webp','গিটহাব কপিলট ১ মাস','গিটহাব কপিলট ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:04');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (41,246,NULL,'products/246/5013be97-f80c-4741-9d48-881404ca4176.webp','আইকনফাইন্ডার ১ মাস','আইকনফাইন্ডার ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:04');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (42,247,NULL,'products/247/0f39fb7d-e910-43f5-9f44-ea6808d0b5eb.webp','স্ক্রিমিং ফ্রগ ১ বছর','স্ক্রিমিং ফ্রগ ১ বছর',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:04');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (43,248,NULL,'products/248/a1480c65-3f10-419e-be96-db9799d7456e.webp','বিগস্পাই প্রো ১ মাস','বিগস্পাই প্রো ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:04');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (44,249,NULL,'products/249/815fcc0e-a813-40ca-ae17-df38e1ba7bf2.webp','প্লেএইচটি ক্রিয়েটর ১ মাস','প্লেএইচটি ক্রিয়েটর ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:04');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (45,250,NULL,'products/250/dea23c51-07d5-4336-b9f9-05f4f0979020.webp','ওয়েভ.ভিডিও ক্রিয়েটর ১ মাস','ওয়েভ.ভিডিও ক্রিয়েটর ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:05');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (46,251,NULL,'products/251/3a53258a-1658-4529-ae4c-831239754a27.webp','মার্কেটমিউজ ১ মাস','মার্কেটমিউজ ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:05');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (47,252,NULL,'products/252/4b1bf8d8-7823-4c97-a01e-226bcd7a750d.webp','হুটসুইট ১ মাস','হুটসুইট ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:05');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (48,253,NULL,'products/253/3f135619-c25c-448d-8aa4-84ea4f809d8d.webp','অরিজিনালিটি এআই ১ মাস','অরিজিনালিটি এআই ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:05');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (49,254,NULL,'products/254/c159c97e-8f2d-4b1f-bec9-6227da3699f3.webp','ফিলমোরা ১৩ লাইফ টাইম','ফিলমোরা ১৩ লাইফ টাইম',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:06');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (50,255,NULL,'products/255/94e00b08-fb2e-46bf-8f9e-21d69d01832e.webp','ট্রাই হ্যাক মি ১ মাস','ট্রাই হ্যাক মি ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:06');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (51,256,NULL,'products/256/79525104-55c2-4270-aca7-b20b114e5142.webp','ফ্রীপিক ১ মাস','ফ্রীপিক ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:06');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (52,257,NULL,'products/257/51d247ba-13d7-4bf2-be71-1f17ddc2609d.webp','এনভাটো এলিমেন্টস ১ মাস','এনভাটো এলিমেন্টস ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:06');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (53,258,NULL,'products/258/5f875129-d035-451e-be91-a5ac502ade2f.webp','ইলেভেন ল্যাবস ১ মাস','ইলেভেন ল্যাবস ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:07');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (54,259,NULL,'products/259/ac3a5eb5-5381-4220-ac26-3387af16e534.webp','ভিড আই কিউ ১ মাস','ভিড আই কিউ ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:07');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (55,260,NULL,'products/260/24788e75-b9a1-4014-80db-03910f9ed5ba.webp','পিএনজি ট্রি ১ বছর','পিএনজি ট্রি ১ বছর',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:07');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (56,261,NULL,'products/261/27d8b04a-eeea-4d5a-8c54-83931dc7dcaf.webp','মাইকমনস্টার ৩ মাস','মাইকমনস্টার ৩ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:07');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (57,262,NULL,'products/262/e4be9f31-3166-407e-a179-e8a2defdbe2d.webp','ওয়েলসেড ল্যাবস, মেকার ১ মাস','ওয়েলসেড ল্যাবস, মেকার ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:08');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (58,263,NULL,'products/263/77c20718-0bde-4eeb-b76d-0c758a5c7544.webp','হিলিয়াম 10 ১ মাস','হিলিয়াম 10 ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:08');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (59,264,NULL,'products/264/cdba1f0e-d12a-471a-87a2-8daef85c2770.webp','কিটল ১ মাস','কিটল ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:08');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (60,265,NULL,'products/265/61dde817-c4b1-4b2e-8b67-7d556b69f8c1.webp','ফিগমা ১ মাস','ফিগমা ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:08');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (61,266,NULL,'products/266/fb8e1a78-ebb0-4053-956d-06db1216dfc8.webp','মোজ প্রো ১ মাস','মোজ প্রো ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:09');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (62,267,NULL,'products/267/b27ed8fb-d79a-4efc-a801-c51e88129a99.webp','রাইটসোনিক ১ মাস','রাইটসোনিক ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:09');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (63,268,NULL,'products/268/56fde4ca-1a68-4f79-8dfe-4f8157911a3b.webp','ফ্ল্যাটিকন পার্সোনাল ১ মাস','ফ্ল্যাটিকন পার্সোনাল ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:09');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (64,269,NULL,'products/269/bfa5fe8c-7c3d-4b7f-80ff-b9b469cd2138.webp','ফ্রিপিক পার্সোনাল ১ মাস','ফ্রিপিক পার্সোনাল ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:09');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (65,270,NULL,'products/270/a73e6801-81c4-4423-8b4c-f05531bb5f93.webp','অল উইন্ডোজ কী','অল উইন্ডোজ কী',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:10');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (66,271,NULL,'products/271/278ceb6d-77ff-4825-8b92-2712bd8a04fc.webp','রিজুম কোচ ১ মাস','রিজুম কোচ ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:10');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (67,272,NULL,'products/272/acc89df6-524d-4cd3-b6de-2618a2666d14.webp','স্টকচার্ট ১ মাস','স্টকচার্ট ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:10');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (68,273,NULL,'products/273/58eb9253-91c1-4bb0-bb11-4717b1e564b3.webp','তাবনিন প্রো ১৪ দিন','তাবনিন প্রো ১৪ দিন',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:10');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (69,274,NULL,'products/274/6b05aafb-c44a-43f5-b661-cfc139c08b3a.webp','ব্রাইন.এফএম ৩ মাস','ব্রাইন.এফএম ৩ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:10');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (70,275,NULL,'products/275/c45de382-b483-4cf2-9ec0-3a414a9faa7a.webp','ওভারলিফ','ওভারলিফ',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:11');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (71,276,NULL,'products/276/11bf72f6-73bf-4a78-af18-83812e160365.webp','ক্রাঞ্চবেস ১ মাস','ক্রাঞ্চবেস ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:11');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (72,277,NULL,'products/277/cfef0723-0e48-4a9d-9005-37d3927236e8.webp','প্রেজি ১ মাস','প্রেজি ১ মাস',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:11');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (73,278,NULL,'products/278/45a83a41-78ca-4a4b-b6c9-9137eac78ffc.webp','বায়োরেন্ডার ১৪ দিন','বায়োরেন্ডার ১৪ দিন',1,0,'2026-09-12 01:54:33','2026-10-09 02:01:11');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (74,279,NULL,'products/279/c4690799-df65-4ff4-a831-fb12a52bfff5.webp','ডিজাইন.এআই ৭ দিন','ডিজাইন.এআই ৭ দিন',1,0,'2026-09-12 01:54:34','2026-10-09 02:01:12');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (75,280,NULL,'products/280/67f1d847-b3f5-46d4-9705-1dfb453d537f.webp','লিঙ্কডইন প্রিমিয়াম পার্সোনাল','লিঙ্কডইন প্রিমিয়াম পার্সোনাল',1,0,'2026-09-12 01:54:34','2026-10-09 02:01:12');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (76,281,NULL,'products/281/51b2cd0c-c345-41e2-98f8-d812ceeefda6.webp','আর ওয়াই টি আর ১ মাস','আর ওয়াই টি আর ১ মাস',1,0,'2026-09-12 01:54:35','2026-10-09 02:01:12');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (77,282,NULL,'products/282/810ceeea-3c78-4e50-8e5e-141f51c102ea.webp','ব্রেইনলি প্লাস ১ মাস','ব্রেইনলি প্লাস ১ মাস',1,0,'2026-09-12 01:54:35','2026-10-09 02:01:12');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (78,283,NULL,'products/283/7ee2211a-5c05-458e-a586-e1992235ecc4.webp','স্পিচিফাই','স্পিচিফাই',1,0,'2026-09-12 01:54:36','2026-10-09 02:01:13');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (79,284,NULL,'products/284/43ad14e8-6609-4f4d-a3b7-80aefc50ed0f.webp','টোম এআই ১ মাস','টোম এআই ১ মাস',1,0,'2026-09-12 01:54:37','2026-10-09 02:01:13');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (80,285,NULL,'products/285/df621869-8474-4fbd-8d74-14b06e731e21.webp','স্পেসলি এআই ৩ দিন','স্পেসলি এআই ৩ দিন',1,0,'2026-09-12 01:54:40','2026-10-09 02:01:13');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (81,286,NULL,'products/286/02e3a2f9-4f96-4f93-80ed-8b7adedecc9c.webp','সাইডার এআই ১ মাস','সাইডার এআই ১ মাস',1,0,'2026-09-12 01:54:40','2026-10-09 02:01:13');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (82,287,NULL,'products/287/3bf78f6e-f563-4598-89bb-29a8782c7fe0.webp','লিটচার্ট এ প্লাস ১ মাস','লিটচার্ট এ প্লাস ১ মাস',1,0,'2026-09-12 01:54:41','2026-10-09 02:01:13');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (83,288,NULL,'products/288/5755eaca-930e-4e87-a4b9-144b6d0aa5e8.webp','জেনি এআই ১ মাস','জেনি এআই ১ মাস',1,0,'2026-09-12 01:54:42','2026-10-09 02:01:14');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (84,289,NULL,'products/289/4d77c67e-09ae-4c1b-bb93-aa4eb283a315.webp','ফটোরুম প্রো ১ মাস','ফটোরুম প্রো ১ মাস',1,0,'2026-09-12 01:54:43','2026-10-09 02:01:14');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (85,290,NULL,'products/290/ea7e2c26-62c4-41b3-ab08-2e285e412914.webp','হুমাটা এআই ১৪ দিন ৫০০ পেজ','হুমাটা এআই ১৪ দিন ৫০০ পেজ',1,0,'2026-09-12 01:54:43','2026-10-09 02:01:14');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (86,291,NULL,'products/291/0e0f0ab7-393e-4c6e-b611-4d60f8511f3a.webp','অথর এআই ১ মাস','অথর এআই ১ মাস',1,0,'2026-09-12 01:54:44','2026-10-09 02:01:14');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (87,292,NULL,'products/292/60f727ab-846b-4d31-ac12-6e08c6c7f931.webp','ক্রিয়েটিভ ফ্যাব্রিকা','ক্রিয়েটিভ ফ্যাব্রিকা',1,0,'2026-09-12 01:54:46','2026-10-09 02:01:15');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (88,293,NULL,'products/293/3efa4d73-852c-4d1d-9966-7b5e619fdce5.webp','ক্রিস্প এআই ১ মাস','ক্রিস্প এআই ১ মাস',1,0,'2026-09-12 01:54:46','2026-10-09 02:01:15');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (89,294,NULL,'products/294/f25fe684-3f81-408b-9ec4-6900cf0876a6.webp','ক্রেয়া এআই ১ মাস','ক্রেয়া এআই ১ মাস',1,0,'2026-09-12 01:54:47','2026-10-09 02:01:15');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (90,295,NULL,'products/295/30eb96cd-f882-4d9d-94e0-e70b22c6e32a.webp','বাইপাস এআই','বাইপাস এআই',1,0,'2026-09-12 01:54:48','2026-10-09 02:01:15');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (91,296,NULL,'products/296/a7491307-e179-463d-b889-711c4c79821b.webp','অ্যাডোব ফায়ারফ্লাইন ১ মাস','অ্যাডোব ফায়ারফ্লাইন ১ মাস',1,0,'2026-09-12 01:54:50','2026-10-09 02:01:15');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (92,297,NULL,'products/297/b09b6b89-662a-451b-ae1f-7d5839028482.webp','ক্রাফট প্রো ১ বছর','ক্রাফট প্রো ১ বছর',1,0,'2026-09-12 01:54:50','2026-10-09 02:01:16');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (93,298,NULL,'products/298/4f8fe9f6-135b-413f-b6eb-cc7130470ad2.webp','নেবুলা প্রিমিয়াম','নেবুলা প্রিমিয়াম',1,0,'2026-09-12 01:54:51','2026-10-09 02:01:16');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (94,299,NULL,'products/299/739fd476-da46-4f12-bdb8-4732468d0719.webp','নভেল এআই ১ মাস পার্সোনাল','নভেল এআই ১ মাস পার্সোনাল',1,0,'2026-09-12 01:54:51','2026-10-09 02:01:16');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (95,300,NULL,'products/300/499357e5-eaaa-4e1b-b192-8918e7863287.webp','লুডউইগ.গুরু ১ মাস','লুডউইগ.গুরু ১ মাস',1,0,'2026-09-12 01:54:53','2026-10-09 02:01:16');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (96,301,NULL,'products/301/fb59aec4-c5a9-46c7-aeda-a33d7c0ec857.webp','অটার এআই ১ মাস পার্সোনাল','অটার এআই ১ মাস পার্সোনাল',1,0,'2026-09-12 01:54:55','2026-10-09 02:01:17');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (97,302,NULL,'products/302/2c9cb854-f74f-417e-ac47-ebf415d8c634.webp','নৌশন এআই ১ মাস','নৌশন এআই ১ মাস',1,0,'2026-09-12 01:54:57','2026-10-09 02:01:17');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (98,303,NULL,'products/303/f1b4b1c3-a030-4a89-93d3-7ff6341366fc.webp','বিউটিফুল.এআই','বিউটিফুল.এআই',1,0,'2026-09-12 01:55:02','2026-10-09 02:01:17');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (99,304,NULL,'products/304/de211400-a85b-4f89-a2dc-cbf81f7b3929.webp','কপি.এআই','কপি.এআই',1,0,'2026-09-12 01:55:03','2026-10-09 02:01:17');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (100,305,NULL,'products/305/e65d1d72-bfb9-4e2d-a5c4-d272752d9aeb.webp','কুইজলেট প্লাস ১ বছর','কুইজলেট প্লাস ১ বছর',1,0,'2026-09-12 01:55:04','2026-10-09 02:01:17');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (101,306,NULL,'products/306/70e8cbc0-bbcd-4d2a-80a1-62277c211412.webp','প্লুরালসাইট ১ মাস','প্লুরালসাইট ১ মাস',1,0,'2026-09-12 01:55:06','2026-10-09 02:01:18');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (102,307,NULL,'products/307/d1038992-7a8b-4ebf-9ab2-e6a87121cf93.webp','বিজনেস ইনসাইডার','বিজনেস ইনসাইডার',1,0,'2026-09-12 01:55:06','2026-10-09 02:01:18');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (103,308,NULL,'products/308/4b2cb4bb-808a-4d8f-b93f-2bbe549dd6a1.webp','ব্রিলিয়ান্ট ১ মাস','ব্রিলিয়ান্ট ১ মাস',1,0,'2026-09-12 01:55:07','2026-10-09 02:01:18');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (104,309,NULL,'products/309/58263111-e19e-4f01-9c8d-d7e2253fb69c.webp','একাডেমিয়া.এডু','একাডেমিয়া.এডু',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:18');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (105,310,NULL,'products/310/4ccf537d-dfc0-498f-ba96-e569ad7a889b.webp','লিব্বি ১ বছর','লিব্বি ১ বছর',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:19');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (106,311,NULL,'products/311/d218557e-77df-4e60-9bc6-f3f2117e28ab.webp','ওয়ান্ড্রিয়াম দ্য গ্রেট কোর্স ১ মাস','ওয়ান্ড্রিয়াম দ্য গ্রেট কোর্স ১ মাস',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:19');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (107,312,NULL,'products/312/fe90d367-21fd-448e-a20d-eb2733ec1469.webp','ম্যাথওয়ে','ম্যাথওয়ে',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:19');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (108,313,NULL,'products/313/7187f699-f228-4f23-9b4f-992032df455f.webp','জিনজার ১ মাস','জিনজার ১ মাস',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:19');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (109,314,NULL,'products/314/2341d318-99be-4a13-b700-1426e07e7769.webp','ল্যাকচারিওর মেডিকেল ১ মাস','ল্যাকচারিওর মেডিকেল ১ মাস',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:20');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (110,315,NULL,'products/315/feb31a69-4a07-4bb2-b5b2-293dad68bf45.webp','মাস্টার ক্লাস','মাস্টার ক্লাস',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:20');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (111,316,NULL,'products/316/ba4a2fe6-2617-417c-97ac-d5e0d44ea4c8.webp','মর্নিং স্টার','মর্নিং স্টার',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:20');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (112,317,NULL,'products/317/110fd864-f311-4997-9e13-beb83bddd25f.webp','স্টাডি.কম','স্টাডি.কম',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:20');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (113,318,NULL,'products/318/27814f27-6af5-43f5-9c41-365058804a52.webp','ডেটা ক্যাম্প','ডেটা ক্যাম্প',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:20');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (114,319,NULL,'products/319/dc1397de-c65e-4baf-8ca0-41357cb520d0.webp','এলসা স্পিক','এলসা স্পিক',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:21');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (115,320,NULL,'products/320/4d8e90d9-9dfa-4948-ab4f-4cfab95ea837.webp','কোডেকাডেমি','কোডেকাডেমি',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:21');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (116,321,NULL,'products/321/582571d7-4f86-49a3-ae06-9615a60ceaef.webp','ফ্লুয়েন্ট ইউ','ফ্লুয়েন্ট ইউ',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:21');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (117,322,NULL,'products/322/4889e2ad-bab3-4135-8c44-6a44cc176872.webp','ম্যাকগ্রা হিল কানেক্ট ১ মাস','ম্যাকগ্রা হিল কানেক্ট ১ মাস',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:21');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (118,323,NULL,'products/323/eac0ccdb-ae9d-4c41-85ed-68a1bd88eea1.webp','পিয়ারসন মাইল্যাব ১ মাস','পিয়ারসন মাইল্যাব ১ মাস',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:22');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (119,324,NULL,'products/324/75045070-d2ad-4119-9736-b0069a2b30fe.webp','কোর্স হিরো ১ মাস','কোর্স হিরো ১ মাস',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:22');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (120,325,NULL,'products/325/feb65cb6-0454-4506-90d9-62882a97434e.webp','স্কিলশেয়ার','স্কিলশেয়ার',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:22');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (121,326,NULL,'products/326/44fd52f9-469e-4965-b3b4-79687d30e5a6.webp','চেগ স্টাডি ১ মাস পার্সোনাল','চেগ স্টাডি ১ মাস পার্সোনাল',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:22');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (122,327,NULL,'products/327/4f90e9aa-69dc-491c-9e6d-a5f534e30f89.webp','কোর্সেরা','কোর্সেরা',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:23');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (123,328,NULL,'products/328/038b34c0-8bdc-43c2-a86f-ca3fd39e39b3.webp','বঙ্গ বিডি','বঙ্গ বিডি',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:23');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (124,329,NULL,'products/329/2abf6bac-7bc4-46a9-b685-2b5349a0bb71.webp','নাউ টিভি ১ মাস পার্সোনাল','নাউ টিভি ১ মাস পার্সোনাল',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:23');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (125,330,NULL,'products/330/e3b1f8a7-4b8b-4998-b40e-31889df6341a.webp','স্লিং টিভি ( ব্লু + অরেঞ্জ) ১ মাস','স্লিং টিভি ( ব্লু + অরেঞ্জ) ১ মাস',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:23');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (126,331,NULL,'products/331/bd75c12a-e45d-418b-88c8-10a75291e564.webp','টেনিস টিভি','টেনিস টিভি',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:23');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (127,332,NULL,'products/332/8dbd6bd5-c02f-4115-87fa-f943140c8ee3.webp','আল্ট বালাজী ১ মাস','আল্ট বালাজী ১ মাস',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:24');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (128,333,NULL,'products/333/ecadeef0-f76f-4f53-b0f7-bbbf26af353f.webp','ক্লিক','ক্লিক',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:24');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (129,334,NULL,'products/334/79a24025-5f8d-4d9d-a0e0-4efa6874112d.webp','অ্যাপল টিভি প্লাস ৩ মাস','অ্যাপল টিভি প্লাস ৩ মাস',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:24');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (130,335,NULL,'products/335/58b5be0a-33d2-4e96-bedd-05a1a4006b5c.webp','এইচবিও গো ১ মাস','এইচবিও গো ১ মাস',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:24');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (131,336,NULL,'products/336/0102eb36-7ae3-44ea-9fde-79838d3a4402.webp','কম্বো প্যাক (নেটফ্লিক্স +আমাজন )','কম্বো প্যাক (নেটফ্লিক্স +আমাজন )',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:24');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (132,337,NULL,'products/337/3d8bec34-fae5-4396-ad98-364cb42bd79a.webp','অ্যামাজন প্রাইম ভিডিও','অ্যামাজন প্রাইম ভিডিও',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:25');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (133,338,NULL,'products/338/eflix-15597.png','নেটফ্লিক্স 4K প্রিমিয়াম অ্যাকাউন্ট','নেটফ্লিক্স 4K প্রিমিয়াম অ্যাকাউন্ট',1,0,'2026-09-12 01:55:11','2026-09-12 01:55:11');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (134,339,NULL,'products/339/8c33499c-9ec9-4397-8329-cec43b7d1c42.webp','হেডস্পেস প্লাস ১ মাস','হেডস্পেস প্লাস ১ মাস',1,0,'2026-09-12 01:55:11','2026-10-09 02:01:25');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (135,340,NULL,'products/340/3c211be2-f7c5-42e1-b4d4-2c51f475eb3d.webp','গুরু ফোকাস প্রিমিয়াম','গুরু ফোকাস প্রিমিয়াম',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:25');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (136,341,NULL,'products/341/f52a42ef-89c3-44d7-b08a-6051d9097c16.webp','দ্য নিউ ইয়র্ক টাইমস ১ মাস','দ্য নিউ ইয়র্ক টাইমস ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:25');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (137,342,NULL,'products/342/e7132a91-ddc4-4ba4-aeaa-dc40a3ea1796.webp','ফাইনান্সিয়াল টাইমস প্রিমিয়াম ১ মাস','ফাইনান্সিয়াল টাইমস প্রিমিয়াম ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:26');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (138,343,NULL,'products/343/f4235357-860c-4f79-b788-22b7879a00e6.webp','সিকিং আলফা ১ মাস','সিকিং আলফা ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:26');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (139,344,NULL,'products/344/81cde332-22e6-4936-ade0-7c23dbca6de6.webp','জুম  প্রো ১ মাস','জুম  প্রো ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:26');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (140,345,NULL,'products/345/1bb0e839-a1ec-4326-93ed-2cefc8ab84dc.webp','কাম মেডিটেশন ১ মাস','কাম মেডিটেশন ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:26');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (141,346,NULL,'products/346/8074a0e8-dcf8-44e3-9cb4-6229ecc67b5c.webp','অডিবল প্রিমিয়াম প্লাস মেম্বেরশিপ ১ মাস + ১ ক্রেডিট','অডিবল প্রিমিয়াম প্লাস মেম্বেরশিপ ১ মাস + ১ ক্রেডিট',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:27');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (142,347,NULL,'products/347/a0fb77c6-9cbd-4b07-9697-db0afcf43818.webp','আই ক্লাউড ১ বছর ১০০ জিবি','আই ক্লাউড ১ বছর ১০০ জিবি',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:27');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (143,348,NULL,'products/348/8766f01a-b34a-41f8-b1f9-6eea80fe0ef1.webp','প্লে স্টেশন প্লাস এসেনশিয়াল ১ মাস','প্লে স্টেশন প্লাস এসেনশিয়াল ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:27');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (144,349,NULL,'products/349/63d5b679-5ede-4dd0-bd7c-8e2b84b8edc8.webp','ইএ প্লে  প্রো ১ মাস','ইএ প্লে  প্রো ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:27');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (145,350,NULL,'products/350/d900ff89-ef0e-4291-81b8-f1584e266f57.webp','এক্সবক্স  এক্স জি পি ইউ গেম পাস ১ মাস','এক্সবক্স  এক্স জি পি ইউ গেম পাস ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:27');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (146,351,NULL,'products/351/2e24bfb6-089c-4eff-a878-245d1f59ba3b.webp','নিন্টেন্ডো সুইচ অনলাইন ১ মাস','নিন্টেন্ডো সুইচ অনলাইন ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:28');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (147,352,NULL,'products/352/2592d326-a1f1-4c05-8a30-70b869b17da1.webp','আলটিমেট গিটার প্রো  ১ মাস','আলটিমেট গিটার প্রো  ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:28');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (148,353,NULL,'products/353/0eff9c46-d0c7-4561-885d-bc38e471fb44.webp','কোবুজ প্রিমিয়াম  ১ মাস','কোবুজ প্রিমিয়াম  ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:28');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (149,354,NULL,'products/354/2b5d716c-6fba-4229-ac8b-d5c77b57644e.webp','ডিজার প্রিমিয়াম ১ মাস','ডিজার প্রিমিয়াম ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:28');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (150,355,NULL,'products/355/05044ac3-e652-4712-8ec9-3c6f5447188e.webp','টাইডাল মিউজিক ১ মাস','টাইডাল মিউজিক ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:29');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (151,356,NULL,'products/356/083b4c19-d1b5-4da6-802f-bdde4b1e514f.webp','ডুডলি এন্টারপ্রাইজ ১ মাস','ডুডলি এন্টারপ্রাইজ ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:29');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (152,357,NULL,'products/357/6daaca1c-414d-43fa-831a-e4c561f8ab5e.webp','অফিস ৩৬৫  ১ বছর','অফিস ৩৬৫  ১ বছর',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:29');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (153,358,NULL,'products/358/d7cf7672-fb96-48a1-ba0e-d7f9b7afeb62.webp','আই লাভ পিডিএফ ১ মাস','আই লাভ পিডিএফ ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:29');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (154,359,NULL,'products/359/23a10194-3bb3-4144-a4c2-518815462499.webp','স্মল পিডিএফ প্রো ১ মাস','স্মল পিডিএফ প্রো ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:29');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (155,360,NULL,'products/360/6db80462-85fe-46f5-b8fe-df579a8cb1dd.webp','ট্রেডিংভিউ প্রিমিয়াম ১ মাস','ট্রেডিংভিউ প্রিমিয়াম ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:30');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (156,361,NULL,'products/361/3c98e891-e049-4f19-bb83-467aab5e3016.webp','টোডোইস্ট প্রিমিয়াম ৩ মাস','টোডোইস্ট প্রিমিয়াম ৩ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:30');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (157,362,NULL,'products/362/6119a27a-55d2-497c-a4b9-3f6ffe5426c6.webp','স্লিপ সাইকেল প্রিমিয়াম ৩ মাস','স্লিপ সাইকেল প্রিমিয়াম ৩ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:30');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (158,363,NULL,'products/363/50af5ea8-a3e6-490c-b4d3-b3c4e124c365.webp','ক্যানভা প্রো','ক্যানভা প্রো',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:30');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (159,364,NULL,'products/364/5cb474cb-1f36-42df-981c-6dda15d6e96c.webp','ইএসপিএন+ ১ মাস','ইএসপিএন+ ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:31');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (160,365,NULL,'products/365/4089c529-2b62-49d3-8afc-400c9e1a9107.webp','চরকি','চরকি',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:31');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (161,366,NULL,'products/366/391be4ff-89b3-405e-ac35-521b875371c2.webp','এমএলবি​ টিভি মেজর লীগ বেসবল  ১ মাস','এমএলবি​ টিভি মেজর লীগ বেসবল  ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:31');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (162,367,NULL,'products/367/23a9864f-023e-4804-a3fe-74e405b28dd0.webp','এনবিএ লিগ পাস ১ মাস','এনবিএ লিগ পাস ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:31');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (163,368,NULL,'products/368/d1da49a1-bdaa-48aa-a35d-cb6fd440dfc2.webp','স্ট্যান প্রিমিয়াম ১ মাস','স্ট্যান প্রিমিয়াম ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:31');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (164,369,NULL,'products/369/ca0d4d13-b2bd-43e4-9c04-3ddbd96ce465.webp','এফ ওয়ান টিভি প্রো ১ মাস','এফ ওয়ান টিভি প্রো ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:32');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (165,370,NULL,'products/370/72987a70-3ac7-495d-b43b-00ba235f3738.webp','স্টারজ ১ মাস','স্টারজ ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:32');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (166,371,NULL,'products/371/c71ce13c-a055-44be-aafd-2b6beeb2d63c.webp','ভিকি রাকুটেন প্লাস ১ মাস','ভিকি রাকুটেন প্লাস ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:32');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (167,372,NULL,'products/372/dd8b937e-e0d1-4182-bd36-60465710b5b3.webp','ক্রাঞ্চিরোল ১ মাস','ক্রাঞ্চিরোল ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:32');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (168,373,NULL,'products/373/704fcb12-01bf-4167-ae02-d6bcdee935e6.webp','অ্যাপল ওয়ান','অ্যাপল ওয়ান',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:33');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (169,374,NULL,'products/374/84f66398-99c0-4f81-ac04-3a478b61fcb1.webp','কুইল বট প্রিমিয়াম ১ মাস','কুইল বট প্রিমিয়াম ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:33');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (170,375,NULL,'products/375/ea650a20-7328-45a4-aba7-943bca17b4dd.webp','টার্নিটিন ১ মাস','টার্নিটিন ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:33');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (171,376,NULL,'products/376/0d3a64c7-8852-4b78-a859-6fd31883a09e.webp','পিকক টিভি ১ মাস','পিকক টিভি ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:33');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (172,377,NULL,'products/377/3a70cde7-4d56-49bc-819b-993f3c107a47.webp','এ এম সি + ১ মাস','এ এম সি + ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:33');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (173,378,NULL,'products/378/2208cb2e-7a29-4acf-ab32-8e3218dded01.webp','কিউরিওসিটি স্ট্রিম ৩ মাস','কিউরিওসিটি স্ট্রিম ৩ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:34');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (174,379,NULL,'products/379/c7d66fd6-3b04-4aa0-8a39-4819c2fbacbd.webp','মেডিটেশন ১ মাস','মেডিটেশন ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:34');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (175,380,NULL,'products/380/c9c03f09-b6dd-4df6-a30d-5a7fa37ef72e.webp','গুগল ড্রাইভ ১ বছর','গুগল ড্রাইভ ১ বছর',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:34');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (176,381,NULL,'products/381/bdfa26d0-ee67-4288-92fd-3479880209ac.webp','100 আই টিউনস গিফট কার্ড','100 আই টিউনস গিফট কার্ড',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:34');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (177,382,NULL,'products/382/d6e8777f-ef85-49b6-84ec-277592a1d506.webp','ডুয়োলিংগো সুপার','ডুয়োলিংগো সুপার',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:35');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (178,383,NULL,'products/383/c51b7019-9a1c-461e-a425-60ea740c2755.webp','ইউডেমি কোর্স','ইউডেমি কোর্স',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:35');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (179,384,NULL,'products/384/e50f9b11-9c33-44f2-9fb3-0813ea5f25cf.webp','প্যারামাউন্ট প্লাস ১ মাস','প্যারামাউন্ট প্লাস ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:35');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (180,385,NULL,'products/385/593ba2fe-16f8-40b9-a6fb-d376e80609aa.webp','শো-টাইম ১ মাস','শো-টাইম ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:35');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (181,386,NULL,'products/386/ef569bd5-b137-421d-ae81-b3beb90475df.webp','এমাজন প্রাইম ভিডিও লিমিটেড টাইম অফার ৬ মাস','এমাজন প্রাইম ভিডিও লিমিটেড টাইম অফার ৬ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:35');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (182,387,NULL,'products/387/c5f34124-930d-4775-aa11-202eaa31396a.webp','ইউটিউব প্রিমিয়াম','ইউটিউব প্রিমিয়াম',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:36');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (183,388,NULL,'products/388/639b3784-c2d3-48bd-9c92-50cf4e0a9f41.webp','75 TL নেটফ্লিক্স গিফট কার্ড','75 TL নেটফ্লিক্স গিফট কার্ড',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:36');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (184,389,NULL,'products/389/f7b246a7-e70e-41b5-a437-fc68c04c438c.webp','চ্যাটজিপিটি প্লাস  অ্যাকাউন্ট','চ্যাটজিপিটি প্লাস  অ্যাকাউন্ট',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:36');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (185,390,NULL,'products/390/3fae8377-4b03-4131-b2b3-0d93c48c605b.webp','গ্রামারলি প্রিমিয়াম ১ মাস','গ্রামারলি প্রিমিয়াম ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:36');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (186,391,NULL,'products/391/86208d1f-f301-44ff-91ae-6acf1be811cd.webp','মুবি এক মাস','মুবি এক মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:36');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (187,392,NULL,'products/392/253113a2-997a-403a-8c19-14a0eb095879.webp','এইচবিও ম্যাক্স ১ মাস','এইচবিও ম্যাক্স ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:37');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (188,393,NULL,'products/393/eflix-15728.png','সার্ফ-সার্ক প্রিমিয়াম ভিপিএন  ১ ডিভাইস','সার্ফ-সার্ক প্রিমিয়াম ভিপিএন  ১ ডিভাইস',1,0,'2026-09-12 01:55:12','2026-09-12 01:55:12');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (189,394,NULL,'products/394/2e0a4342-b234-4637-a450-5af87ebf7e5b.webp','ডিজনি প্লাস ১ মাস','ডিজনি প্লাস ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:37');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (190,395,NULL,'products/395/35244c48-01a0-4031-b903-27c4026858d3.webp','হই!চই!','হই!চই!',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:37');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (191,396,NULL,'products/396/36ce8590-1bd9-4665-96b9-559024f3614d.webp','স্পোটিফাই পারসোনাল একাউন্ট','স্পোটিফাই পারসোনাল একাউন্ট',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:37');
INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `path`, `alt_text`, `title`, `is_thumbnail`, `sort_order`, `created_at`, `updated_at`) VALUES (192,397,NULL,'products/397/69abdf9b-d2a3-4daf-af83-3b9d30ebe2dc.webp','হুলু ১ মাস','হুলু ১ মাস',1,0,'2026-09-12 01:55:12','2026-10-09 02:01:38');

UPDATE `products` SET `vendor_id` = NULL, `created_by` = NULL, `updated_by` = NULL;

-- Initial administrator requested for the cPanel database import.
-- User accounts are intentionally excluded from this import.

SET @initial_admin_id = LAST_INSERT_ID();
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`)
SELECT `id`, 'App\\Models\\User', @initial_admin_id
FROM `roles`
WHERE `name` = 'super_admin' AND `guard_name` = 'web';

SET FOREIGN_KEY_CHECKS=1;
