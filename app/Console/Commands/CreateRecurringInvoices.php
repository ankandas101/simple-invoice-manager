<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Invoice;
use Illuminate\Console\Command;
use App\Actions\Tec\PrepareOrder;
use Spatie\Activitylog\Contracts\Activity;

class CreateRecurringInvoices extends Command
{
    protected $description = 'Create recurring invoices';

    protected $signature = 'app:generate-invoices';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $recurring_invoices = Invoice::withoutGlobalScopes()
            ->with(['items', 'items.taxes', 'customer:id,name'])->recurring()
            ->where(function ($query) {
                $query->whereNull('next_create_date')->orWhere('next_create_date', '<=', now()->format('Y-m-d'));
            })->get()->filter(function ($recurring_invoice) {
                $date = now()->parse($recurring_invoice->getOriginal('last_created_at') ? $recurring_invoice->getOriginal('last_created_at') : $recurring_invoice->getOriginal('date'));
                if ($recurring_invoice->create_before) {
                    $date = $recurring_invoice->create_before == 1 ? $date->subDay() : $date->subDays($recurring_invoice->create_before);
                }

                if ($recurring_invoice->repeat) {
                    return $this->{$recurring_invoice->repeat}($date);
                }
                logger()->error(__('Invoice :id is not created as it has not set the repeat cycle.', ['id' => $recurring_invoice->id]));
            });

        $recurring_invoices->each(function ($recurring_invoice) {
            $this->create($recurring_invoice);
            $recurring_invoice->disableLogging();
            $recurring_invoice->update([
                'last_created_at'  => date('Y-m-d'),
                'next_create_date' => $this->nextDate($recurring_invoice),
            ]);

            activity()->withProperties($recurring_invoice)
                ->tap(function (Activity $activity) use ($recurring_invoice) {
                    $activity->account_id = $recurring_invoice->account_id;
                })
                ->log(__('New invoice has been created for recurring of invoice :invoice for customer :customer and next invoice will be on :date', [
                    'invoice'  => __('number') . ' ' . $recurring_invoice->id . ' (' . __('Ref') . ': ' . $recurring_invoice->reference . ')',
                    'customer' => $recurring_invoice->customer->name . ' (' . __('Id') . ': ' . $recurring_invoice->customer->id . ')',
                    'date'     => $recurring_invoice->next_create_date,
                ]));
        });

        if ($recurring_invoices->count()) {
            $this->info(sprintf('%d ' . __('invoice', [], $recurring_invoices->count()) . ' have been created.', $recurring_invoices->count()));
        } else {
            $this->info('Nothing to generate!');
        }
    }

    protected function annually($date)
    {
        return $date->startOfDay()->addYear()->lte(Carbon::now()->startOfDay());
    }

    protected function biennially($date)
    {
        return $date->startOfDay()->addYears(2)->lte(Carbon::now()->startOfDay());
    }

    protected function create($recurring_invoice)
    {
        $this->line('Creating invoice for recurring invoice # ' . $recurring_invoice->id . ' with reference of ' . $recurring_invoice->reference);
        $v = $recurring_invoice->toArray();
        // $v = unserialize(serialize($recurring_invoice->toArray()));

        $invoice_items = [];

        foreach ($recurring_invoice->items as $item) {
            $invoice_item = [
                'name'                  => $item->name,
                'price'                 => $item->price,
                'quantity'              => $item->quantity,
                'net_price'             => $item->net_price,
                'unit_price'            => $item->unit_price,
                'account_id'            => $item->account_id,
                'product_id'            => $item->product_id ?? null,
                'details'               => $item->details,
                'discount_amount'       => $item->discount_amount,
                'total_discount_amount' => $item->total_discount_amount,
                'discount'              => $item->discount,
                'tax_method'            => $item->tax_method,
                'tax_amount'            => $item->tax_amount,
                'total_tax_amount'      => $item->total_tax_amount,
                'extra_attributes'      => $item->extra_attributes->toArray(),
                'total'                 => $item->total,
            ];

            $invoice_item['taxes'] = [];
            if (! empty($item->taxes)) {
                $invoice_item['taxes'] = $item->taxes->pluck('id')->all();
            }

            $invoice_items[] = $invoice_item;
        }

        $v['items'] = $invoice_items;
        $v['date'] = Carbon::now()->format('Y-m-d');
        $v['invoice_id'] = $recurring_invoice->id;
        $v['account_id'] = $recurring_invoice->account_id;
        unset(
            $v['id'],
            $v['repeat'],
            $v['recurring'],
            $v['create_before'],
            $v['last_created_at'],
            $v['hash'],
            $v['reference'],
            $v['created_at'],
            $v['updated_at'],
            $v['attachment'],
            $v['number'],
            $v['company_number']
        );

        $v['paid'] = 0;
        $v['due_date'] = null;
        $v['status'] = 'pending';
        if ($recurring_invoice->due_date) {
            $days = now()->parse($recurring_invoice->due_date)->diffInDays($recurring_invoice->date);
            $v['due_date'] = now()->addDays($days)->format('Y-m-d');
        }
        $invoice = (new PrepareOrder($v, new Invoice))->save();

        $this->line('Created invoice # ' . $invoice->id . ' with reference of ' . $invoice->reference);
        if (safe_email($invoice->customer->email)) {
            try {
                $invoice->customer->notify(new \App\Notifications\InvoiceCreated($invoice));
                $this->line('Emailed the invoice # ' . $invoice->id . ' to customer.');
            } catch (\Exception $e) {
                logger()->error($e->getMessage());
                $this->error('Failed to emailed the invoice # ' . $invoice->id . ' with error: ' . $e->getMessage());
            }
        }
    }

    protected function daily($date)
    {
        return $date->startOfDay()->addDay()->lte(Carbon::now()->startOfDay());
    }

    protected function monthly($date)
    {
        return $date->startOfDay()->addMonth()->lte(Carbon::now()->startOfDay());
    }

    protected function nextDate($invoice)
    {
        return match ($invoice->repeat) {
            'daily'      => now()->parse($invoice->getOriginal('last_created_at'))->addDay()->format('Y-m-d'),
            'weekly'     => now()->parse($invoice->getOriginal('last_created_at'))->addWeek()->format('Y-m-d'),
            'monthly'    => now()->parse($invoice->getOriginal('last_created_at'))->addMonth()->format('Y-m-d'),
            'quarterly'  => now()->parse($invoice->getOriginal('last_created_at'))->addQuarter()->format('Y-m-d'),
            'semiannual' => now()->parse($invoice->getOriginal('last_created_at'))->addMonths(6)->format('Y-m-d'),
            'annually'   => now()->parse($invoice->getOriginal('last_created_at'))->addYear()->format('Y-m-d'),
            'biennially' => now()->parse($invoice->getOriginal('last_created_at'))->addYears(2)->format('Y-m-d'),
        };
    }

    protected function quarterly($date)
    {
        return $date->startOfDay()->addQuarter()->lte(Carbon::now()->startOfDay());
    }

    protected function semiannual($date)
    {
        return $date->startOfDay()->addMonths(6)->lte(Carbon::now()->startOfDay());
    }

    protected function weekly($date)
    {
        return $date->startOfDay()->addWeek()->lte(Carbon::now()->startOfDay());
    }
}
