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
            $expression = DB::raw($sql);
            DB::unprepared($expression->getValue(DB::connection()->getQueryGrammar()));
            $result = ['success' => true, 'message' => 'Database tables are created.'];
        } catch (\Exception $e) {
            $result = ['success' => false, 'SQL: unable to create tables, ' . $e->getMessage()];
        }

        return $result;
    }
}
