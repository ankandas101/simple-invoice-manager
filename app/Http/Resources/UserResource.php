<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);
        $data['extra_attributes'] = orderCustomFields($data, 'user');

        return $data;
    }
}
