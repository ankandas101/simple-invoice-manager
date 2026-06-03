<?php

namespace App\Http\Requests;

use App\Rules\ExtraAttributes;
use Illuminate\Foundation\Http\FormRequest;

class QuotationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date'                     => 'required|date',
            'reference'                => 'nullable|alpha_dash|unique:quotations,reference,' . optional($this->route('quotation'))->id,
            'company_id'               => 'required|exists:companies,id',
            'customer_id'              => 'required|exists:customers,id',
            'expiry_date'              => 'nullable|date',
            'status'                   => 'nullable|in:pending,sent,confirmed,canceled',
            'shipping'                 => 'nullable',
            'note'                     => 'nullable',
            'taxes'                    => 'nullable',
            'tax_method'               => 'nullable',
            'discount'                 => 'nullable',
            'extra_attributes'         => ['nullable', new ExtraAttributes('mainquotation')],
            'items'                    => 'required|array|min:1',
            'items.0.name'             => 'required',
            'items.0.price'            => 'required|numeric',
            'items.0.quantity'         => 'required|numeric',
            'items.*.name'             => 'nullable',
            'items.*.price'            => 'nullable|numeric',
            'items.*.net_price'        => 'nullable|numeric',
            'items.*.unit_price'       => 'nullable|numeric',
            'items.*.quantity'         => 'nullable|numeric',
            'items.*.details'          => 'nullable',
            'items.*.taxes'            => 'nullable',
            'items.*.tax_method'       => 'nullable',
            'items.*.discount'         => 'nullable',
            'items.*.extra_attributes' => ['nullable', new ExtraAttributes('quotationitem')],

            'attachments'   => 'nullable',
            'attachments.*' => 'mimes:' . ((get_settings('attachment_exts') ?? null) ?: 'jpg,png,pdf,xlsx,docx,zip'),
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = $this->validator->validated();
        $data['items'] = collect($data['items'])->filter(function ($item) {
            return isset($item['name']) && isset($item['quantity']) && isset($item['price']);
        })->all();

        return $data;
    }
}
