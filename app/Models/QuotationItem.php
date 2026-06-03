<?php

namespace App\Models;

use App\Traits\HasSchemalessAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuotationItem extends Model
{
    use HasFactory;
    use HasSchemalessAttributes;

    public $fillable = [
        'quotation_id', 'product_id', 'account_id', 'name', 'details', 'quantity', 'price', 'net_price',
        'unit_price', 'tax_method', 'discount', 'discount_amount', 'extra_attributes', 'total', 'tax_amount', 'subtotal',
    ];

    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function taxes()
    {
        return $this->belongsToMany(TaxRate::class);
        // return $this->belongsToMany(TaxRate::class, null, 'tax_rate_id');
    }
}
