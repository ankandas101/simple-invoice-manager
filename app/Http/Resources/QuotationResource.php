<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);
        $data['items'] = new QuotationItemCollection($this->items);
        $data['company'] = new CompanyResource($data['company']);
        $data['customer'] = new CustomerResource($data['customer']);
        $data['extra_attributes'] = orderCustomFields($data, 'mainquotation');

        return $data;
    }
}
