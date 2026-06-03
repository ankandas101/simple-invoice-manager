<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Field;
use App\Models\Company;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Requests\PaymentRequest;
use App\Http\Resources\PaymentCollection;

class PaymentController extends Controller
{
    public function destroy(Request $request, Payment $payment)
    {
        if ($payment->{$request->force ? 'forceDelete' : 'delete'}()) {
            return redirect()->route('payments.index')->with('message', __('{record} has been {action}.', ['record' => __('Payment'), 'action' => __('deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Payment::whereIn('id', $request->selection)->get() as $payment) {
            $payment->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : $failed--;
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed]));
    }

    public function destroyPermanently(Payment $payment)
    {
        if ($payment->forceDelete()) {
            return redirect()->route('payments.index')->with('message', __('{record} has been {action}.', ['record' => __('Payment'), 'action' => __('permanently deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function index(Request $request)
    {
        $filters = $request->all('search', 'start_date', 'end_date', 'trashed', 'company');

        return Inertia::render('Payment/List', [
            'filters'   => $filters,
            'companies' => Company::get(['id', 'name']),
            'fields'    => Field::ofModel('payment')->get(),
            'payments'  => new PaymentCollection(
                Payment::with(['company', 'customer', 'user', 'invoice:id,number,company_number'])
                    ->filter($filters)->orderByDesc('date')->latest()->paginate()->onEachSide(2)->withQueryString()
            ),
        ]);
    }

    public function restore(Payment $payment)
    {
        $payment->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => __('Payment'), 'action' => __('restored')]));
    }

    public function show(Payment $payment)
    {
        return Inertia::render('Payment/Details', [
            'payment' => $payment->load(['company', 'customer', 'user']),
        ]);
    }

    public function store(PaymentRequest $request)
    {
        $send_receipt = $request->send_receipt;
        unset($request['send_receipt']);

        $payment = Payment::create($request->validated());

        if ($send_receipt || $payment->invoice->receipt) {
            $payment->invoice->customer->notify(new \App\Notifications\PaymentReceived($payment));
        }

        if ($request->is_ajax == 'yes') {
            return response()->json($payment);
        }

        return redirect()->route('payments.index')->with('message', __('{record} has been {action}.', ['record' => __('Payment'), 'action' => __('created')]));
    }

    public function update(PaymentRequest $request, Payment $payment)
    {
        $payment->update($request->validated());
        if ($request->is_ajax == 'yes') {
            return response()->json($payment);
        }

        return redirect()->route('payments.index')->with('message', __('{record} has been {action}.', ['record' => __('Payment'), 'action' => __('updated')]));
    }
}
