<?php

namespace JoshEmbling\CacheMachine;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use JoshEmbling\CacheMachine\Commands\CacheMachineCommand;

class CacheMachineServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('cache-machine')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_cache-machine_table')
            ->hasCommand(CacheMachineCommand::class);
    }
}
