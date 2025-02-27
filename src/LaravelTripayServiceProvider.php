<?php

namespace HanzoAlpha\LaravelTripay;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use HanzoAlpha\LaravelTripay\Commands\LaravelTripayCommand;

class LaravelTripayServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-tripay')
            ->hasConfigFile('laravel-tripay')
            ->hasMigration('create_laravel_tripay_table')
            ->hasCommand(LaravelTripayCommand::class);
    }
}
