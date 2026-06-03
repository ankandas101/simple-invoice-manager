<?php

namespace App\Observers;

use App\Models\Payment;

class PaymentObserver
{
    public $afterCommit = true;

    public function created(Payment $payment)
    {
        $payment->customer->transactions()->create([
            'account_id' => $payment->account_id,
            'payment_id' => $payment->id,
            'amount'     => 0 - $payment->amount,
            'type'       => 'payment-created',
            'invoice_id' => $payment->invoice_id,
        ]);
        $payment->invoice->increment('paid', $payment->amount);
        $payment->invoice->refresh();
        if ($payment->invoice->grand_total <= $payment->invoice->paid) {
            $payment->invoice->update(['status' => 'paid']);
        }
    }

    public function deleting(Payment $payment)
    {
        if (! $payment->deleted_at) {
            $payment->customer->transactions()->create([
                'account_id' => $payment->account_id,
                'payment_id' => $payment->id,
                'amount'     => $payment->amount,
                'type'       => 'payment-deleted',
                'invoice_id' => $payment->invoice_id,
            ]);

            $payment->invoice->decrement('paid', $payment->amount);
            $payment->invoice->refresh();
            if ($payment->invoice->grand_total <= $payment->invoice->paid) {
                $payment->invoice->update(['status' => 'paid']);
            } elseif ($payment->invoice->due_date && now()->gt($payment->invoice->due_date)) {
                $payment->invoice->update(['status' => 'overdue']);
            } else {
                $payment->invoice->update(['status' => 'pending']);
            }
        }
    }

    public function restored(Payment $payment)
    {
        $payment->customer->transactions()->create([
            'account_id' => $payment->account_id,
            'payment_id' => $payment->id,
            'amount'     => 0 - $payment->amount,
            'type'       => 'payment-restored',
            'invoice_id' => $payment->invoice_id,
        ]);

        $payment->invoice->increment('paid', $payment->amount);
        $payment->invoice->refresh();
        if ($payment->invoice->grand_total <= $payment->invoice->paid) {
            $payment->invoice->update(['status' => 'paid']);
        } elseif ($payment->invoice->due_date && now()->gt($payment->invoice->due_date)) {
            $payment->invoice->update(['status' => 'overdue']);
        } else {
            $payment->invoice->update(['status' => 'pending']);
        }
    }

    public function updated(Payment $payment)
    {
        if ($payment->amount != $payment->getOriginal('amount')) {
            $payment->customer->transactions()->create([
                'account_id' => $payment->getOriginal('account_id') ?? $payment->account_id,
                'payment_id' => $payment->id,
                'amount'     => $payment->getOriginal('amount'),
                'type'       => 'payment-edit',
                'invoice_id' => $payment->invoice_id,
            ]);
            $payment->customer->transactions()->create([
                'account_id' => $payment->account_id,
                'payment_id' => $payment->id,
                'amount'     => 0 - $payment->amount,
                'type'       => 'payment-updated',
                'invoice_id' => $payment->invoice_id,
            ]);

            $payment->invoice->increment('paid', $payment->amount - $payment->getOriginal('amount'));
            $payment->invoice->refresh();
            if ($payment->invoice->grand_total <= $payment->invoice->paid) {
                $payment->invoice->update(['status' => 'paid']);
            } elseif ($payment->invoice->due_date && now()->gt($payment->invoice->due_date)) {
                $payment->invoice->update(['status' => 'overdue']);
            } else {
                $payment->invoice->update(['status' => 'pending']);
            }
        }
    }
}
