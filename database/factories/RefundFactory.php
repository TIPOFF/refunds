<?php namespace Tipoff\Refunds\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tipoff\Refunds\Enums\RefundMethod;
use Tipoff\Refunds\Models\Refund;

class RefundFactory extends Factory
{
    protected $model = Refund::class;

    public function definition()
    {
        return [
            'amount'     => rand(1000, 2000),
            'method'     => $this->faker->randomElement(RefundMethod::getEnumerators()),
            'payment_id' => randomOrCreate(app('payment')),
            'creator_id' => randomOrCreate(app('user')),
            'updater_id' => randomOrCreate(app('user')),
        ];
    }
}
