<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FieldRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'        => 'required|unique:fields,name,' . optional($this->route('field'))->id,
            'type'        => 'required',
            'slug'        => 'nullable',
            'order'       => 'nullable',
            'options'     => 'nullable',
            'models'      => 'required|array|min:1',
            'description' => 'nullable',
            'show'        => 'nullable|boolean',
            'required'    => 'nullable|boolean',
        ];
    }
}
