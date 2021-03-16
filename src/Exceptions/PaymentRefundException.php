<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Exceptions;

use Exception;
use Throwable;

class PaymentRefundException extends Exception implements RefundException
{
    public static function stripeNotConfigured($code = 0, Throwable $previous = null): self
    {
        return new static('Stripe not configured for location.', $code, $previous);
    }

    public static function paymentRequired($code = 0, Throwable $previous = null): self
    {
        return new static('Original payment id method is required.', $code, $previous);
    }

    public static function alreadyIssued($code = 0, Throwable $previous = null): self
    {
        return new static('Refund has already been issued.', $code, $previous);
    }

    public static function amountTooLarge($code = 0, Throwable $previous = null): self
    {
        return new static('Please check the payment for the max amount that can be refunded.', $code, $previous);
    }

    public static function unhandledMethod($code = 0, Throwable $previous = null): self
    {
        return new static('Refund method is not handled.', $code, $previous);
    }

    public function __construct(string $message, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
