<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Models;

use Assert\Assert;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Tipoff\Authorization\Models\User;
use Tipoff\Payments\Models\Payment;
use Tipoff\Refunds\Enums\RefundMethod;
use Tipoff\Refunds\Notifications\RefundConfirmation;
use Tipoff\Refunds\Services\IssueRefund;
use Tipoff\Support\Casts\Enum;
use Tipoff\Support\Contracts\Checkout\Vouchers\VoucherInterface;
use Tipoff\Support\Contracts\Payment\PaymentInterface;
use Tipoff\Support\Contracts\Payment\RefundInterface;
use Tipoff\Support\Models\BaseModel;
use Tipoff\Support\Traits\HasCreator;
use Tipoff\Support\Traits\HasPackageFactory;
use Tipoff\Support\Traits\HasUpdater;

/**
 * @property int id
 * @property Payment payment
 * @property string refund_number
 * @property int amount
 * @property RefundMethod method
 * @property string|null transaction_number
 * @property VoucherInterface|null voucher
 * @property User|null issuer
 * @property User creator
 * @property User updater
 * @property Carbon issued_at
 * @property Carbon created_at
 * @property Carbon updated_at
 * // Raw relations
 * @property int payment_id
 * @property int voucher_id
 * @property int issuer_id
 * @property int creator_id
 * @property int updater_id
 */
class Refund extends BaseModel implements RefundInterface
{
    use HasCreator;
    use HasUpdater;
    use HasPackageFactory;

    protected $casts = [
        'amount' => 'integer',
        'method' => Enum::class . ':' . RefundMethod::class,
        'issued_at' => 'datetime',
        'payment_id' => 'integer',
        'voucher_id' => 'integer',
        'issuer_id' => 'integer',
        'creator_id' => 'integer',
        'updater_id' => 'integer',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (Refund $refund) {
            $refund->refund_number = static::generateRefundNumber();
        });

        static::saving(function (Refund $refund) {
            Assert::lazy()
                ->that($refund->payment_id)->notEmpty('A refund must be applied to a payment.')
                ->that($refund->amount)->notEmpty('A refund must be for an amount.')
                ->that($refund->amount)->lessOrEqualThan($refund->payment->amount_refundable, 'Please check the payment for the max amount that can be refunded.')
                ->verifyNow();
        });

        static::saved(function (Refund $refund) {
            $payment = $refund->payment;
            $payment->amount_refunded = static::amountRefunded($payment);
            $payment->save();
        });
    }

    public function issue(): self
    {
        return app(IssueRefund::class)($this);
    }

    public function isStripe(): bool
    {
        return $this->method->is(RefundMethod::STRIPE());
    }

    public function isVoucher(): bool
    {
        return $this->method->is(RefundMethod::VOUCHER());
    }

    public function notifyUser(): self
    {
        $this->payment->user->notify(new RefundConfirmation($this));

        return $this;
    }

    public function decoratedAmount(): string
    {
        return '$' . number_format($this->amount / 100, 2, '.', ',');
    }

    private static function generateRefundNumber(): string
    {
        do {
            $token = Str::of(Carbon::now('America/New_York')->format('ymdB'))->substr(1, 7) . Str::upper(Str::random(2));
        } while (static::query()->where('refund_number', $token)->exists()); //check if the token already exists and if it does, try again

        return $token;
    }

    public function payment()
    {
        return $this->belongsTo(app('payment'));
    }

    public function voucher()
    {
        return $this->belongsTo(app('voucher'));
    }

    public function issuer()
    {
        return $this->belongsTo(app('user'), 'issuer_id');
    }

    public function getVoucher(): VoucherInterface
    {
        return $this->voucher;
    }

    public static function createRefund(PaymentInterface $payment, int $amount, string $method): RefundInterface
    {
        $result = new static();
        $result->amount = $amount;
        $result->method = RefundMethod::byValue($method);
        $result->payment_id = $payment->getId();
        $result->save();

        return $result;
    }

    public static function amountRefunded(PaymentInterface $payment): int
    {
        $result = static::query()
            ->whereNotNull('issued_at')
            ->where('payment_id', '=', $payment->getId())
            ->sum('amount');

        return (int) $result;
    }
}
