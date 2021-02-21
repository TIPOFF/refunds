<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Tipoff\Refunds\Models\Refund;

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
     * @param Refund $refund
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
            $message->line("Refund voucher code: " . $this->refund->voucher->code);
        }

        return $message;
    }
}
