<?php

namespace Tipoff\Refunds;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tipoff\Refunds\Commands\RefundsCommand;

class RefundsServiceProvider extends PackageServiceProvider
{
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
            ->hasViews()
            ->hasMigration('create_refunds_table')
            ->hasCommand(RefundsCommand::class);
    }
}
