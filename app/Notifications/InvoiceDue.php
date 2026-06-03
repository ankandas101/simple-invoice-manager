<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InvoiceDue extends Notification
{
    use Queueable;

    public $invoice;

    public function __construct($invoice)
    {
        $this->invoice = $invoice;
        session()->forget('mail_logo');
        session()->forget('mail_name');
    }

    public function toMail($notifiable)
    {
        $prefer_company_email = get_settings('prefer_company_email');
        $url = URL::signedRoute('views.invoice', ['id' => $this->invoice->id, 'hash' => $this->invoice->hash]);

        $message = (new MailMessage())
            ->subject(__('Invoice Due'))
            ->greeting('Hello ' . $this->invoice->customer->name . ',')
            ->line(__('I am checking in on Invoice # {number} (Reference: {reference}) due today {due_date}.', ['number' => $this->invoice->id, 'reference' => $this->invoice->reference, 'due_date' => $this->invoice->due_date ?? now()->toDateString()]))
            ->line(__('You can make payment by clicking the following button', ['x' => __('Invoice')]))
            ->action(__('Make Payment'), $url)
            ->line(__('If payment has already been made please let me know the date and reference. If payment is still pending we can extend a day grace period to help get that settled.'))
            ->line(__('Thank you!'));

        if ($prefer_company_email ?? null) {
            session(['mail_logo' => $this->invoice->company->logo, 'mail_name' => $this->invoice->company->name]);
            $message->salutation(str(__('Regards') . ',<br />' . $this->invoice->company->name ?? config('app.name'))->toHtmlString())
                ->from($this->invoice->company->email ?? config('mail.from.address'), $this->invoice->company->name ?? config('mail.from.name'));
        }

        return $message;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }
}
