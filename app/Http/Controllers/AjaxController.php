<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Notifications\PaymentReceived;
use Illuminate\Validation\ValidationException;

class AjaxController extends Controller
{
    public function charge(Request $request, $id, $hash)
    {
        if (! $request->gateway) {
            abort(403, 'Unknown gateway!');
        }
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($id);
        if ($invoice->hash != $hash) {
            abort(404);
        }
        $settings = get_settings();
        if ($request->gateway == 'PayPal') {
            $data = $request->paypal;
            $amount = $invoice->grand_total - $invoice->paid;
            logger()->info('PayPal Request Data: ', ['data' => $data]);
            $fees = ($data['fees'] ?? null) ?: formatDecimal(calculate_gateway_fees($amount, $settings['paypal'], $invoice->company?->country == $invoice->customer?->country));
            $paid = $data['amount']['value'] - $fees;
            if ($settings['currency_code'] == $data['amount']['currency_code']) {
                try {
                    $payment = Payment::create([
                        'date'           => now()->toDateString(),
                        'fees'           => $fees,
                        'method'         => 'PayPal',
                        'invoice_id'     => $invoice->id,
                        'user_id'        => $invoice->user_id,
                        'account_id'     => $invoice->account_id,
                        'company_id'     => $invoice->company_id,
                        'customer_id'    => $invoice->customer_id,
                        'transaction_id' => $data['id'],
                        'status'         => $data['status'],
                        'amount'         => $paid,
                        'note'           => __('Payment for invoice # {number} is paid using PayPal gateway.', ['number' => $invoice->id]),
                    ]);
                    $payment->customer->notify(new PaymentReceived($payment));

                    return back()->with('message', __('{record} has been {action}.', ['record' => __('Payment'), 'action' => __('created')]));
                } catch (\Exception $e) {
                    // return back()->with('error', $e->getMessage());
                    throw ValidationException::withMessages(['paypal' => $e->getMessage()]);
                }
            }
        }

        if ($request->gateway == 'Stripe') {
            $token = $request->stripe;
            $amount = $invoice->grand_total - $invoice->paid;
            logger()->info('Stripe Request Token: ', ['token' => $token]);
            $stripe = new \Stripe\StripeClient($settings['stripe']->secret_key);
            $fees = ($token['fees'] ?? null) ?: formatDecimal(calculate_gateway_fees($amount, $settings['stripe'], $invoice->company?->country == $invoice->customer?->country));
            try {
                $response = $stripe->charges->create([
                    'source'      => $token['id'],
                    'amount'      => ($amount + $fees) * 100,
                    'currency'    => $settings['currency_code'] ?: 'USD',
                    'metadata'    => ['reference' => $invoice->reference],
                    'description' => 'Payment for invoice # ' . $invoice->id,
                ]);
                logger()->info('Stripe Response: ', ['response' => $response]);
                $payment = Payment::create([
                    'date'           => now()->toDateString(),
                    'fees'           => $fees,
                    'method'         => 'Stripe',
                    'invoice_id'     => $invoice->id,
                    'user_id'        => $invoice->user_id,
                    'account_id'     => $invoice->account_id,
                    'company_id'     => $invoice->company_id,
                    'customer_id'    => $invoice->customer_id,
                    'transaction_id' => $response->id,
                    'status'         => $response->status,
                    'amount'         => $invoice->grand_total,
                    'receipt_url'    => $response->receipt_url,
                    'note'           => __('Payment for invoice # {number} is paid using Stripe gateway.', ['number' => $invoice->id]),
                ]);
                $payment->customer->notify(new PaymentReceived($payment));

                return back()->with('message', __('{record} has been {action}.', ['record' => __('Payment'), 'action' => __('created')]));
            } catch (\Exception $e) {
                // return back()->with('error', $e->getMessage());
                throw ValidationException::withMessages(['stripe' => $e->getMessage()]);
            }
        }
    }

    public function language($language)
    {
        $langFiles = collect(json_decode(File::get(lang_path('languages.json')))->available)->pluck('value')->all();
        if (! in_array($language, $langFiles)) {
            return back()->with('error', __('Language is not available yet.'));
        }
        app()->setlocale($language);
        session(['language' => $language]);

        return back()->with('message', __('Language has been changed.'));
    }

    public function PayPalIPN($data)
    {
        logger()->info('PayPal notification received.', ['data' => $data]);
    }
}
