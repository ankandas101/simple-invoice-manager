<?php

namespace App\Models;

use App\Traits\HasSchemalessAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceItem extends Model
{
    use HasFactory;
    use HasSchemalessAttributes;

    public $fillable = [
        'invoice_id', 'product_id', 'account_id', 'name', 'details', 'quantity', 'price', 'net_price',
        'unit_price', 'tax_method', 'discount', 'discount_amount', 'extra_attributes', 'total', 'tax_amount', 'total_discount_amount', 'total_tax_amount', 'subtotal',
    ];

    public $timestamps = false;

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function taxes()
    {
        return $this->belongsToMany(TaxRate::class);
        // return $this->belongsToMany(TaxRate::class, null, 'tax_rate_id');
    }
}
