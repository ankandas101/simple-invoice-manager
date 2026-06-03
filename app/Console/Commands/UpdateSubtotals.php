<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Quotation;
use Illuminate\Console\Command;

class UpdateSubtotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-subtotals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update subtotals for all orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (Invoice::withoutGlobalScopes()->with('items')->cursor() as $invoice) {
            $invoice->items->each(function ($item) {
                $item->update([
                    'subtotal' => formatDecimal($item->quantity * $item->net_price),
                ]);
            });
            $invoice->update([
                'subtotal' => $invoice->items->sum('subtotal'),
            ]);
        }
        foreach (Quotation::withoutGlobalScopes()->with('items')->cursor() as $quote) {
            $quote->items->each(function ($item) {
                $item->update([
                    'subtotal' => formatDecimal($item->quantity * $item->net_price),
                ]);
            });
            $quote->update([
                'subtotal' => $quote->items->sum('subtotal'),
            ]);
        }
    }
}
