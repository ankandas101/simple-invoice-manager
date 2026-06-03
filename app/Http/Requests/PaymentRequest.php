<?php

namespace App\Http\Requests;

use App\Rules\ExtraAttributes;
use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date'             => 'required',
            'reference'        => 'nullable|alpha_dash|unique:payments,reference,' . optional($this->route('payment'))->id,
            'customer_id'      => 'required',
            'company_id'       => 'required',
            'invoice_id'       => 'required',
            'amount'           => 'required|numeric',
            'method'           => 'nullable',
            'note'             => 'nullable',
            'send_receipt'     => 'nullable|boolean',
            'extra_attributes' => ['nullable', new ExtraAttributes('payment')],
        ];
    }
}
