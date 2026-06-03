<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InvoiceCanceled extends Notification
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
        // $url = URL::signedRoute('invoice.view', ['id' => $this->invoice->id, 'hash' => $this->invoice->hash]);

        $message = (new MailMessage)
            ->error()
            ->subject(__('Invoice Canceled'))
            ->greeting('Hello ' . $this->invoice->customer->name . ',')
            ->line(__('Invoice # {number} (Reference: {reference}) is canceled.', ['number' => $this->invoice->id, 'reference' => $this->invoice->reference]))
            ->line(__('If there has been an error please let me know so we can resolve that together.'))
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
