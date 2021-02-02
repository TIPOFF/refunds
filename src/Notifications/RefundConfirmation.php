<?php

namespace Tipoff\Refunds\Notifications;

use Tipoff\Refunds\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundConfirmation extends Notification
{
    use Queueable;

    /**
     * Refund.
     * @var Refund
     */
    public $refund;

    /**
     * Create a new notification instance.
     *
     * @param Refund
     */
    public function __construct(Refund $refund)
    {
        $this->refund = $refund;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $refund = $this->refund;

        $message = (new MailMessage)
            ->subject('Refund')
            ->line('Your refund was processed successfully.')
            ->line('Amount: ' . $refund->decoratedAmount());

        if ($refund->isStripe()) {
            $message->line('Transaction number: ' . $refund->transaction_number);
        }

        if ($refund->isVoucher()) {
            $message->line("Refund voucher code: " . $this->voucher->code);
        }

        return $message;
    }
}
