<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class SendOverdueInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-overdue-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email overdue invoices to the customers.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $invoices = Invoice::withoutGlobalScopes()->isOverdue()->isNotPaid()->isNotCanceled()->get();
        foreach ($invoices as $invoice) {
            $invoice->updateQuietly(['status' => 'overdue']);
            $this->line('Sending overdue notification for invoice # ' . $invoice->id);
            $invoice->customer->notify(new \App\Notifications\InvoiceDue($invoice));
        }

        if ($invoices->count()) {
            $this->info(sprintf('%d ' . __('invoice', [], $invoices->count()) . ' have been emailed.', $invoices->count()));
        } else {
            $this->info('No overdue invoice!');
        }
    }
}
