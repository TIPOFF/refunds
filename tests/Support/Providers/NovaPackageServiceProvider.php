<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Tests\Support\Providers;

use Tipoff\Refunds\Nova\Refund;
use Tipoff\TestSupport\Providers\BaseNovaPackageServiceProvider;

class NovaPackageServiceProvider extends BaseNovaPackageServiceProvider
{
    public static array $packageResources = [
        Refund::class,
    ];
}
