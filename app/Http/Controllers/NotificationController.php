<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Field;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quotation;
use Illuminate\Http\Request;
use App\Notifications\InvoicePaid;
use App\Notifications\InvoiceCanceled;

class NotificationController extends Controller
{
    public function invoice(Request $request, $id, $hash)
    {
        abort_unless($request->hasValidSignature(), 401);
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($id);
        abort_if($invoice->hash != $hash || $invoice->deleted_at, 404);

        $invoice->loadMissing(['company', 'customer', 'items', 'user', 'taxes', 'items.taxes']);

        $fields = [
            'company'  => Field::ofModel('company')->get(),
            'customer' => Field::ofModel('customer')->get(),
            'items'    => Field::ofModel('invoiceitem')->get(),
            'fields'   => Field::ofModel('maininvoice')->get(),
        ];
        $invoice->hideExtraAttributes($fields);

        return Inertia::render('Invoice/Details', [
            'accept_payment' => true,
            'invoice'        => $invoice,
            'settings'       => get_settings(),
        ]);
    }

    public function invoiceNotification(Invoice $invoice)
    {
        if (safe_email($invoice->customer->email)) {
            if ($invoice->status == 'pending') {
                if ($invoice->due_date && Carbon::parse($invoice->due_date)->lt(now())) {
                    $invoice->customer->notify(new \App\Notifications\InvoiceDue($invoice));
                } else {
                    $invoice->customer->notify(new \App\Notifications\InvoiceCreated($invoice));
                }
            } elseif ($invoice->status == 'overdue') {
                $invoice->customer->notify(new \App\Notifications\InvoiceOverdue($invoice));
            } elseif ($invoice->status == 'paid') {
                $invoice->customer->notify(new InvoicePaid($invoice));
            } elseif ($invoice->status == 'canceled') {
                $invoice->customer->notify(new InvoiceCanceled($invoice));
            }

            return back()->with('message', __('{record} has been {action}.', ['record' => __('Email'), 'action' => __('sent')]));
        }

        return back()->with('error', __('{record} has been {action}.', ['record' => __('Email'), 'action' => __('failed')]));
    }

    public function payment(Request $request, $id, $hash)
    {
        abort_unless($request->hasValidSignature(), 401);
        $payment = Payment::withoutGlobalScopes()->findOrFail($id);
        abort_if($payment->hash != $hash || $payment->deleted_at, 404);

        $payment->loadMissing(['company', 'customer', 'user']);

        $fields = [
            'fields'   => Field::ofModel('payment')->get(),
            'company'  => Field::ofModel('company')->get(),
            'customer' => Field::ofModel('customer')->get(),
        ];
        $payment->hideExtraAttributes($fields);

        return Inertia::render('Payment/Details', [
            'payment'  => $payment,
            'settings' => get_settings(),
        ]);
    }

    public function paymentNotification(Payment $payment)
    {
        if (safe_email($payment->customer->email)) {
            $payment->customer->notify(new \App\Notifications\PaymentCreated($payment));

            return back()->with('message', __('{record} has been {action}.', ['record' => __('Email'), 'action' => __('sent')]));
        }

        return back()->with('error', __('{record} has been {action}.', ['record' => __('Email'), 'action' => __('failed')]));
    }

    public function quotation(Request $request, $id, $hash)
    {
        abort_unless($request->hasValidSignature(), 401);
        $quotation = Quotation::withoutGlobalScopes()->findOrFail($id);
        abort_if($quotation->hash != $hash || $quotation->deleted_at, 404);

        $quotation->loadMissing(['company', 'customer', 'items', 'user', 'taxes', 'items.taxes']);

        $fields = [
            'company'  => Field::ofModel('company')->get(),
            'customer' => Field::ofModel('customer')->get(),
            'items'    => Field::ofModel('quotationitem')->get(),
            'fields'   => Field::ofModel('mainquotation')->get(),
        ];
        $quotation->hideExtraAttributes($fields);

        return Inertia::render('Quotation/Details', [
            'quotation' => $quotation,
            'settings'  => get_settings(),
        ]);
    }

    public function quotationNotification(Quotation $quotation)
    {
        if (safe_email($quotation->customer->email)) {
            $quotation->customer->notify(new \App\Notifications\QuotationCreated($quotation));

            return back()->with('message', __('{record} has been {action}.', ['record' => __('Email'), 'action' => __('sent')]));
        }

        return back()->with('error', __('{record} has been {action}.', ['record' => __('Email'), 'action' => __('failed')]));
    }
}
