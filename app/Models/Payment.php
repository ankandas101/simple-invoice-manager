<?php

namespace App\Models;

use App\Casts\AppDate;
use App\Traits\HidePrivateAttributes;
use App\Traits\HasSchemalessAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;
    use HasSchemalessAttributes;
    use HidePrivateAttributes;

    public $fillable = [
        'date', 'reference', 'hash', 'amount', 'company_id', 'customer_id', 'invoice_id',
        'user_id', 'method', 'note', 'extra_attributes', 'account_id',
        'fees', 'transaction_id', 'status', 'receipt_url', 'number', 'company_number',
    ];

    public static $hasReference = true;

    protected $setHash = true;

    protected $setUser = true;

    protected function casts(): array
    {
        return [
            'extra_attributes' => 'array',
            'date'             => AppDate::class,
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
        log_activity(__('delete_text', ['record' => __('Payment')]), $this, $this, 'Payment');

        return parent::forceDelete();
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class)->withoutGlobalScopes();
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['trashed'] ?? null, fn ($q, $t) => $q->{$t . 'Trashed'}())
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search))
            ->when($filters['start_date'] ?? null, fn ($query, $date) => $query->where('date', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($query, $date) => $query->where('date', '<=', $date))
            ->when($filters['method'] ?? null, fn ($query, $method) => $query->where('method', $method))
            ->when($filters['company'] ?? null, fn ($query, $company) => $query->where('company_id', $company))
            ->when($filters['customer'] ?? null, fn ($query, $customer) => $query->where('customer_id', $customer))
            ->when($filters['user'] ?? null, fn ($query, $user) => $query->where('user_id', $user))
            ->when($filters['invoice'] ?? null, fn ($query, $invoice) => $query->whereRelation('invoice', 'id', $invoice));
    }

    public function scopeSearch($query, $s)
    {
        return $query->where(
            fn ($q) => $q->where('reference', 'like', "%{$s}%")
                ->orWhere('number', 'like', "%{$s}%")
                ->orWhere('company_number', 'like', "%{$s}%")
                ->orWhereRelation('user', 'name', 'like', "%{$s}%")
                ->orWhereRelation('customer', 'name', 'like', "%{$s}%")
                ->orWhere('note', 'like', "%{$s}%")
                ->orWhere('extra_attributes', 'like', "%{$s}%")
                ->orWhereHas('invoice', fn ($q) => $q->search($s))
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

        static::creating(function ($payment) {
            if (! $payment->account_id) {
                $payment->account_id = getAccountId();
            }
            if ($payment->setHash && ! $payment->hash) {
                $payment->hash = uuid4();
            }
            if ($payment->setUser && ! $payment->user_id) {
                $payment->user_id = auth()->user()?->id ?? User::first()->id;
            }
            if ($payment::$hasReference && ! $payment->reference) {
                $payment->reference = get_reference($payment);
            }
            [$payment->number, $payment->company_number] = get_next_number($payment);
        });
    }
}
