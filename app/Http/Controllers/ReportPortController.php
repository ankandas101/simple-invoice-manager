<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;

class ReportPortController extends Controller
{
    public function invoices(Request $request)
    {
        $filters = $request->all('search', 'recurring', 'start_date', 'end_date', 'status', 'trashed', 'company', 'customer', 'user', 'product', 'fields');

        $invoices = Invoice::with(['company', 'customer', 'items', 'user'])->filter($filters)->orderByDesc('date')->latest();

        function generator($invoices)
        {
            foreach ($invoices->cursor() as $row) {
                yield [
                    'id'             => $row->id,
                    'number'         => $row->number,
                    'company_number' => $row->company_number,
                    'date'           => $row->date,
                    'created_at'     => $row->created_at,
                    'reference'      => $row->reference,
                    'company'        => $row->company->name,
                    'customer'       => $row->customer->name,
                    'created_by'     => $row->user->name,
                    'status'         => $row->status,
                    'due_date'       => $row->due_date,
                    'discount'       => (float) $row->total_discount_amount,
                    'tax'            => (float) $row->total_tax_amount,
                    'shipping'       => (float) $row->shipping,
                    'grand_total'    => (float) $row->grand_total,
                    'paid'           => (float) $row->paid,
                ];
            }
        }

        return (new FastExcel(generator($invoices)))->download('invoices_report.xlsx');
    }

    public function payments(Request $request)
    {
        $filters = $request->all('search', 'start_date', 'end_date', 'method', 'trashed', 'company', 'customer', 'user', 'invoice');

        $payments = Payment::with(['company', 'customer', 'user:id,name,email', 'invoice:id,reference'])->filter($filters)->latest();

        function generator($payments)
        {
            foreach ($payments->cursor() as $row) {
                yield [
                    'id'                     => $row->id,
                    'date'                   => $row->date,
                    'created_at'             => $row->created_at,
                    'reference'              => $row->reference,
                    'invoice_id'             => $row->invoice->id,
                    'invoice_number'         => $row->invoice->number,
                    'invoice_company_number' => $row->invoice->company_number,
                    'company'                => $row->company->name,
                    'customer'               => $row->customer->name,
                    'created_by'             => $row->user->name,
                    'method'                 => $row->method,
                    'amount'                 => (float) $row->amount,
                ];
            }
        }

        return (new FastExcel(generator($payments)))->download('payments_report.xlsx');
    }
}
