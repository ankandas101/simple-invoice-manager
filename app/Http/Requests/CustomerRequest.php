<?php

namespace App\Http\Requests;

use App\Rules\ExtraAttributes;
use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'             => 'required',
            'company'          => 'nullable',
            'address'          => 'nullable',
            'city'             => 'nullable',
            'postal_code'      => 'nullable',
            'state'            => 'nullable',
            'country'          => 'nullable',
            'email'            => 'nullable|email|unique:customers,email,' . optional($this->route('customer'))->id,
            'phone'            => 'nullable|required_without:email|unique:customers,phone,' . optional($this->route('customer'))->id,
            'extra_attributes' => ['nullable', new ExtraAttributes('customer')],
        ];
    }
}
