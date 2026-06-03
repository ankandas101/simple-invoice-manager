<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'        => 'required|unique:roles,name,' . optional($this->route('role'))->id,
            'permissions' => optional($this->route('role'))->id ? 'required|array' : 'nullable',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = $this->validator->validated();
        $data['guard_name'] = 'web';

        return $data;
    }
}
