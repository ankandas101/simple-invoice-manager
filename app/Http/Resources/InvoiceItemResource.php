<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemResource extends JsonResource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);
        $data['extra_attributes'] = orderCustomFields($data, 'invoiceitem');

        return $data;
    }
}
