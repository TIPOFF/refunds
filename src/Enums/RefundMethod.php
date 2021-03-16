<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Enums;

use Tipoff\Support\Enums\BaseEnum;

/**
 * @method static RefundMethod STRIPE()
 * @method static RefundMethod VOUCHER()
 * @psalm-immutable
 */
class RefundMethod extends BaseEnum
{
    const STRIPE = 'Stripe';
    const VOUCHER = 'Voucher';
}
