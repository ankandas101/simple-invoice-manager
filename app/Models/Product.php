<?php

namespace App\Models;

use App\Traits\HasSchemalessAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    use HasSchemalessAttributes;

    public $fillable = ['name', 'price', 'details', 'tax_method', 'extra_attributes'];

    public function delete()
    {
        if ($this->invoiceItems()->exists() || $this->quotationItems()->exists()) {
            return false;
        }

        return parent::delete();
    }

    public function forceDelete()
    {
        if ($this->invoiceItems()->exists() || $this->quotationItems()->exists()) {
            return false;
        }

        log_activity(__('delete_text', ['record' => __('Product')]), $this, $this, 'Product');

        return parent::forceDelete();
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function quotationItems()
    {
        return $this->hasMany(QuotationItem::class);
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

    public function taxes()
    {
        return $this->belongsToMany(TaxRate::class);
    }
}
