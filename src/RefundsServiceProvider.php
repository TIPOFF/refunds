<?php

declare(strict_types=1);

namespace Tipoff\Refunds;

use Tipoff\Refunds\Models\Refund;
use Tipoff\Refunds\Policies\RefundPolicy;
use Tipoff\Support\TipoffPackage;
use Tipoff\Support\TipoffServiceProvider;

class RefundsServiceProvider extends TipoffServiceProvider
{
    public function configureTipoffPackage(TipoffPackage $package): void
    {
        $package
            ->hasPolicies([
                Refund::class => RefundPolicy::class,
            ])
            ->hasNovaResources([
                \Tipoff\Refunds\Nova\Refund::class,
            ])
            ->name('refunds')
            ->hasConfigFile();
    }
}
