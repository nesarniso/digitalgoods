-- eflix.store attribute and multi-variation schema patch
-- MySQL 8+/MariaDB. Safe to import on an existing installation.
-- This patch does not delete or modify existing catalog data.

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `attributes` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `attributes_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `attribute_values` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `attribute_id` bigint unsigned NOT NULL,
    `value` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `attribute_values_attribute_id_value_unique` (`attribute_id`, `value`),
    KEY `attribute_values_attribute_id_foreign` (`attribute_id`),
    CONSTRAINT `attribute_values_attribute_id_foreign`
        FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_variants` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `product_id` bigint unsigned NOT NULL,
    `sku` varchar(255) NOT NULL,
    `barcode` varchar(255) DEFAULT NULL,
    `purchase_price` decimal(12,2) DEFAULT NULL,
    `price` decimal(12,2) NOT NULL,
    `sale_price` decimal(12,2) DEFAULT NULL,
    `weight` decimal(10,3) DEFAULT NULL,
    `image_path` varchar(255) DEFAULT NULL,
    `is_default` tinyint(1) NOT NULL DEFAULT '0',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `product_variants_sku_unique` (`sku`),
    UNIQUE KEY `product_variants_barcode_unique` (`barcode`),
    KEY `product_variants_product_id_foreign` (`product_id`),
    CONSTRAINT `product_variants_product_id_foreign`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_variant_attribute_value` (
    `product_variant_id` bigint unsigned NOT NULL,
    `attribute_value_id` bigint unsigned NOT NULL,
    PRIMARY KEY (`product_variant_id`, `attribute_value_id`),
    KEY `pvav_attribute_value_id_foreign` (`attribute_value_id`),
    CONSTRAINT `pvav_product_variant_id_foreign`
        FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `pvav_attribute_value_id_foreign`
        FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Mark the corresponding Laravel migrations as applied when the migrations
-- table exists. This prevents a later `artisan migrate` from recreating them.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_23_000006_create_attributes_table', COALESCE(MAX(`batch`), 0) + 1
FROM `migrations`
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_08_23_000006_create_attributes_table'
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_23_000007_create_product_variants_table', COALESCE(MAX(`batch`), 0) + 1
FROM `migrations`
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_08_23_000007_create_product_variants_table'
);
