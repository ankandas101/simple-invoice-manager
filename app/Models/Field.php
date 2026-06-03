<?php

namespace App\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Field extends Model
{
    use HasFactory;
    use HasSlug;

    public $casts = ['models' => 'array'];

    public $fillable = [
        'name', 'type', 'models', 'slug', 'order', 'options', 'required', 'show', 'description',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['trashed'] ?? null, fn ($q, $t) => $q->{$t . 'Trashed'}())
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search));
    }

    public function scopeOfModel($query, $model)
    {
        $query->where('models', 'like', "%{$model}%")->orderBy('order', 'asc');
    }

    public function scopeOfModels($query, $models)
    {
        if (! empty($models)) {
            $r = 0;
            foreach ($models as $model) {
                $query->{$r ? 'orWhere' : 'where'}('models', 'like', "%{$model}%")->orderBy('order', 'asc');
            }
            $query->orderBy('order', 'asc');
        }
    }

    public function scopeSearch($query, $s)
    {
        $query->where(
            fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('type', 'like', "%{$s}%")
                ->orWhere('models', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%")
        );
    }
}
