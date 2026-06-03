<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class AttachmentEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public $model, public $attachments)
    {
    }
}
