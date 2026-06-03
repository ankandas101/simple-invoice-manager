<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Inertia\Inertia;
use App\Models\Field;
use App\Models\Company;
use App\Models\TaxRate;
use App\Models\Quotation;
use Illuminate\Http\Request;
use App\Actions\Tec\PrepareOrder;
use App\Http\Requests\QuotationRequest;
use App\Http\Resources\QuotationCollection;

class QuotationController extends Controller
{
    public function create()
    {
        return Inertia::render('Quotation/Form', [
            'customer_fields' => Field::ofModel('customer')->get(),
            'item_fields'     => Field::ofModel('quotationitem')->get(),
            'fields'          => Field::ofModel('mainquotation')->get(),
            'note'            => Note::where('default_quote', 1)->first(),
            'companies'       => Company::get(['id as value', 'name as label']),
            'tax_rates'       => TaxRate::all(['id as value', 'name as label', 'id', 'rate', 'fixed']),
        ]);
    }

    public function destroy(Request $request, Quotation $quotation)
    {
        if ($quotation->{$request->force ? 'forceDelete' : 'delete'}()) {
            return redirect()->route('quotations.index')->with('message', __('{record} has been {action}.', ['record' => __('Quotation'), 'action' => __('deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Quotation::whereIn('id', $request->selection)->get() as $quotation) {
            $quotation->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : $failed--;
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed]));
    }

    public function destroyPermanently(Quotation $quotation)
    {
        if ($quotation->forceDelete()) {
            return redirect()->route('quotations.index')->with('message', __('{record} has been {action}.', ['record' => __('Quotation'), 'action' => __('permanently deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function edit(Quotation $quotation)
    {
        return Inertia::render('Quotation/Form', [
            'customer_fields' => Field::ofModel('customer')->get(),
            'item_fields'     => Field::ofModel('quotationitem')->get(),
            'fields'          => Field::ofModel('mainquotation')->get(),
            'note'            => Note::where('default_quote', 1)->first(),
            'companies'       => Company::get(['id as value', 'name as label']),
            'tax_rates'       => TaxRate::all(['id as value', 'name as label', 'id', 'rate', 'fixed']),
            'edit'            => ['data' => $quotation->load(['company', 'customer', 'items', 'user', 'taxes', 'items.taxes'])],
        ]);
    }

    public function index(Request $request)
    {
        $filters = $request->all('search', 'start_date', 'end_date', 'status', 'trashed', 'company');

        return Inertia::render('Quotation/List', [
            'filters'    => $filters,
            'companies'  => Company::get(['id', 'name']),
            'quotations' => new QuotationCollection(
                Quotation::with(['company', 'customer', 'items', 'user'])->filter($filters)->orderByDesc('date')->latest()->paginate()->onEachSide(2)->withQueryString()
            ),
        ]);
    }

    public function restore(Quotation $quotation)
    {
        $quotation->restore();
        $quotation->items->each->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => __('Quotation'), 'action' => __('restored')]));
    }

    public function show(Request $request, Quotation $quotation)
    {
        return Inertia::render('Quotation/Details', [
            'quotation' => $quotation->load(['company', 'customer', 'items', 'user', 'taxes', 'items.taxes']),
        ]);
    }

    public function store(QuotationRequest $request)
    {
        $form = $request->validated();
        $quotation = (new PrepareOrder($form, new Quotation()))->save();
        $quotation->saveAttachments($request->file('attachments'));

        return redirect()->route('quotations.index')
            ->with('message', __('{record} has been {action}.', ['record' => __('Quotation'), 'action' => __('created')]));
    }

    public function update(QuotationRequest $request, Quotation $quotation)
    {
        $form = $request->validated();
        $quotation = (new PrepareOrder($form, $quotation, true))->save();
        $quotation->saveAttachments($request->file('attachments'));

        return redirect()->route('quotations.index')
            ->with('message', __('{record} has been {action}.', ['record' => __('Quotation'), 'action' => __('updated')]));
    }
}
