<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Services;

use Carbon\Carbon;
use Tipoff\Refunds\Enums\RefundMethod;
use Tipoff\Refunds\Exceptions\PaymentRefundException;
use Tipoff\Refunds\Exceptions\VouchersNotAvailableException;
use Tipoff\Refunds\Models\Refund;
use Tipoff\Refunds\Services\RefundGateway\RefundGateway;
use Tipoff\Support\Contracts\Checkout\Vouchers\VoucherInterface;

class IssueRefund
{
    public function __invoke(Refund $refund): Refund
    {
        if ($refund->issued_at) {
            throw PaymentRefundException::alreadyIssued();
        }

        if ($refund->amount > $refund->payment->amount_refundable) {
            throw PaymentRefundException::amountTooLarge();
        }

        if ($refund->method->is(RefundMethod::STRIPE())) {
            return $this->paymentRefund($refund);
        }

        if ($refund->method->is(RefundMethod::VOUCHER())) {
            return $this->voucherRefund($refund);
        }

        throw PaymentRefundException::unhandledMethod();
    }

    private function paymentRefund(Refund $refund): Refund
    {
        $payment = $refund->payment;
        $service = app(RefundGateway::class);
        $refund->transaction_number = $service->refund($payment->location, $payment->user, $refund->amount, [
            'charge_number' => $payment->charge_number,
        ]);

        return $this->markAsIssued($refund);
    }

    private function voucherRefund(Refund $refund): Refund
    {
        /** @var VoucherInterface $service */
        $service = findService(VoucherInterface::class);
        throw_unless($service, VouchersNotAvailableException::class);

        $payment = $refund->payment;
        $voucher = $service::createRefundVoucher((int) $payment->location_id, $payment->user, $refund->amount);
        $refund->voucher_id = $voucher->getId();

        return $this->markAsIssued($refund);
    }

    private function markAsIssued(Refund $refund): Refund
    {
        $refund->issued_at = Carbon::now();
        $refund->issuer_id = auth()->id();
        $refund->save();

        return $refund;
    }
}
