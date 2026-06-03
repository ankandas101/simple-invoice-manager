<?php

namespace App\Models;

use App\Casts\AppDate;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    public $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'date'       => AppDate::class,
            'created_at' => AppDate::class . ':time',
            'updated_at' => AppDate::class . ':time',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, fn ($query, $search) => $query->where('data', 'like', "%{$search}%"));
    }

    protected static function booted()
    {
        static::creating(function ($transaction) {
            if (! $transaction->account_id) {
                $transaction->account_id = getAccountId();
            }
        });
        static::created(function ($transaction) {
            Model::withoutEvents(fn () => $transaction->customer->increment('balance', $transaction->amount));
        });
    }
}
