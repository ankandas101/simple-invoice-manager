<?php

namespace App\Casts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class AppDate implements CastsAttributes
{
    /**
     * Create a new cast class instance.
     */
    public function __construct(
        protected ?string $time = null,
    ) {}

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $settings = get_settings(['date_format', 'php_date_format']);
        if (($settings['date_format'] ?? 'js') == 'php') {
            return now()->parse($value)->isoFormat($this->time ? 'lll' : 'll');
            // return now()->parse($value)->format(($settings['php_date_format'] ?? 'Y-m-d') . ($this->time ? ' h:kk A' : ''));
        }

        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }
}
