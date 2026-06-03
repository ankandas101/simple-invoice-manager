<?php

namespace App\Http\Middleware;

use Closure;
use Exception;

class Language
{
    public function handle($request, Closure $next)
    {
        try {
            $locale = 'en';
            if (optional(auth()->user())->language) {
                $locale = auth()->user()->language;
            } elseif (env('APP_INSTALLED') && function_exists('get_settings')) {
                $locale = get_settings('language', true);
            }

            app()->setlocale(session('language', ($locale ? $locale : 'en')));

            return $next($request);
        } catch (Exception $e) {
            return $next($request);
        }
    }
}
