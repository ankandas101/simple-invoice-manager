<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;

class NoteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'          => 'required|unique:notes,name,' . optional($this->route('note'))->id,
            'details'       => 'required',
            'default_sale'  => 'nullable|boolean',
            'default_quote' => 'nullable|boolean',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = $this->validator->validated();
        if ($data['default_sale']) {
            DB::table('notes')->update(['default_sale' => 0]);
        }
        if ($data['default_quote']) {
            DB::table('notes')->update(['default_quote' => 0]);
        }

        return $data;
    }
}
