<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);
        $data['extra_attributes'] = orderCustomFields($data, 'payment');

        return $data;
    }
}
