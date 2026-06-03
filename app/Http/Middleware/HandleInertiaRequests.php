<?php

namespace App\Http\Middleware;

use App\Models\User;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = null;
        if ($request->session()->has('impersonate') && get_settings('impersonation')) {
            Auth::guard('web')->onceUsingId($request->session()->get('impersonate'));
            $user = User::find($request->session()->get('impersonate'));
        }

        $settings = get_public_settings();
        $langFiles = json_decode(file_get_contents(lang_path('languages.json')));

        return array_merge(parent::share($request), [
            'demo'         => demo(),
            'settings'     => $settings,
            'language'     => app()->getLocale(),
            'languages'    => $langFiles->available,
            'user'         => $user ?? $request->user(),
            'logo'         => $settings && ($settings['login_logo'] ?? null) ? $settings['login_logo'] : asset('storage/logo.svg'),
            'invoice_logo' => $settings && ($settings['invoice_logo'] ?? null) ? $settings['invoice_logo'] : asset('storage/logo.svg'),
            'flash'        => [
                'error'   => session('error'),
                'message' => session('message'),
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy())->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            'can_impersonate'  => $request->user()?->canImpersonate(),
            'is_impersonating' => $request->user()?->isImpersonating(),
        ]);
    }

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }
}
