<?php

namespace App\Models;

use App\Helpers\Notifiable;
use App\Traits\HasSchemalessAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;
    use HasSchemalessAttributes;
    use Notifiable;

    public $fillable = [
        'name', 'contact_person', 'email', 'phone', 'address', 'city', 'postal_code', 'state',
        'country', 'active', 'ss_image', 'logo',  'account_id', 'extra_attributes', 'show_name', 'logo_dark',
        'show_address', 'bank_account_details', 'allow_transfer', 'qrcode',
        'invoice_number', 'payment_number', 'quotation_number',
    ];

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

        log_activity(__('delete_text', ['record' => __('Company')]), $this, $this, 'Company');

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
        $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"))->orWhere('extra_attributes', 'like', "%{$s}%");
    }
}
