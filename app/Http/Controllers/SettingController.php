<?php

namespace App\Http\Controllers;

use DateTimeZone;
use Inertia\Inertia;
use App\Models\Setting;
use App\Rules\LocaleLength;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

        return Inertia::render('Setting/Index', [
            'timezones'       => $timezones,
            'current'         => get_settings(),
            'laravel_version' => app()->version(),
        ]);
    }

    public function store(Request $request)
    {
        $langFiles = collect(json_decode(File::get(lang_path('languages.json')))->available)->pluck('value')->all();
        $settings = $request->validate([
            'name'             => 'required',
            'currency_code'    => 'required|string|size:3',
            'language'         => 'required|string|in:' . implode(',', $langFiles),
            'per_page'         => 'required|in:10,15,25,50,100',
            'default_locale'   => ['required', new LocaleLength()],
            'fraction'         => 'required|numeric|min:0|in:0,1,2,3,4',
            'invoice_status'   => 'required|string|in:badge,text',
            'initial_rows'     => 'required|numeric|min:1|max:20',
            'reference'        => 'required|string|in:ai,ulid,uuid,uniqid',
            'paypal'           => 'nullable|array',
            'stripe'           => 'nullable|array',
            'hour12'           => 'nullable|boolean',
            'timezone'         => 'nullable',
            'paypal.*'         => 'nullable',
            'stripe.*'         => 'nullable',
            'show_tax'         => 'nullable|boolean',
            'product_discount' => 'nullable|boolean',
            'create_user'      => 'nullable|boolean',
            'over_selling'     => 'nullable|boolean',
            'impersonation'    => 'nullable|boolean',
            'login_logo'       => 'nullable|mimes:png,jpg,jpeg,svg',
            'invoice_logo'     => 'nullable|mimes:png,jpg,jpeg,svg',
            'attachment_exts'  => 'nullable',
            'date_format'      => 'required|in:js,php',
            'php_date_format'  => 'nullable',

            'prefer_company_email'         => 'nullable|boolean',
            'mail_from_address'            => 'nullable|email',
            'mail_from_name'               => 'nullable|string',
            'mail_default'                 => 'nullable|in:smtp,mailgun,sparkpost,ses',
            'mail_mailers_smtp_host'       => 'nullable|string',
            'mail_mailers_smtp_port'       => 'nullable|numeric',
            'mail_mailers_smtp_username'   => 'nullable|string',
            'mail_mailers_smtp_password'   => 'nullable|string',
            'mail_mailers_smtp_encryption' => 'nullable|in:tls,ssl',
            'services_mailgun_domain'      => 'nullable|string',
            'services_mailgun_secret'      => 'nullable|string',
            'services_mailgun_endpoint'    => 'nullable|string',
            'services_sparkpost_secret'    => 'nullable|string',
            'services_sparkpost_endpoint'  => 'nullable|string',
            'services_ses_key'             => 'nullable|string',
            'services_ses_secret'          => 'nullable|string',
            'services_ses_region'          => 'nullable|string',
            'reference_prefix'             => 'nullable|string',
            'number_format'                => 'nullable|string',
            'use_company_number'           => 'nullable|boolean',
            'invoice_prefix'               => 'nullable|string',
            'payment_prefix'               => 'nullable|string',
            'quotation_prefix'             => 'nullable|string',
            'show_subtotal'                => 'nullable|boolean',
        ]);

        if (demo()) {
            $settings['name'] = 'SIM';
            $settings['login_logo'] = asset('/storage/logo.svg');
            $settings['invoice_logo'] = asset('/storage/logo.svg');
        } else {
            if ($request->has('login_logo') && $request->login_logo) {
                $settings['login_logo'] = Storage::disk('assets')->url($request->login_logo->store('/images', 'assets'));
            }
            if ($request->has('invoice_logo') && $request->invoice_logo) {
                $settings['invoice_logo'] = Storage::disk('assets')->url($request->invoice_logo->store('/images', 'assets'));
            }
        }

        $json_fields = ['paypal', 'stripe'];
        collect($settings)->each(function ($value, $key) use ($json_fields) {
            Setting::updateOrCreate(['tec_key' => $key, 'account_id' => auth()->user()->account_id], ['tec_value' => in_array($key, $json_fields) ? json_encode($value) : $value]);
        });
        log_activity(__('{record} has been {action}.', ['record' => 'Settings', 'action' => 'saved']), $settings, auth()->user(), 'Setting');

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Settings', 'action' => 'saved']));
    }
}
