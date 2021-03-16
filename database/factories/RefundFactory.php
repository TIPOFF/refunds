<?php namespace Tipoff\Refunds\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tipoff\Payments\Models\Payment;
use Tipoff\Refunds\Enums\RefundMethod;
use Tipoff\Refunds\Models\Refund;

class RefundFactory extends Factory
{
    protected $model = Refund::class;

    public function definition()
    {
        /** @var Payment $payment */
        $payment = randomOrCreate(app('payment'));

        return [
            'amount'     => $this->faker->numberBetween(1, $payment->amount),
            'method'     => $this->faker->randomElement(RefundMethod::getEnumerators()),
            'payment_id' => $payment,
            'creator_id' => randomOrCreate(app('user')),
            'updater_id' => randomOrCreate(app('user')),
        ];
    }
}
