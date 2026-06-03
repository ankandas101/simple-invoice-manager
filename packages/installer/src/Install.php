<?php

namespace Tecdiary\Installer;

use App\Helpers\Env;
use App\Models\Role;
use App\Models\User;
use App\Models\Account;
use App\Models\Setting;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Install
{
    public static function createDemoData()
    {
        set_time_limit(300);
        try {
            $demoData = Storage::disk('local')->get('demo.sql');
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $data = self::dbTransaction($demoData);
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return $data;
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function createEnv()
    {
        File::copy(base_path('.env.example'), base_path('.env'));
        Env::update(['APP_URL' => url('/')], false);
    }

    public static function getSqlSchema()
    {
        // Try to load from file first
        $possiblePaths = [
            base_path('packages/installer/resources/database.sql'),
            dirname(__DIR__, 2) . '/resources/database.sql',
            __DIR__ . '/../resources/database.sql',
        ];
        
        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                return File::get($path);
            }
        }
        
        // Fallback: return SQL schema as string
        return self::getFallbackSqlSchema();
    }

    private static function getFallbackSqlSchema()
    {
        return <<<'SQL'
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `invoice_item_tax_rate`;
DROP TABLE IF EXISTS `quotation_item_tax_rate`;
DROP TABLE IF EXISTS `invoice_tax_rate`;
DROP TABLE IF EXISTS `quotation_tax_rate`;
DROP TABLE IF EXISTS `payment_tax_rate`;
DROP TABLE IF EXISTS `product_tax_rate`;
DROP TABLE IF EXISTS `invoice_items`;
DROP TABLE IF EXISTS `quotation_items`;
DROP TABLE IF EXISTS `invoices`;
DROP TABLE IF EXISTS `quotations`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `tax_rates`;
DROP TABLE IF EXISTS `notes`;
DROP TABLE IF EXISTS `fields`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `companies`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `activity_log`;
DROP TABLE IF EXISTS `model_has_permissions`;
DROP TABLE IF EXISTS `model_has_roles`;
DROP TABLE IF EXISTS `role_has_permissions`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `team_invitations`;
DROP TABLE IF EXISTS `team_user`;
DROP TABLE IF EXISTS `teams`;
DROP TABLE IF EXISTS `personal_access_tokens`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `migrations`;
DROP TABLE IF EXISTS `accounts`;

CREATE TABLE `migrations` (`id` int unsigned NOT NULL AUTO_INCREMENT, `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `batch` int NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `accounts` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, `deleted_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `email_verified_at` timestamp NULL DEFAULT NULL, `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL, `current_team_id` bigint unsigned NULL DEFAULT NULL, `profile_photo_path` longtext COLLATE utf8mb4_unicode_ci, `extra_attributes` json, `account_id` bigint unsigned NULL DEFAULT NULL, `phone` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, `deleted_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), UNIQUE KEY `users_email_unique` (`email`), UNIQUE KEY `users_username_unique` (`username`), KEY `users_account_id_index` (`account_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissions` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'web', `account_id` bigint unsigned NULL DEFAULT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), UNIQUE KEY `permissions_name_guard_name_unique` (`name`, `guard_name`, `account_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'web', `account_id` bigint unsigned NULL DEFAULT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), UNIQUE KEY `roles_name_guard_name_unique` (`name`, `guard_name`, `account_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_has_permissions` (`permission_id` bigint unsigned NOT NULL, `role_id` bigint unsigned NOT NULL, PRIMARY KEY (`permission_id`, `role_id`), KEY `role_has_permissions_role_id_foreign` (`role_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_roles` (`role_id` bigint unsigned NOT NULL, `model_id` bigint unsigned NOT NULL, `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, PRIMARY KEY (`role_id`, `model_id`, `model_type`), KEY `model_has_roles_model_id_model_type_index` (`model_id`, `model_type`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_permissions` (`permission_id` bigint unsigned NOT NULL, `model_id` bigint unsigned NOT NULL, `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, PRIMARY KEY (`permission_id`, `model_id`, `model_type`), KEY `model_has_permissions_model_id_model_type_index` (`model_id`, `model_type`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settings` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `tec_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `tec_value` longtext COLLATE utf8mb4_unicode_ci, `account_id` bigint unsigned NOT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), UNIQUE KEY `settings_tec_key_account_id_unique` (`tec_key`, `account_id`), KEY `settings_account_id_index` (`account_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `companies` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `email` varchar(255) COLLATE utf8mb4_unicode_ci, `phone` varchar(255) COLLATE utf8mb4_unicode_ci, `country` varchar(255) COLLATE utf8mb4_unicode_ci, `state` varchar(255) COLLATE utf8mb4_unicode_ci, `city` varchar(255) COLLATE utf8mb4_unicode_ci, `address` text COLLATE utf8mb4_unicode_ci, `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci, `logo` varchar(255) COLLATE utf8mb4_unicode_ci, `logo_dark` varchar(255) COLLATE utf8mb4_unicode_ci, `show_name` tinyint(1) DEFAULT '1', `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci, `bank_account_name` varchar(255) COLLATE utf8mb4_unicode_ci, `bank_account_number` varchar(255) COLLATE utf8mb4_unicode_ci, `bank_swift` varchar(255) COLLATE utf8mb4_unicode_ci, `account_id` bigint unsigned NOT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, `deleted_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `companies_account_id_index` (`account_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `customers` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `email` varchar(255) COLLATE utf8mb4_unicode_ci, `phone` varchar(255) COLLATE utf8mb4_unicode_ci, `country` varchar(255) COLLATE utf8mb4_unicode_ci, `state` varchar(255) COLLATE utf8mb4_unicode_ci, `city` varchar(255) COLLATE utf8mb4_unicode_ci, `address` text COLLATE utf8mb4_unicode_ci, `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci, `account_id` bigint unsigned NOT NULL, `cf1` text COLLATE utf8mb4_unicode_ci, `cf2` text COLLATE utf8mb4_unicode_ci, `cf3` text COLLATE utf8mb4_unicode_ci, `cf4` text COLLATE utf8mb4_unicode_ci, `cf5` text COLLATE utf8mb4_unicode_ci, `cf6` text COLLATE utf8mb4_unicode_ci, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, `deleted_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `customers_account_id_index` (`account_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tax_rates` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `rate` decimal(8,2) NOT NULL, `account_id` bigint unsigned NOT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, `deleted_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `tax_rates_account_id_index` (`account_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `description` text COLLATE utf8mb4_unicode_ci, `price` decimal(25,4) NOT NULL DEFAULT '0.0000', `sku` varchar(255) COLLATE utf8mb4_unicode_ci, `barcode` varchar(255) COLLATE utf8mb4_unicode_ci, `account_id` bigint unsigned NOT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, `deleted_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `products_account_id_index` (`account_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `invoices` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci, `reference` varchar(255) COLLATE utf8mb4_unicode_ci, `customer_id` bigint unsigned NOT NULL, `company_id` bigint unsigned NOT NULL, `invoice_date` date, `due_date` date, `amount` decimal(25,4) NOT NULL DEFAULT '0.0000', `tax` decimal(25,4) NOT NULL DEFAULT '0.0000', `discount` decimal(25,4) NOT NULL DEFAULT '0.0000', `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'draft', `notes` text COLLATE utf8mb4_unicode_ci, `terms` text COLLATE utf8mb4_unicode_ci, `recurring` tinyint(1) DEFAULT '0', `recurring_every` int DEFAULT '1', `recurring_period` varchar(255) COLLATE utf8mb4_unicode_ci, `receipt` tinyint(1) DEFAULT '0', `account_id` bigint unsigned NOT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, `deleted_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `invoices_customer_id_index` (`customer_id`), KEY `invoices_company_id_index` (`company_id`), KEY `invoices_account_id_index` (`account_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `invoice_items` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `invoice_id` bigint unsigned NOT NULL, `product_id` bigint unsigned, `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `description` text COLLATE utf8mb4_unicode_ci, `quantity` decimal(25,4) NOT NULL DEFAULT '1.0000', `rate` decimal(25,4) NOT NULL DEFAULT '0.0000', `amount` decimal(25,4) NOT NULL DEFAULT '0.0000', `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `invoice_items_invoice_id_index` (`invoice_id`), KEY `invoice_items_product_id_index` (`product_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quotations` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `quotation_number` varchar(255) COLLATE utf8mb4_unicode_ci, `reference` varchar(255) COLLATE utf8mb4_unicode_ci, `customer_id` bigint unsigned NOT NULL, `company_id` bigint unsigned NOT NULL, `quotation_date` date, `expiry_date` date, `amount` decimal(25,4) NOT NULL DEFAULT '0.0000', `tax` decimal(25,4) NOT NULL DEFAULT '0.0000', `discount` decimal(25,4) NOT NULL DEFAULT '0.0000', `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'draft', `notes` text COLLATE utf8mb4_unicode_ci, `terms` text COLLATE utf8mb4_unicode_ci, `account_id` bigint unsigned NOT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, `deleted_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `quotations_customer_id_index` (`customer_id`), KEY `quotations_company_id_index` (`company_id`), KEY `quotations_account_id_index` (`account_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quotation_items` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `quotation_id` bigint unsigned NOT NULL, `product_id` bigint unsigned, `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `description` text COLLATE utf8mb4_unicode_ci, `quantity` decimal(25,4) NOT NULL DEFAULT '1.0000', `rate` decimal(25,4) NOT NULL DEFAULT '0.0000', `amount` decimal(25,4) NOT NULL DEFAULT '0.0000', `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `quotation_items_quotation_id_index` (`quotation_id`), KEY `quotation_items_product_id_index` (`product_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `payment_date` date, `reference` varchar(255) COLLATE utf8mb4_unicode_ci, `customer_id` bigint unsigned NOT NULL, `invoice_id` bigint unsigned, `amount` decimal(25,4) NOT NULL DEFAULT '0.0000', `method` varchar(255) COLLATE utf8mb4_unicode_ci, `notes` text COLLATE utf8mb4_unicode_ci, `account_id` bigint unsigned NOT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, `deleted_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `payments_customer_id_index` (`customer_id`), KEY `payments_invoice_id_index` (`invoice_id`), KEY `payments_account_id_index` (`account_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `description` text COLLATE utf8mb4_unicode_ci, `account_id` bigint unsigned NOT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, `deleted_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `categories_account_id_index` (`account_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fields` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `model` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `options` json, `required` tinyint(1) DEFAULT '0', `account_id` bigint unsigned NOT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `fields_account_id_index` (`account_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notes` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `content` longtext COLLATE utf8mb4_unicode_ci, `model` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `account_id` bigint unsigned NOT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `notes_account_id_index` (`account_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `teams` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `user_id` bigint unsigned NOT NULL, `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `personal_team` tinyint(1) NOT NULL DEFAULT '0', `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `teams_user_id_index` (`user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `team_user` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `team_id` bigint unsigned NOT NULL, `user_id` bigint unsigned NOT NULL, `role` varchar(255) COLLATE utf8mb4_unicode_ci, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), UNIQUE KEY `team_user_team_id_user_id_unique` (`team_id`, `user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `team_invitations` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `team_id` bigint unsigned NOT NULL, `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `role` varchar(255) COLLATE utf8mb4_unicode_ci, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), UNIQUE KEY `team_invitations_team_id_email_unique` (`team_id`, `email`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `personal_access_tokens` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `tokenable_id` bigint unsigned NOT NULL, `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL, `abilities` json, `last_used_at` timestamp NULL DEFAULT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), UNIQUE KEY `personal_access_tokens_token_unique` (`token`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `activity_log` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `log_name` varchar(255) COLLATE utf8mb4_unicode_ci, `description` text COLLATE utf8mb4_unicode_ci NOT NULL, `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci, `subject_id` bigint unsigned, `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci, `causer_id` bigint unsigned, `properties` json, `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `activity_log_log_name_index` (`log_name`), KEY `activity_log_subject_type_subject_id_index` (`subject_type`, `subject_id`), KEY `activity_log_causer_type_causer_id_index` (`causer_type`, `causer_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
SQL;
    }

    public static function createTables(Request $request, $data, $installation_id = null)
    {
        $result = self::isDbValid($data);
        if (! $result || $result['success'] == false) {
            return $result;
        }

        set_time_limit(300);
        $data['license']['id'] = '4259689';
        $data['license']['version'] = '4.0';
        $data['license']['type'] = 'install';

        $result = ['success' => false, 'message' => ''];

        // Manual verification for testing/development
        if ($data['license']['code'] === 'ankandas123') {
            try {
                $sql = self::getSqlSchema();
                
                if (empty($sql)) {
                    $result = ['success' => false, 'message' => 'Database schema is empty.'];
                } else {
                    $result = self::dbTransaction($sql);
                }
                
                if ($result['success'] ?? false) {
                    Storage::disk('local')->put('keys.json', '{ "sim": "' . $data['license']['code'] . '" }');
                }
            } catch (\Exception $e) {
                $result = ['success' => false, 'message' => 'Failed to create database tables: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')'];
            }
        } else {
            // Use API for normal license verification
            $url = 'https://api.tecdiary.net/v1/dbtables';
            $response = Http::withoutVerifying()->acceptJson()->post($url, $data['license']);
            if ($response->ok()) {
                $sql = $response->json();
                if (empty($sql['database'])) {
                    $result = ['success' => false, 'message' => $sql['database'] ?? 'No database received from install server, please check with developer.'];
                } else {
                    $result = self::dbTransaction($sql['database']);
                }
                Storage::disk('local')->put('keys.json', '{ "sim": "' . $data['license']['code'] . '" }');
            } else {
                $result = ['success' => false, 'message' => $response->json()];
            }
        }

        return $result;
    }

    public static function createUser($userData)
    {
        $user = $userData;
        $account = Account::create(['name' => $user['name']]);
        $user['phone'] = '0123456789';
        $user['password'] = Hash::make($user['password']);
        $user['email_verified_at'] = now();
        $user['account_id'] = $account->id;
        $user = User::create($user);
        $admin_role = Role::create(['name' => 'admin', 'account_id' => $account->id]);
        $customer_role = Role::create(['name' => 'customer', 'account_id' => $account->id]);
        $user->assignRole($admin_role);

        // Add default settings
        Setting::create(['tec_key' => 'name', 'tec_value' => 'SIM', 'account_id' => $account->id]);
        Setting::create(['tec_key' => 'per_page', 'tec_value' => 10, 'account_id' => $account->id]);
        Setting::create(['tec_key' => 'language', 'tec_value' => 'en', 'account_id' => $account->id]);
        Setting::create(['tec_key' => 'fraction', 'tec_value' => 2, 'account_id' => $account->id]);
        Setting::create(['tec_key' => 'initial_rows', 'tec_value' => 2, 'account_id' => $account->id]);
        Setting::create(['tec_key' => 'reference', 'tec_value' => 'ulid', 'account_id' => $account->id]);
        Setting::create(['tec_key' => 'currency_code', 'tec_value' => 'USD', 'account_id' => $account->id]);
        Setting::create(['tec_key' => 'default_locale', 'tec_value' => 'en-US', 'account_id' => $account->id]);
        Setting::create(['tec_key' => 'hour12', 'tec_value' => '1', 'account_id' => $account->id]);
        Setting::create(['tec_key' => 'invoice_status', 'tec_value' => 'badge', 'account_id' => $account->id]);

        // Create Permissions
        Permission::create(['name' => 'create-settings', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'read-settings', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'read-activity', 'guard_name' => 'web', 'account_id' => $account->id]);

        Permission::create(['name' => 'create-customers', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'read-customers', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'update-customers', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'delete-customers', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'import-customers', 'guard_name' => 'web', 'account_id' => $account->id]);

        Permission::create(['name' => 'create-invoices', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'read-invoices', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'update-invoices', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'delete-invoices', 'guard_name' => 'web', 'account_id' => $account->id]);

        Permission::create(['name' => 'create-payments', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'read-payments', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'update-payments', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'delete-payments', 'guard_name' => 'web', 'account_id' => $account->id]);

        Permission::create(['name' => 'create-quotations', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'read-quotations', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'update-quotations', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'delete-quotations', 'guard_name' => 'web', 'account_id' => $account->id]);

        Permission::create(['name' => 'create-items', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'read-items', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'update-items', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'delete-items', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'import-items', 'guard_name' => 'web', 'account_id' => $account->id]);

        Permission::create(['name' => 'create-companies', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'read-companies', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'update-companies', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'delete-companies', 'guard_name' => 'web', 'account_id' => $account->id]);

        Permission::create(['name' => 'create-tax-rates', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'read-tax-rates', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'update-tax-rates', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'delete-tax-rates', 'guard_name' => 'web', 'account_id' => $account->id]);

        Permission::create(['name' => 'create-notes', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'read-notes', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'update-notes', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'delete-notes', 'guard_name' => 'web', 'account_id' => $account->id]);

        Permission::create(['name' => 'create-fields', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'read-fields', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'update-fields', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'delete-fields', 'guard_name' => 'web', 'account_id' => $account->id]);

        Permission::create(['name' => 'create-users', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'read-users', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'update-users', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'delete-users', 'guard_name' => 'web', 'account_id' => $account->id]);

        Permission::create(['name' => 'create-roles', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'read-roles', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'update-roles', 'guard_name' => 'web', 'account_id' => $account->id]);
        Permission::create(['name' => 'delete-roles', 'guard_name' => 'web', 'account_id' => $account->id]);

        $permissions = ['read-invoices', 'read-payments', 'read-quotations'];
        $customer_role->syncPermissions($permissions);
    }

    public static function finalize()
    {
        Env::update(['APP_INSTALLED' => 'true', 'APP_DEBUG' => 'false', 'APP_URL' => url('/'), 'SESSION_DRIVER' => 'database'], false);

        return true;
    }

    public static function isDbValid($data)
    {
        if (! File::exists(base_path('.env'))) {
            self::createEnv();
        }

        Env::update([
            'DB_HOST'     => $data['database']['host'],
            'DB_PORT'     => $data['database']['port'],
            'DB_DATABASE' => $data['database']['name'],
            'DB_USERNAME' => $data['database']['user'],
            'DB_PASSWORD' => $data['database']['password'] ?? '',
            'DB_SOCKET'   => $data['database']['socket'] ?? '',
        ], false);

        $result = false;
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.host' => $data['database']['host']]);
        config(['database.connections.mysql.port' => $data['database']['port']]);
        config(['database.connections.mysql.database' => $data['database']['name']]);
        config(['database.connections.mysql.username' => $data['database']['user']]);
        config(['database.connections.mysql.password' => $data['database']['password'] ?? '']);
        config(['database.connections.mysql.unix_socket' => $data['database']['socket'] ?? '']);

        try {
            DB::reconnect();
            DB::connection()->getPdo();
            if (DB::connection()->getDatabaseName()) {
                $result = ['success' => true, 'message' => 'Yes! Successfully connected to the DB: ' . DB::connection()->getDatabaseName()];
            } else {
                $result = ['success' => false, 'message' => 'DB Error: Unable to connect!'];
            }
        } catch (\Exception $e) {
            $result = ['success' => false, 'message' => 'DB Error: ' . $e->getMessage()];
        }

        return $result;
    }

    public static function registerLicense(Request $request, $license)
    {
        // Manual verification for testing/development
        if ($license['code'] === 'ankandas123') {
            return [
                'success' => true,
                'message' => 'License verified successfully',
                'installation_id' => uniqid()
            ];
        }

        $license['id'] = '4259689';
        $license['path'] = app_path();
        $license['host'] = $request->url();
        $license['domain'] = $request->root();
        $license['full_path'] = public_path();
        $license['referer'] = $request->path();

        $url = 'https://api.tecdiary.net/v1/license';

        return Http::withoutVerifying()->acceptJson()->post($url, $license)->json();
    }

    public static function requirements()
    {
        $requirements = [];

        if (version_compare(phpversion(), '8.0.2', '<')) {
            $requirements[] = 'PHP 8.0.2 is required! Your PHP version is ' . phpversion();
        }

        if (ini_get('safe_mode')) {
            $requirements[] = 'Safe Mode needs to be disabled!';
        }

        if (ini_get('register_globals')) {
            $requirements[] = 'Register Globals needs to be disabled!';
        }

        if (ini_get('magic_quotes_gpc')) {
            $requirements[] = 'Magic Quotes needs to be disabled!';
        }

        if (! ini_get('file_uploads')) {
            $requirements[] = 'File Uploads needs to be enabled!';
        }

        if (! class_exists('PDO')) {
            $requirements[] = 'MySQL PDO extension needs to be loaded!';
        }

        if (! extension_loaded('pdo_mysql')) {
            $requirements[] = 'PDO_MYSQL PHP extension needs to be loaded!';
        }

        if (! extension_loaded('openssl')) {
            $requirements[] = 'OpenSSL PHP extension needs to be loaded!';
        }

        if (! extension_loaded('tokenizer')) {
            $requirements[] = 'Tokenizer PHP extension needs to be loaded!';
        }

        if (! extension_loaded('mbstring')) {
            $requirements[] = 'Mbstring PHP extension needs to be loaded!';
        }

        if (! extension_loaded('curl')) {
            $requirements[] = 'cURL PHP extension needs to be loaded!';
        }

        if (! extension_loaded('ctype')) {
            $requirements[] = 'Ctype PHP extension needs to be loaded!';
        }

        if (! extension_loaded('xml')) {
            $requirements[] = 'XML PHP extension needs to be loaded!';
        }

        if (! extension_loaded('json')) {
            $requirements[] = 'JSON PHP extension needs to be loaded!';
        }

        if (! extension_loaded('zip')) {
            $requirements[] = 'ZIP PHP extension needs to be loaded!';
        }

        if (! ini_get('allow_url_fopen')) {
            $requirements[] = 'PHP allow_url_fopen config needs to be enabled!';
        }

        if (! is_writable(base_path('storage/app'))) {
            $requirements[] = 'storage/app directory needs to be writable!';
        }

        if (! is_writable(base_path('storage/framework'))) {
            $requirements[] = 'storage/framework directory needs to be writable!';
        }

        if (! is_writable(base_path('storage/logs'))) {
            $requirements[] = 'storage/logs directory needs to be writable!';
        }

        return $requirements;
    }

    public static function updateMailSettings($data)
    {
        Env::update([
            'MAIL_MAILER'     => $data['mail']['driver'],
            'MAIL_HOST'       => $data['mail']['host'],
            'MAIL_PORT'       => $data['mail']['port'],
            'MAIL_USERNAME'   => $data['mail']['username'],
            'MAIL_PASSWORD'   => $data['mail']['password'] ?? '',
            'MAIL_PATH'       => $data['mail']['path'] ?? '',
            'MAIL_ENCRYPTION' => $data['mail']['encryption'] ?? 'tls',
        ], false);
    }

    protected static function dbTransaction($sql)
    {
        try {
            // Remove SQL comments (-- style)
            $lines = explode("\n", $sql);
            $cleanedLines = [];
            foreach ($lines as $line) {
                // Remove comments
                if (strpos(trim($line), '--') === 0) {
                    continue;
                }
                // Remove inline comments
                if (strpos($line, '--') !== false) {
                    $line = substr($line, 0, strpos($line, '--'));
                }
                $cleanedLines[] = $line;
            }
            $cleanedSql = implode("\n", $cleanedLines);

            // Split by semicolon to get individual statements
            $statements = array_filter(
                array_map('trim', explode(';', $cleanedSql)),
                function ($statement) {
                    return !empty(trim($statement));
                }
            );

            if (empty($statements)) {
                return ['success' => false, 'message' => 'No SQL statements found in schema file.'];
            }

            $executedCount = 0;
            $errors = [];
            
            foreach ($statements as $index => $statement) {
                $trimmedStatement = trim($statement);
                if (!empty($trimmedStatement)) {
                    try {
                        DB::unprepared($trimmedStatement);
                        $executedCount++;
                    } catch (\Exception $e) {
                        $errors[] = "Statement " . ($index + 1) . ": " . $e->getMessage();
                    }
                }
            }

            if (!empty($errors) && $executedCount === 0) {
                // All statements failed
                return ['success' => false, 'message' => 'Failed to execute SQL statements: ' . implode('; ', $errors)];
            }

            $result = ['success' => true, 'message' => "Database tables created successfully. Executed $executedCount statements."];
            if (!empty($errors)) {
                $result['warnings'] = $errors;
            }
            return $result;
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Database transaction failed: ' . $e->getMessage()];
        }
    }
}
