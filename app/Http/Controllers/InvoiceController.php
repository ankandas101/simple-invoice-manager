<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\Note;
use Inertia\Inertia;
use App\Models\Field;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\TaxRate;
use App\Models\Quotation;
use Illuminate\Http\Request;
use App\Actions\Tec\PrepareOrder;
use App\Http\Requests\InvoiceRequest;
use App\Http\Resources\InvoiceCollection;
use App\Notifications\TransferReceiptCanceled;

class InvoiceController extends Controller
{
    public function create(Request $request)
    {
        $quote = null;
        if ($request->from_quote) {
            $quote = Quotation::with(['company:id,name', 'customer:id,name', 'items', 'taxes:id,name', 'items.taxes:id,name'])->findOrFail($request->get('from_quote'));
        }

        return Inertia::render('Invoice/Form', [
            'quote'           => $quote,
            'customer_fields' => Field::ofModel('customer')->get(),
            'item_fields'     => Field::ofModel('invoiceitem')->get(),
            'fields'          => Field::ofModel('maininvoice')->get(),
            'note'            => Note::where('default_sale', 1)->first(),
            'companies'       => Company::get(['id as value', 'name as label']),
            'tax_rates'       => TaxRate::all(['id as value', 'name as label', 'id', 'rate', 'fixed']),
        ]);
    }

    public function destroy(Request $request, Invoice $invoice)
    {
        if ($invoice->{$request->force ? 'forceDelete' : 'delete'}()) {
            return redirect()->route('invoices.index')->with('message', __('{record} has been {action}.', ['record' => __('Invoice'), 'action' => __('deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Invoice::whereIn('id', $request->selection)->get() as $invoice) {
            $invoice->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : $failed--;
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed]));
    }

    public function destroyPermanently(Invoice $invoice)
    {
        if ($invoice->forceDelete()) {
            return redirect()->route('invoices.index')->with('message', __('{record} has been {action}.', ['record' => __('Invoice'), 'action' => __('permanently deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function edit(Invoice $invoice)
    {
        return Inertia::render('Invoice/Form', [
            'customer_fields' => Field::ofModel('customer')->get(),
            'item_fields'     => Field::ofModel('invoiceitem')->get(),
            'fields'          => Field::ofModel('maininvoice')->get(),
            'note'            => Note::where('default_sale', 1)->first(),
            'companies'       => Company::get(['id as value', 'name as label']),
            'tax_rates'       => TaxRate::all(['id as value', 'name as label', 'id', 'rate', 'fixed']),
            'edit'            => ['data' => $invoice->load(['company', 'customer', 'items', 'user', 'taxes', 'items.taxes'])],
        ]);
    }

    public function index(Request $request)
    {
        $filters = $request->all('search', 'status', 'recurring', 'start_date', 'end_date', 'trashed', 'transfer', 'company');

        return Inertia::render('Invoice/List', [
            'filters'        => $filters,
            'companies'      => Company::get(['id', 'name']),
            'payment_fields' => Field::ofModel('payment')->get(),
            'invoices'       => new InvoiceCollection(
                Invoice::with(['company', 'customer', 'items', 'user'])->filter($filters)->orderByDesc('date')->latest()->paginate()->onEachSide(2)->withQueryString()
            ),
        ]);
    }

    public function restore(Invoice $invoice)
    {
        $invoice->restore();
        $invoice->items->each->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => __('Invoice'), 'action' => __('restored')]));
    }

    public function show(Invoice $invoice)
    {
        return Inertia::render('Invoice/Details', [
            'invoice' => $invoice->load(['company', 'customer', 'items', 'user', 'taxes', 'items.taxes']),
        ]);
    }

    public function download(Request $request, $id)
    {
        $invoice = Invoice::with(['company', 'customer', 'items', 'user', 'taxes', 'items.taxes'])->find($id);

        if ($request->view == 1) {
            return view('invoice', [
                'invoice'  => $invoice,
                'settings' => get_settings(),
            ]);
        }

        $pdf = PDF::loadView('invoice', [
            'invoice'  => $invoice,
            'settings' => get_settings(),
        ])->setOptions(['defaultFont' => 'sans-serif']);

        return $pdf->download('Invoice No. ' . $invoice->id . '.pdf');
    }

    public function store(InvoiceRequest $request)
    {
        $invoice = (new PrepareOrder($request->validated(), new Invoice()))->save();
        $invoice->saveAttachments($request->file('attachments'));

        return redirect()->route('invoices.index')
            ->with('message', __('{record} has been {action}.', ['record' => __('Invoice'), 'action' => __('created')]));
    }

    public function update(InvoiceRequest $request, Invoice $invoice)
    {
        $invoice = (new PrepareOrder($request->validated(), $invoice, true))->save();
        $invoice->saveAttachments($request->file('attachments'));

        return redirect()->route('invoices.index')
            ->with('message', __('{record} has been {action}.', ['record' => __('Invoice'), 'action' => __('updated')]));
    }

    public function cancelReceipt(Request $request, Invoice $invoice)
    {
        if ($request->user()->cant('update-invoices')) {
            return redirect()->route('invoices.index')
                ->with('error', __('You do not have permissions to perform this action.'));
        }

        $invoice->update(['receipt' => null]);
        $invoice->customer->notify(new TransferReceiptCanceled($invoice));

        return redirect()->route('invoices.index')
            ->with('message', __('Receipt has been marked as canceled.'));
    }
}
