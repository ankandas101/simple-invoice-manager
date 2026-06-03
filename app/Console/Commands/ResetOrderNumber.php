<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Quotation;
use Illuminate\Console\Command;

class ResetOrderNumber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-order-number';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset order number to 0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $number_format = get_settings('number_format');

        $this->line('Resetting order number for ' . now()->format($number_format));
        if (in_array($number_format, ['Y', 'Y/', 'Y-']) && now()->startOfYear()->isToday()) {
            $this->line('Resetting order number to 0 for ' . now()->year);
            Setting::updateOrCreate(['tec_key' => get_class(new Invoice)], ['tec_value' => 0]);
            Setting::updateOrCreate(['tec_key' => get_class(new Payment)], ['tec_value' => 0]);
            Setting::updateOrCreate(['tec_key' => get_class(new Quotation())], ['tec_value' => 0]);
            Company::query()->update(['invoice_number' => 0, 'payment_number' => 0, 'quotation_number' => 0]);
            $this->info('Order number has been reset.');
        } elseif (in_array($number_format, ['Ym', 'Y/m/', 'Y-m-']) && now()->startOfMonth()->isToday()) {
            $this->line('Resetting order number to 0 for ' . now()->format($number_format));
            Setting::updateOrCreate(['tec_key' => get_class(new Invoice)], ['tec_value' => 0]);
            Setting::updateOrCreate(['tec_key' => get_class(new Payment)], ['tec_value' => 0]);
            Setting::updateOrCreate(['tec_key' => get_class(new Quotation)], ['tec_value' => 0]);
            Company::query()->update(['invoice_number' => 0, 'payment_number' => 0, 'quotation_number' => 0]);
            $this->info('Order number has been reset.');
        } else {
            $this->line('No need to reset order number for ' . now()->format($number_format));
        }
    }
}
