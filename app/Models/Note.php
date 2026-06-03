<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Note extends Model
{
    use HasFactory;

    public $fillable = ['name', 'details', 'default_sale', 'default_quote', 'account_id'];

    public function forceDelete()
    {
        log_activity(__('delete_text', ['record' => __('Note')]), $this, $this, 'Note');

        return parent::forceDelete();
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['trashed'] ?? null, fn ($q, $t) => $q->{$t . 'Trashed'}())
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search));
    }

    public function scopeSearch($query, $s)
    {
        $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('details', 'like', "%{$s}%"));
    }
}
