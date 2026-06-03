<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);
        $data['paid'] ??= 0;
        $data['items'] = new InvoiceItemCollection($this->items);
        $data['company'] = new CompanyResource($data['company']);
        $data['customer'] = new CustomerResource($data['customer']);
        $data['extra_attributes'] = orderCustomFields($data, 'maininvoice');

        return $data;
    }
}
