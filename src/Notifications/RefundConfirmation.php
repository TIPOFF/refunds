<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Notifications;

use Assert\Assert;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Tipoff\Refunds\Models\Refund;

class RefundConfirmation extends Notification
{
    use Queueable;

    public Refund $refund;

    public function __construct(Refund $refund)
    {
        Assert::that($refund->issued_at)->notNull('Refund has not been issued.');

        $this->refund = $refund;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

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
            $message->line("Refund voucher code: " . $refund->getVoucher()->getCode());
        }

        return $message;
    }
}
