<?php

namespace Tipoff\Refunds;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tipoff\Refunds\Models\Refund;
use Tipoff\Support\TipoffPackage;
use Tipoff\Support\TipoffServiceProvider;

class RefundsServiceProvider extends PackageServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        parent::boot();
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->hasModelInterfaces([
                RefundInterface::class => Refund::class,
            ])
            ->name('refunds')
            ->hasConfigFile()
            ->hasViews();
    }
}
