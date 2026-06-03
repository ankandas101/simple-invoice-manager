<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class QuotationCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public $quotation;

    public function __construct($quotation)
    {
        $this->quotation = $quotation;
        session()->forget('mail_logo');
        session()->forget('mail_name');
    }

    public function toMail($notifiable)
    {
        $prefer_company_email = get_settings('prefer_company_email');
        $url = URL::signedRoute('views.quotation', ['id' => $this->quotation->id, 'hash' => $this->quotation->hash]);

        $message = (new MailMessage())
            ->subject(__('Quotation # {number} ({company})', ['number' => $this->quotation->id, 'company' => $this->quotation->company->name]))
            ->greeting('Hello ' . $this->quotation->customer->name . ',')
            ->line(__('New quotation # {number} (Reference: {reference}) has been created.', ['number' => $this->quotation->id, 'reference' => $this->quotation->reference]))
            ->line(__('You can view the {x} by clicking the following button', ['x' => __('Quotation')]))
            ->action(__('View Quotation'), $url)
            ->line(__('Thank you!'));

        if ($prefer_company_email ?? null) {
            session(['mail_logo' => $this->quotation->company->logo, 'mail_name' => $this->quotation->company->name]);
            $message->salutation(str(__('Regards') . ',<br />' . $this->quotation->company->name ?? config('app.name'))->toHtmlString())
                ->from($this->quotation->company->email ?? config('mail.from.address'), $this->quotation->company->name ?? config('mail.from.name'));
        }

        return $message;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }
}
