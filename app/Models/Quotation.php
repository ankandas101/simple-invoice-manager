<?php

namespace App\Models;

use App\Casts\AppDate;
use App\Traits\HasAttachments;
use App\Traits\HidePrivateAttributes;
use App\Traits\HasSchemalessAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quotation extends Model
{
    use HasAttachments;
    use HasFactory;
    use HasSchemalessAttributes;
    use HidePrivateAttributes;

    public $fillable = [
        'date', 'reference', 'hash', 'company_id', 'customer_id', 'user_id', 'note', 'expiry_date',
        'subtotal', 'total', 'total_tax', 'tax_method', 'grand_total', 'shipping', 'order_tax_amount',
        'product_tax_amount', 'total_tax_amount', 'order_discount', 'order_discount_amount',
        'product_discount_amount', 'total_discount_amount', 'account_id', 'status', 'extra_attributes',
        'number', 'company_number',
    ];

    public static $hasReference = true;

    protected $setHash = true;

    protected $setUser = true;

    protected $with = ['attachments', 'customer', 'user:id,name,email'];

    protected function casts(): array
    {
        return [
            'extra_attributes' => 'array',
            'date'             => AppDate::class,
            'expiry_date'      => AppDate::class,
            'created_at'       => AppDate::class . ':time',
            'updated_at'       => AppDate::class . ':time',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function forceDelete()
    {
        log_activity(__('delete_text', ['record' => __('Quotation')]), $this, $this, 'Quotation');
        $this->items->each->forceDelete();

        return parent::forceDelete();
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class)->withTrashed();
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['trashed'] ?? null, fn ($q, $t) => $q->{$t . 'Trashed'}())
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['start_date'] ?? null, fn ($query, $date) => $query->where('date', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($query, $date) => $query->where('date', '<=', $date))
            ->when($filters['company'] ?? null, fn ($query, $company) => $query->where('company_id', $company));
    }

    public function scopeSearch($query, $s)
    {
        return $query->where(
            fn ($q) => $q->where('id', 'like', "%{$s}%")
                ->orWhere('number', 'like', "%{$s}%")
                ->orWhere('company_number', 'like', "%{$s}%")
                ->orWhere('reference', 'like', "%{$s}%")
                ->orWhere('status', 'like', "%{$s}%")
                ->orWhereRelation('user', 'name', 'like', "%{$s}%")
                ->orWhereRelation('customer', 'name', 'like', "%{$s}%")
                ->orWhere('note', 'like', "%{$s}%")
                ->orWhere('extra_attributes', 'like', "%{$s}%")
        );
    }

    public function taxes()
    {
        return $this->belongsToMany(TaxRate::class);
        // return $this->belongsToMany(TaxRate::class, null, 'tax_rate_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::addGlobalScope('mine', function (Builder $builder) {
            $asUser = null;
            $user = auth()->user();
            if (session()->get('impersonate')) {
                $asUser = User::find(session()->get('impersonate'));
            }
            if ($asUser && $asUser->hasRole('customer')) {
                $builder->where('customer_id', $asUser->customer_id);
            } elseif ($user && $user->hasRole('customer')) {
                $builder->where('customer_id', $user->customer_id);
            } elseif (! $user->hasRole('admin') && ! $user->view_all) {
                $builder->where('user_id', $user->id);
            }
        });

        static::creating(function ($model) {
            if (! $model->account_id) {
                $model->account_id = getAccountId();
            }
            if ($model->setHash && ! $model->hash) {
                $model->hash = uuid4();
            }
            if ($model->setUser && ! $model->user_id) {
                $model->user_id = auth()->user()->id;
            }
            if ($model::$hasReference && ! $model->reference) {
                $model->reference = get_reference($model);
            }
            [$model->number, $model->company_number] = get_next_number($model);
        });

        static::deleting(function ($model) {
            $model->items()->delete();
        });
    }
}
