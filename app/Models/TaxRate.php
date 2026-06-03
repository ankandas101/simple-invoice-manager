<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxRate extends Model
{
    use HasFactory;

    public $fillable = ['name', 'rate', 'fixed'];

    public function delete()
    {
        if ($this->products()->exists()) {
            return false;
        }

        return parent::delete();
    }

    public function forceDelete()
    {
        if ($this->products()->exists()) {
            return false;
        }

        log_activity(__('delete_text', ['record' => __('Tax rate')]), $this, $this, 'Tax rate');

        return parent::forceDelete();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['trashed'] ?? null, fn ($q, $t) => $q->{$t . 'Trashed'}())
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search));
    }

    public function scopeSearch($query, $s)
    {
        $query->where(fn ($q) => $q->where('code', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%"));
    }
}
