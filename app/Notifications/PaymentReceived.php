<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentReceived extends Notification
{
    use Queueable;

    public $payment;

    public function __construct($payment)
    {
        $this->payment = $payment;
        session()->forget('mail_logo');
        session()->forget('mail_name');
    }

    public function toMail($notifiable)
    {
        $prefer_company_email = get_settings('prefer_company_email');
        $url = URL::signedRoute('views.payment', ['id' => $this->payment->id, 'hash' => $this->payment->hash]);

        $message = (new MailMessage)
            ->subject(__('Payment Received ({company})', ['number' => $this->payment->id, 'company' => $this->payment->company->name]))
            ->greeting('Hello ' . $this->payment->customer->name . ',')
            ->line(__('We have received payment amounting to :amount for invoice # {number} (Reference: {reference}).', [
                'amount'    => Number::currency($this->payment->amount, in: get_settings('currency_code') ?? 'USD'),
                'number'    => $this->payment->invoice->id,
                'reference' => $this->payment->invoice->reference,
            ]))
            ->line(__('You can view the {x} by clicking the following button', ['x' => __('Payment')]))
            ->action(__('View Payment'), $url)
            ->line(__('Thank you for your business!'));

        if ($prefer_company_email ?? null) {
            session(['mail_logo' => $this->payment->company->logo, 'mail_name' => $this->payment->company->name]);
            $message->salutation(str(__('Regards') . ',<br />' . $this->payment->company->name ?? config('app.name'))->toHtmlString())
                ->from($this->payment->company->email ?? config('mail.from.address'), $this->payment->company->name ?? config('mail.from.name'));
        }

        return $message;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }
}
