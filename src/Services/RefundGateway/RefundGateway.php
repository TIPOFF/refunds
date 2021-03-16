<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Services\RefundGateway;

use Tipoff\Locations\Models\Location;
use Tipoff\Payments\Enums\Gateway;
use Tipoff\Support\Contracts\Payment\ChargeableInterface;
use Tipoff\Support\Contracts\Services\BaseService;

interface RefundGateway extends BaseService
{
    public function getGatewayType(): Gateway;

    public function refund(Location $location, ChargeableInterface $user, int $amount, array $options = []): string;
}
