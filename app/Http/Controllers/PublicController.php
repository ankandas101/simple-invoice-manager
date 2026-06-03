<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Field;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Notifications\TransferReceiptUploaded;

class PublicController extends Controller
{
    public function invoice($id, $hash)
    {
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

        return Inertia::render('Invoice/Details', ['invoice' => $invoice, 'hash' => $hash]);
        // return Inertia::render('Invoice/Details', [
        //     'invoice' => $invoice->load([
        //         'company'     => fn ($q) => $q->withoutGlobalScopes(),
        //         'customer'    => fn ($q) => $q->withoutGlobalScopes(),
        //         'items'       => fn ($q) => $q->withoutGlobalScopes(),
        //         'user'        => fn ($q) => $q->withoutGlobalScopes(),
        //         'taxes'       => fn ($q) => $q->withoutGlobalScopes(),
        //         'items.taxes' => fn ($q) => $q->withoutGlobalScopes(),
        //     ]),
        // ]);
    }

    public function payment($id, $hash)
    {
        $payment = Payment::withoutGlobalScopes()->findOrFail($id);
        abort_if($payment->hash != $hash || $payment->deleted_at, 404);

        $payment->loadMissing(['company', 'customer', 'user', 'invoice:id,number,company_number']);

        $fields = [
            'fields'   => Field::ofModel('payment')->get(),
            'company'  => Field::ofModel('company')->get(),
            'customer' => Field::ofModel('customer')->get(),
        ];
        $payment->hideExtraAttributes($fields);

        return Inertia::render('Payment/Details', ['payment' => $payment]);
    }

    public function quotation($id, $hash)
    {
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

        return Inertia::render('Quotation/Details', ['quotation' => $quotation]);
    }

    public function uploadReceipt(Request $request, $id, $hash)
    {
        $request->validate(['receipt' => 'required|mimes:png,jpg,jpeg,svg,pdf']);

        $invoice = Invoice::withoutGlobalScopes()->findOrFail($id);
        abort_if($invoice->hash != $hash || $invoice->deleted_at, 404);

        $invoice->saveAttachments([$request->receipt]);
        $invoice->update(['receipt' => $request->receipt->getClientOriginalName()]);

        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new TransferReceiptUploaded($invoice));
        }

        return back()->with('message', __('Receipt has been attached to the invoice.'));
    }
}
