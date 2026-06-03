<?php

namespace App\Http\Requests;

use App\Rules\ExtraAttributes;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'             => 'required|unique:products,name,' . optional($this->route('product'))->id,
            'price'            => 'required|numeric',
            'details'          => 'nullable',
            'tax_method'       => 'nullable',
            'taxes'            => 'nullable|array',
            'extra_attributes' => ['nullable', new ExtraAttributes('product')],
        ];
    }
}
