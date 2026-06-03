<?php

namespace App\Http\Requests;

use App\Rules\ExtraAttributes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'             => 'required|unique:companies,name,' . optional($this->route('company'))->id,
            'contact_person'   => 'nullable',
            'address'          => 'nullable',
            'city'             => 'nullable',
            'postal_code'      => 'nullable',
            'state'            => 'nullable',
            'country'          => 'nullable',
            'email'            => 'nullable|email',
            'show_name'        => 'nullable|boolean',
            'phone'            => 'nullable|required_without:email',
            'logo'             => 'nullable|mimes:png,jpg,jpeg,svg',
            'logo_dark'        => 'nullable|mimes:png,jpg,jpeg,svg',
            'ss_image'         => 'nullable|mimes:png,jpg,jpeg,svg',
            'extra_attributes' => ['nullable', new ExtraAttributes('company')],

            'show_address'         => 'nullable|boolean',
            'allow_transfer'       => 'nullable|boolean',
            'bank_account_details' => 'nullable',
            'qrcode'               => 'nullable|mimes:png,jpg,jpeg,svg',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = $this->validator->validated();

        if ($this->has('logo') && $this->logo) {
            $data['logo'] = Storage::disk('assets')->url($this->logo->store('/images', 'assets'));
        } else {
            unset($data['logo']);
        }

        if ($this->has('logo_dark') && $this->logo_dark) {
            $data['logo_dark'] = Storage::disk('assets')->url($this->logo_dark->store('/images', 'assets'));
        } else {
            unset($data['logo_dark']);
        }

        if ($this->has('qrcode') && $this->qrcode) {
            $data['qrcode'] = Storage::disk('assets')->url($this->qrcode->store('/images', 'assets'));
        } else {
            unset($data['qrcode']);
        }

        return $data;
    }
}
