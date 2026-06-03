<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\TaxRate;
use Illuminate\Http\Request;
use App\Http\Requests\TaxRateRequest;
use App\Http\Resources\TaxRateResource;
use App\Http\Resources\TaxRateCollection;

class TaxRateController extends Controller
{
    public function create()
    {
        return Inertia::render('TaxRate/Form');
    }

    public function destroy(Request $request, TaxRate $tax_rate)
    {
        if ($tax_rate->{$request->force ? 'forceDelete' : 'delete'}()) {
            return redirect()->route('tax_rates.index')->with('message', __('{record} has been {action}.', ['record' => __('Tax rate'), 'action' => __('deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (TaxRate::withTrashed()->whereIn('id', $request->selection)->get() as $tax_rate) {
            $tax_rate->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : $failed--;
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed]));
    }

    public function destroyPermanently(TaxRate $tax_rate)
    {
        if ($tax_rate->forceDelete()) {
            return redirect()->route('tax_rates.index')->with('message', __('{record} has been {action}.', ['record' => __('Tax rate'), 'action' => __('permanently deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function edit(TaxRate $tax_rate)
    {
        return Inertia::render('TaxRate/Form', [
            'edit' => new TaxRateResource($tax_rate),
        ]);
    }

    public function index(Request $request)
    {
        $filters = $request->all('search', 'trashed');

        return Inertia::render('TaxRate/List', [
            'filters'   => $filters,
            'tax_rates' => new TaxRateCollection(
                TaxRate::filter($filters)->orderByDesc('id')->paginate()->onEachSide(2)->withQueryString()
            ),
        ]);
    }

    public function restore(TaxRate $tax_rate)
    {
        $tax_rate->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => __('Tax rate'), 'action' => __('restored')]));
    }

    public function store(TaxRateRequest $request)
    {
        TaxRate::create($request->validated());

        return redirect()->route('tax_rates.index')->with('message', __('{record} has been {action}.', ['record' => __('Tax rate'), 'action' => __('created')]));
    }

    public function update(TaxRateRequest $request, TaxRate $tax_rate)
    {
        $tax_rate->update($request->validated());

        return redirect()->route('tax_rates.index')->with('message', __('{record} has been {action}.', ['record' => __('Tax rate'), 'action' => __('updated')]));
    }
}
