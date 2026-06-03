<?php

namespace App\Models;

use App\Casts\AppDate;
use App\Traits\HasAttachments;
use App\Traits\HidePrivateAttributes;
use App\Traits\HasSchemalessAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasAttachments;
    use HasFactory;
    use HasSchemalessAttributes;
    use HidePrivateAttributes;

    public $fillable = [
        'date', 'reference', 'hash', 'company_id', 'customer_id', 'user_id', 'note', 'total',
        'total_tax', 'tax_method', 'grand_total', 'shipping', 'order_tax_amount', 'product_tax_amount',
        'total_tax_amount', 'order_discount', 'order_discount_amount', 'product_discount_amount',
        'total_discount_amount', 'paid', 'account_id', 'status', 'extra_attributes', 'due_date',
        'recurring', 'repeat', 'create_before', 'last_created_at', 'invoice_id', 'next_create_date', 'receipt', 'subtotal', 'number', 'company_number',
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
            'due_date'         => AppDate::class,
            'last_created_at'  => AppDate::class,
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

    public function delete()
    {
        $this->items->each->delete();
        $this->payments->each->delete();

        return parent::delete();
    }

    public function forceDelete()
    {
        log_activity(__('delete_text', ['record' => __('Invoice')]), $this, $this, 'Invoice');
        $this->items->each->forceDelete();
        $this->payments->each->forceDelete();

        return parent::forceDelete();
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->withTrashed();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class)->withoutGlobalScopes()->withTrashed();
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['trashed'] ?? null, fn ($q, $t) => $q->{$t . 'Trashed'}())
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search))
            ->when($filters['recurring'] ?? null, fn ($query) => $query->recurring())
            ->when($filters['start_date'] ?? null, fn ($query, $date) => $query->where('date', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($query, $date) => $query->where('date', '<=', $date))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['company'] ?? null, fn ($query, $company) => $query->where('company_id', $company))
            ->when($filters['customer'] ?? null, fn ($query, $customer) => $query->where('customer_id', $customer))
            ->when($filters['user'] ?? null, fn ($query, $user) => $query->where('user_id', $user))
            ->when($filters['product'] ?? null, fn ($query, $product) => $query->whereRelation('items', 'name', 'like', "%{$product}%"))
            ->when($filters['fields'] ?? null, fn ($query, $search) => $query->where('extra_attributes', 'like', "%{$search}%")->orWhereHas('items', fn ($q) => $q->where('extra_attributes', 'like', "%{$search}%")))
            ->when($filters['transfer'] ?? null, fn ($query) => $query->whereNotNull('receipt')->where('grand_total', '>', 'paid'));
    }

    public function scopeRecurring($query)
    {
        return $query->where('recurring', 1);
    }

    public function scopeIsNotCanceled($query)
    {
        return $query->where('status', '!=', 'canceled');
    }

    public function scopeIsOverdue($query)
    {
        return $query->whereNotNull('due_date')->where('due_date', '<=', now()->toDateString());
    }

    public function scopeIsNotPaid($query)
    {
        return $query->whereColumn('paid', '<', 'grand_total');
    }

    public function scopeIsPaid($query)
    {
        return $query->whereColumn('paid', '>=', 'grand_total');
    }

    public function scopeSearch($query, $s)
    {
        return $query->where(
            fn ($q) => $q->where('id', 'like', "%{$s}%")
                ->orWhere('number', 'like', "%{$s}%")
                ->orWhere('company_number', 'like', "%{$s}%")
                ->orWhere('reference', 'like', "%{$s}%")
                ->orWhere('status', 'like', "%{$s}%")
                ->orWhereRelation('company', 'name', 'like', "%{$s}%")
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
            } elseif ($user && ! $user->hasRole('admin') && ! $user->view_all) {
                $builder->where('user_id', $user->id);
            } elseif (! $user || ! $user->hasRole('admin')) {
                abort(403, __('Access denied!'));
            }
        });

        static::creating(function ($invoice) {
            if (! $invoice->account_id) {
                $invoice->account_id = getAccountId();
            }
            if ($invoice->setHash && ! $invoice->hash) {
                $invoice->hash = uuid4();
            }
            if ($invoice->setUser && ! $invoice->user_id) {
                $invoice->user_id = auth()->user()->id;
            }
            if ($invoice::$hasReference && ! $invoice->reference) {
                $invoice->reference = get_reference($invoice);
            }
            [$invoice->number, $invoice->company_number] = get_next_number($invoice);
        });

        static::updating(function ($invoice) {
            if ($invoice->grand_total != $invoice->getOriginal('grand_total')) {
                if ($invoice->grand_total <= $invoice->paid) {
                    $invoice->status = 'paid';
                } elseif ($invoice->due_date && now()->gt($invoice->due_date)) {
                    $invoice->status = 'overdue';
                } else {
                    $invoice->status = 'pending';
                }
            }
        });

        static::deleting(function ($invoice) {
            if (! $invoice->deleted_at && $invoice->status != 'canceled') {
                $invoice->customer->transactions()->create([
                    'amount'     => 0 - $invoice->grand_total,
                    'type'       => 'invoice-deleting',
                    'invoice_id' => $invoice->id,
                    'account_id' => $invoice->account_id,
                ]);
            }
        });

        static::restored(function ($invoice) {
            if ($invoice->status != 'canceled') {
                $invoice->customer->transactions()->create([
                    'amount'     => $invoice->grand_total,
                    'type'       => 'invoice-restored',
                    'invoice_id' => $invoice->id,
                    'account_id' => $invoice->account_id,
                ]);
            }
        });
    }

    protected function paid(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? 0,
        )->shouldCache();
    }
}
