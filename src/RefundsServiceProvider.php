<?php

namespace Tipoff\Refunds;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tipoff\Refunds\Models\Refund;
use Tipoff\Refunds\Policies\RefundPolicy;

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
            ->name('refunds')
            ->hasConfigFile()
            ->hasViews();
    }

    public function registeringPackage()
    {
        Gate::policy(Refund::class, RefundPolicy::class);
    }
}
