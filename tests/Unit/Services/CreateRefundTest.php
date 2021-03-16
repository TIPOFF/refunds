<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Tests\Unit\Services;

use Assert\LazyAssertionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tipoff\Authorization\Models\User;
use Tipoff\Payments\Models\Payment;
use Tipoff\Refunds\Enums\RefundMethod;
use Tipoff\Refunds\Exceptions\PaymentRefundException;
use Tipoff\Refunds\Exceptions\VouchersNotAvailableException;
use Tipoff\Refunds\Models\Refund;
use Tipoff\Refunds\Notifications\RefundConfirmation;
use Tipoff\Refunds\Services\RefundGateway\RefundGateway;
use Tipoff\Refunds\Tests\TestCase;
use Tipoff\Support\Contracts\Checkout\Vouchers\VoucherInterface;

class CreateRefundTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $service = \Mockery::mock(RefundGateway::class);
        $service->shouldReceive('refund')->andReturn('ok');
        $this->app->instance(RefundGateway::class, $service);
    }

    /** @test */
    public function create_and_issue_full_refund()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        /** @var Payment $payment */
        $payment = Payment::factory()->create([
            'amount' => 1234,
        ]);

        /** @var Refund $refund */
        $refund = Refund::createRefund($payment, 1234, RefundMethod::STRIPE);

        $this->assertNotNull($refund->id);
        $this->assertEquals(1234, $refund->amount);
        $this->assertNull($refund->transaction_number);
        $this->assertNull($refund->issued_at);
        $this->assertNull($refund->issuer_id);

        $refund = $refund->issue();
        $this->assertNotNull($refund->transaction_number);
        $this->assertNotNull($refund->issued_at);
        $this->assertNotNull($refund->issuer_id);

        $payment->refresh();
        $this->assertEquals(1234, $payment->amount_refunded);
        $this->assertEquals(0, $payment->amount_refundable);
    }

    /** @test */
    public function refund_stripe_notification()
    {
        Notification::fake();

        $user = User::factory()->create();
        $this->actingAs($user);

        /** @var Payment $payment */
        $payment = Payment::factory()->create([
            'amount' => 1234,
        ]);

        /** @var Refund $refund */
        $refund = Refund::createRefund($payment, 1234, RefundMethod::STRIPE);
        $refund = $refund->issue();

        Notification::assertNothingSent();

        $refund->notifyUser();

        Notification::assertSentTo(
            [$payment->user],
            RefundConfirmation::class
        );
    }

    /** @test */
    public function refund_voucher_notification()
    {
        Notification::fake();

        $service = \Mockery::mock(VoucherInterface::class);
        $service->shouldReceive('createRefundVoucher')
            ->andReturnSelf();
        $service->shouldReceive('getId')
            ->andReturn(123);
        $this->app->instance(VoucherInterface::class, $service);

        $user = User::factory()->create();
        $this->actingAs($user);

        /** @var Payment $payment */
        $payment = Payment::factory()->create([
            'amount' => 1234,
        ]);

        /** @var Refund $refund */
        $refund = Refund::createRefund($payment, 1234, RefundMethod::VOUCHER);
        $refund = $refund->issue();

        Notification::assertNothingSent();

        $refund->notifyUser();

        Notification::assertSentTo(
            [$payment->user],
            RefundConfirmation::class
        );
    }

    /** @test */
    public function create_and_issue_multiple_refunds()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        /** @var Payment $payment */
        $payment = Payment::factory()->create([
            'amount' => 1234,
        ]);

        /** @var Refund $refund */
        $refund = Refund::createRefund($payment, 123, RefundMethod::STRIPE);
        $refund->issue();

        $refund = Refund::createRefund($payment, 234, RefundMethod::STRIPE);
        $refund->issue();

        $refund = Refund::createRefund($payment, 345, RefundMethod::STRIPE);
        $refund->issue();

        $payment->refresh();
        $this->assertEquals(702, $payment->amount_refunded);
        $this->assertEquals(532, $payment->amount_refundable);

        $this->assertCount(3, $payment->refunds);
    }

    /** @test */
    public function create_and_issue_voucher_refund()
    {
        $service = \Mockery::mock(VoucherInterface::class);
        $service->shouldReceive('createRefundVoucher')
            ->andReturnSelf();
        $service->shouldReceive('getId')
            ->andReturn(123);
        $this->app->instance(VoucherInterface::class, $service);

        $user = User::factory()->create();
        $this->actingAs($user);

        /** @var Payment $payment */
        $payment = Payment::factory()->create([
            'amount' => 1234,
        ]);

        /** @var Refund $refund */
        $refund = Refund::createRefund($payment, 1234, RefundMethod::VOUCHER);
        $refund = $refund->issue();

        $this->assertNull($refund->transaction_number);
        $this->assertNotNull($refund->voucher_id);
        $this->assertNotNull($refund->issued_at);
        $this->assertNotNull($refund->issuer_id);

        $payment->refresh();
        $this->assertEquals(1234, $payment->amount_refunded);
        $this->assertEquals(0, $payment->amount_refundable);
    }

    /** @test */
    public function create_and_issue_voucher_refund_no_service()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        /** @var Payment $payment */
        $payment = Payment::factory()->create([
            'amount' => 1234,
        ]);

        $this->expectException(VouchersNotAvailableException::class);
        $this->expectExceptionMessage('Voucher services are not enabled.');

        /** @var Refund $refund */
        $refund = Refund::createRefund($payment, 1234, RefundMethod::VOUCHER);
        $refund->issue();
    }

    /** @test */
    public function create_excess_refund()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        /** @var Payment $payment */
        $payment = Payment::factory()->create([
            'amount' => 1234,
        ]);

        $this->expectException(LazyAssertionException::class);
        $this->expectExceptionMessage('Please check the payment for the max amount that can be refunded.');

        Refund::createRefund($payment, 2345, RefundMethod::STRIPE);
    }

    /** @test */
    public function issue_excess_refund()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        /** @var Payment $payment */
        $payment = Payment::factory()->create([
            'amount' => 1000,
        ]);

        $refund1 = Refund::createRefund($payment, 500, RefundMethod::STRIPE);
        $refund2 = Refund::createRefund($payment, 501, RefundMethod::STRIPE);

        $refund1 = $refund1->issue();
        $this->assertNotNull($refund1->transaction_number);

        $this->expectException(PaymentRefundException::class);
        $this->expectExceptionMessage('Please check the payment for the max amount that can be refunded.');

        $refund2->refresh();
        $refund2->issue();
    }

    /** @test */
    public function issue_refund_twice()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        /** @var Payment $payment */
        $payment = Payment::factory()->create([
            'amount' => 1000,
        ]);

        $refund = Refund::createRefund($payment, 500, RefundMethod::STRIPE);

        $refund = $refund->issue();
        $this->assertNotNull($refund->transaction_number);

        $this->expectException(PaymentRefundException::class);
        $this->expectExceptionMessage('Refund has already been issued.');

        $refund->refresh();
        $refund->issue();
    }
}
