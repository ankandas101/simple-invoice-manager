<?php

namespace App\Models;

use App\Helpers\Notifiable;
use App\Traits\HasSchemalessAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;
    use HasSchemalessAttributes;
    use Notifiable;

    public $fillable = [
        'name', 'company', 'email', 'phone', 'address', 'city', 'postal_code', 'state', 'country',
        'active', 'cf1', 'cf2',  'cf3', 'cf4',  'cf5', 'cf6', 'user_id', 'account_id', 'extra_attributes',
    ];

    protected $setUser = true;

    public function delete()
    {
        if ($this->invoices()->exists() || $this->quotations()->exists()) {
            return false;
        }

        return parent::delete();
    }

    public function forceDelete()
    {
        if ($this->invoices()->exists() || $this->quotations()->exists()) {
            return false;
        }

        log_activity(__('delete_text', ['record' => __('Customer')]), $this, $this, 'Customer');

        return parent::forceDelete();
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['trashed'] ?? null, fn ($q, $t) => $q->{$t . 'Trashed'}())
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search));
    }

    public function scopeSearch($query, $s)
    {
        $query->where(function ($q) use ($s) {
            $q->where('name', 'like', "%{$s}%")
                ->orWhere('company', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")
                ->orWhere('extra_attributes', 'like', "%{$s}%");
        });
    }

    public function transactions()
    {
        return $this->hasMany(Transactions::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
