<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Tests\Unit\Services;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Authorization\Models\User;
use Tipoff\Locations\Models\Location;
use Tipoff\Refunds\Exceptions\PaymentRefundException;
use Tipoff\Refunds\Services\RefundGateway\RefundGateway;
use Tipoff\Refunds\Tests\TestCase;

class StripeRefundGatewayTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function charge_no_location_config()
    {
        $user = User::factory()->create();
        $location = Location::factory()->create();

        $service = $this->app->make(RefundGateway::class);

        $this->expectException(PaymentRefundException::class);
        $this->expectExceptionMessage('Stripe not configured for location.');

        $service->refund($location, $user, 123, ['charge_number' => 'abcd']);
    }

    /** @test */
    public function charge_no_stripe_config()
    {
        $user = User::factory()->create();
        $location = Location::factory()->create();

        $service = $this->app->make(RefundGateway::class);

        $this->expectException(PaymentRefundException::class);
        $this->expectExceptionMessage('Stripe not configured for location.');

        $service->refund($location, $user, 123, ['charge_number' => 'abcd']);
    }

    /** @test */
    public function charge_no_charge_number()
    {
        $user = User::factory()->create();
        $location = Location::factory()->create();
        config()->set('payments.stripe_keys', [
            'default' => [
                'publishable' => 'DEF_PUB',
                'secret' => 'DEV_SEC',
            ],
        ]);

        $service = $this->app->make(RefundGateway::class);

        $this->expectException(PaymentRefundException::class);
        $this->expectExceptionMessage('Original payment id method is required.');

        $service->refund($location, $user, 123, []);
    }

    /** @test */
    public function refund_ok()
    {
        $location = Location::factory()->create();
        config()->set('payments.stripe_keys', [
            'default' => [
                'publishable' => 'DEF_PUB',
                'secret' => 'DEV_SEC',
            ],
        ]);

        $service = $this->app->make(RefundGateway::class);

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('refund')
            ->withArgs(function ($chargeNumber, $options) {
                return $chargeNumber === 'abcd' && $options['amount'] === 123;
            })
            ->once()
            ->andReturn(
                new \Stripe\Refund(['id' => 'ok'])
            );

        $service->refund($location, $user, 123, ['charge_number' => 'abcd']);
    }
}
