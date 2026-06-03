<?php

namespace App\Http\Requests;

use App\Rules\ExtraAttributes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'phone'            => 'nullable',
            'customer_id'      => 'nullable',
            'roles'            => 'array|min:1',
            'name'             => 'required|max:50',
            'edit_all'         => 'nullable|boolean',
            'view_all'         => 'nullable|boolean',
            'email'            => 'required|email|unique:users,email,' . optional($this->route('user'))->id,
            'password'         => optional($this->route('user'))->id ? 'nullable|confirmed|min:8' : 'required|confirmed|min:8',
            'extra_attributes' => ['nullable', new ExtraAttributes('user')],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = $this->validator->validated();

        if ($this->input('password')) {
            $data['password'] = Hash::make($this->input('password'));
        } else {
            unset($data['password']);
        }

        return $data;
    }
}
