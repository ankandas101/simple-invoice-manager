<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaxRateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'  => 'required|unique:tax_rates,name,' . optional($this->route('tax_rate'))->id,
            'rate'  => 'required|numeric',
            'fixed' => 'nullable|boolean',
        ];
    }
}
