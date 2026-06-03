<?php

use App\Models\Field;
use App\Models\Company;
use App\Models\Setting;
use App\Models\TaxRate;

// Get Settings
if (! function_exists('get_settings')) {
    function get_settings($keys = null)
    {
        $json = ['paypal', 'stripe'];
        if (! empty($keys)) {
            $single = ! is_array($keys) || 1 == count($keys);

            if ($single) {
                $key = is_array($keys) ? $keys[0] : $keys;
                $value = optional(Setting::where('tec_key', $key)->first())->tec_value;

                return in_array($key, $json) ? json_decode($value) : $value;
            }

            return Setting::whereIn('tec_key', $keys)->get()->mapWithKeys(function ($item) use ($json) {
                return [$item['tec_key'] => in_array($item['tec_key'], $json) ? json_decode($item['tec_value']) : $item['tec_value']];
            });
        }

        return Setting::all()->pluck('tec_value', 'tec_key')
            ->merge(['baseUrl' => url('/')])->transform(function ($value, $key) use ($json) {
                return in_array($key, $json) ? json_decode($value) : $value;
            });
    }
}

if (! function_exists('get_public_settings')) {
    function get_public_settings($keys = null)
    {
        $settings = get_settings($keys);

        foreach ($settings as $key => $value) {
            if (str($key)->contains('mail') || str($key)->contains('services')) {
                unset($settings[$key]);
            }
        }

        if ($settings['stripe'] ?? null && $settings['stripe']->secret_key) {
            unset($settings['stripe']->secret_key);
        }

        if ($settings['paypal'] ?? null && $settings['paypal']->secret) {
            unset($settings['paypal']->secret);
        }

        return $settings;
    }
}

// Log Activity
if (! function_exists('log_activity')) {
    function log_activity($activity, $properties = null, $model = null, $name = null)
    {
        return activity($name)->performedOn($model)->withProperties($properties)->log($activity);
    }
}

// Format Decimal
if (! function_exists('formatDecimal')) {
    function formatDecimal($number, $decimals = 2, $ds = '.', $ts = '')
    {
        return number_format($number, $decimals, $ds, $ts);
    }
}

// Format Number
if (! function_exists('formatNumber')) {
    function formatNumber($number, $decimals = 2, $ds = '.', $ts = ',')
    {
        return number_format($number, $decimals, $ds, $ts);
    }
}

// Is Demo Enabled
if (! function_exists('demo')) {
    function demo()
    {
        return env('DEMO', false);
    }
}

// Json translation with choice replace
if (! function_exists('__choice')) {
    function __choice($key, array $replace = [], $number = null)
    {
        return trans_choice($key, $number, $replace);
    }
}

// Get account id
if (! function_exists('getAccountId')) {
    function getAccountId($account_id = null)
    {
        return session('account_id', $account_id ?? auth()->user()?->account_id ?? 1);
    }
}

// Get UUID v1
if (! function_exists('uuid1')) {
    function uuid1()
    {
        $nodeProvider = new Ramsey\Uuid\Provider\Node\RandomNodeProvider();

        return Ramsey\Uuid\Uuid::uuid1($nodeProvider->getNode());
    }
}

// Get UUID v4
if (! function_exists('uuid4')) {
    function uuid4()
    {
        return Ramsey\Uuid\Uuid::uuid4();
    }
}

// Get ULID
if (! function_exists('ulid')) {
    function ulid()
    {
        return (string) Ulid\Ulid::generate(true);
    }
}

// Get get next id
if (! function_exists('get_next_id')) {
    function get_next_id($model)
    {
        return Illuminate\Support\Facades\DB::table($model->getTable())->max('id') + 1;
        // return collect(Illuminate\Support\Facades\DB::select("show table status like '{$model->getTable()}'"))->first()->Auto_increment;
    }
}

// Get reference
if (! function_exists('get_reference')) {
    function get_reference($model)
    {
        $settings = get_settings(['reference', 'reference_prefix']);

        $reference = match ($settings['reference'] ?? null) {
            'ai'     => get_next_id($model),
            'ulid'   => ulid(),
            'uuid'   => uuid4(),
            'uniqid' => uniqid(),
            default  => ulid(),
        };

        return ($settings['reference_prefix'] ?? null) ? now()->format($settings['reference_prefix']) . $reference : $reference;
    }
}

// Calculate Discount
if (! function_exists('calculateDiscount')) {
    function calculateDiscount($discount = 0, $amount = 0)
    {
        if ($discount && $amount) {
            if (str($discount)->contains('%')) {
                $dv = explode('%', $discount);
                $discount = number_format((($amount * (float) $dv[0]) / 100), 2, '.', '');
            }
        }

        return $discount;
    }
}

// Calculate Taxes
if (! function_exists('calculateTax')) {
    function calculateTax(TaxRate $tax, $amount = null, $tax_method = 'exclusive')
    {
        if ($tax && $amount) {
            if ($tax->fixed) {
                return formatDecimal($tax->rate);
            }
            if ($tax_method == 'inclusive') {
                return formatDecimal(($amount * $tax->rate) / (100 + $tax->rate));
            }

            return formatDecimal($amount * $tax->rate / 100);
        }

        return 0;
    }
}

// Calculate Taxes
if (! function_exists('calculateTaxes')) {
    function calculateTaxes($taxes, $amount, $tax_method = 'exclusive')
    {
        $tax_amount = 0;
        if (! empty($taxes)) {
            foreach ($taxes as $tax) {
                $tax_amount += calculateTax($tax, $amount, $tax_method);
            }
        }

        return $tax_amount;
    }
}

// Order Custom Fields
if (! function_exists('orderCustomFields')) {
    function orderCustomFields($data, $ofModel)
    {
        $extra_attributes = [];
        $fields = Field::where('show', 1)->ofModel($ofModel)->pluck('name');
        if ($fields) {
            foreach ($fields as $field) {
                $extra_attributes[$field] = $data['extra_attributes'][$field] ?? '';
            }
        }

        return $extra_attributes;
    }
}

// Calculate Gateway Fees
if (! function_exists('calculate_gateway_fees')) {
    function calculate_gateway_fees($amount, $data, $same_country)
    {
        $fees = 0;
        if ($data?->fixed ?? null) {
            $fees += $data->fixed;
        }
        if ($same_country && ($data?->same_country ?? null)) {
            $fees += $amount * ($data->same_country / 100);
        }
        if (! $same_country && ($data?->other_countries ?? null)) {
            $fees += $amount * ($data->other_countries / 100);
        }

        return $fees;
    }
}

// Is safe email
if (! function_exists('safe_email')) {
    function safe_email($email = '')
    {
        if (demo()) {
            return true;
        }
        $contains = str($email)->contains('@example.');

        return $email && ! $contains;
    }
}

// Get dot settings array
if (! function_exists('get_dot_keys')) {
    function get_dot_keys($flip = false)
    {
        $keys = [
            'mail_from_address'            => 'mail.from.address',
            'mail_from_name'               => 'mail.from.name',
            'mail_default'                 => 'mail.default',
            'mail_mailers_smtp_host'       => 'mail.mailers.smtp.host',
            'mail_mailers_smtp_port'       => 'mail.mailers.smtp.port',
            'mail_mailers_smtp_username'   => 'mail.mailers.smtp.username',
            'mail_mailers_smtp_password'   => 'mail.mailers.smtp.password',
            'mail_mailers_smtp_encryption' => 'mail.mailers.smtp.encryption',
            'services_mailgun_domain'      => 'services.mailgun.domain',
            'services_mailgun_secret'      => 'services.mailgun.secret',
            'services_mailgun_endpoint'    => 'services.mailgun.endpoint',
            'services_sparkpost_secret'    => 'services.sparkpost.secret',
            'services_sparkpost_endpoint'  => 'services.sparkpost.endpoint',
            'services_ses_key'             => 'services.ses.key',
            'services_ses_secret'          => 'services.ses.secret',
            'services_ses_region'          => 'services.ses.region',
        ];

        if ($flip) {
            return array_flip($keys);
        }

        return $keys;
    }
}

// Get reference
if (! function_exists('get_next_number')) {
    function get_next_number($model)
    {
        $key = get_class($model);
        $key = str($key)->replace('App\\Models\\', '')->snake()->append('_number')->toString();
        $prefix_key = str($key)->replace('_number', '_prefix')->toString();

        if ($model->company_id) {
            $company = Company::find($model->company_id);
            $company_number = (int) ($company->{$key} ?? 0) + 1;
            $company->update([$key => $company_number]);
        }

        $next_number = get_settings($key);
        $number = (int) ($next_number ?? 0) + 1;
        Setting::updateOrCreate(
            ['tec_key' => $key, 'account_id' => getAccountId()], ['tec_value' => $number]
        );

        $prefix = get_settings($prefix_key);
        $number_format = get_settings('number_format');

        return [
            $prefix . (($number_format ? now()->format($number_format) : '') . $number),
            $prefix . (($number_format ? now()->format($number_format) : '') . $company_number),
        ];
    }
}
