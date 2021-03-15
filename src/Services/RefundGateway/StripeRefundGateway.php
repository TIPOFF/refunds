<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Services\RefundGateway;

use Exception;
use Stripe\Stripe;
use Tipoff\Locations\Models\Location;
use Tipoff\Payments\Enums\Gateway;
use Tipoff\Payments\Objects\PaymentSettings;
use Tipoff\Refunds\Exceptions\PaymentRefundException;
use Tipoff\Support\Contracts\Payment\ChargeableInterface;

class StripeRefundGateway implements RefundGateway
{
    public function getGatewayType(): Gateway
    {
        return Gateway::STRIPE();
    }

    public function refund(Location $location, ChargeableInterface $user, int $amount, array $options = []): string
    {
        $paymentSettings = PaymentSettings::forLocation($location);
        if (! $paymentSettings->getStripeSecret()) {
            throw PaymentRefundException::stripeNotConfigured();
        }

        if (empty($options['charge_number'])) {
            throw PaymentRefundException::paymentRequired();
        }

        try {
            Stripe::setApiKey($paymentSettings->getStripeSecret());

            $refund = $user->refund($options['charge_number'], [
                'amount' => $amount,
            ]);

            return $refund->id;
        } catch (Exception $exception) {
            throw new PaymentRefundException($exception->getMessage());
        }
    }
}
