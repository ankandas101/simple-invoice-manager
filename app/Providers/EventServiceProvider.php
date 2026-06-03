<?php

namespace App\Providers;

use App\Models\Payment;
use App\Events\AttachmentEvent;
use App\Observers\PaymentObserver;
use Illuminate\Auth\Events\Registered;
use App\Listeners\AttachmentEventListener;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AttachmentEvent::class => [AttachmentEventListener::class],
        Registered::class      => [SendEmailVerificationNotification::class],
    ];

    protected $observers = [
        Payment::class => [PaymentObserver::class],
    ];

    public function boot()
    {
        //
    }

    public function shouldDiscoverEvents()
    {
        return false;
    }
}
