<?php namespace Tipoff\Refunds\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Tipoff\Refunds\Notifications\RefundConfirmation;
use Tipoff\Support\Models\BaseModel;
use Tipoff\Support\Traits\HasCreator;
use Tipoff\Support\Traits\HasPackageFactory;
use Tipoff\Support\Traits\HasUpdater;

class Refund extends BaseModel
{
    use HasCreator;
    use HasUpdater;
    use HasPackageFactory;

    const METHOD_STRIPE = 'Stripe';
    const METHOD_VOUCHER = 'Voucher';

    const REFUND_VOUCHER_TYPE_ID = 1;

    protected $guarded = ['id'];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($refund) {
            $refund->generateRefundNumber();
        });

        static::saving(function ($refund) {
            if (empty($refund->payment_id)) {
                throw new \Exception('A refund must be applied to a payment.');
            }
            if (empty($refund->amount)) {
                throw new \Exception('A refund must be for an amount.');
            }
            if ($refund->amount > ($refund->payment->amount_refundable)) {
                throw new \Exception('Please check the payment for the max amount that can be refunded.');
            }
        });

        static::created(function ($refund) {
            $refund
                ->payment
                ->generateAmountRefunded()
                ->save();
        });
    }

    /**
     * Issue refund.
     *
     * @return self|null
     * @throws \Exception
     */
    public function issue()
    {
        switch ($this->method) {
            case Refund::METHOD_STRIPE:
                return $this->stripeRefund();

            case Refund::METHOD_VOUCHER:
                return $this->voucherRefund();
        }
    }

    /**
     * Is a stripe refund.
     *
     * @return bool
     */
    public function isStripe()
    {
        return $this->method == Refund::METHOD_STRIPE;
    }

    /**
     * Is a voucher refund.
     *
     * @return bool
     */
    public function isVoucher()
    {
        return $this->method == Refund::METHOD_VOUCHER;
    }

    /**
     * Notify customner about refund.
     *
     * @return void
     */
    public function notifyCustomer()
    {
        $this->payment->customer->user->notify(new RefundConfirmation($this));
    }

    /**
     * Generate formated amount.
     *
     * @return string
     */
    public function decoratedAmount()
    {
        return '$' . number_format($this->amount / 100, 2, '.', ',');
    }

    /**
     * Refund transaction to voucher.
     *
     * @return Refund
     */
    public function voucherRefund()
    {
        $amount = $this->amount;

        /** @var Model $voucherModel */
        $voucherModel = app('voucher');

        $voucher = $voucherModel::create([
            'location_id' => $this->payment->order->location_id,
            'customer_id' => $this->payment->customer_id,
            'voucher_type_id' => Refund::REFUND_VOUCHER_TYPE_ID,
            'redeemable_at' => now(),
            'amount' => $amount,
            'creator_id' => $this->creator_id,
            'updater_id' => $this->updater_id,
        ]);

        $this->fill([
            'issued_at' => now(),
            'issuer_id' => auth()->id(),
            'voucher_id' => $voucher->id,
        ]);

        $this->save();

        return $this;
    }

    /**
     * Refund stripe payment.
     *
     * @return Refund
     */
    public function stripeRefund()
    {
        $options = [];

        $payment = $this->payment;
        $amount = $this->amount;

        Stripe::setApiKey($payment->order->location->stripe_secret);

        config(['cashier.key' => $payment->order->location->stripe_publishable]);
        config(['cashier.secret' => $payment->order->location->stripe_secret]);

        if (empty($payment->charge_id)) {
            throw new \Exception('Cant refund payment without charge id.');
        }
        if (! empty($amount)) {
            $options['amount'] = $amount;
        }
        $user = $payment->customer->user;

        $refund = $user->refund($payment->charge_id, $options);
        $payment->amount_refunded = $refund->amount;

        $this->fill([
            'amount' => $refund->amount,
            'issued_at' => now(),
            'issuer_id' => auth()->id(),
            'transaction_number' => $refund->id,
        ]);

        $this->save();

        return $this;
    }

    public function generateRefundNumber()
    {
        do {
            $token = Str::of(Carbon::now('America/New_York')->format('ymdB'))->substr(1, 7) . Str::upper(Str::random(2));
        } while (self::where('refund_number', $token)->first()); //check if the token already exists and if it does, try again

        $this->refund_number = $token;
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
}
