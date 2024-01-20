<?php

namespace JoshEmbling\CacheMachine\Tests;

use Carbon\Carbon;
use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Cache;
use JoshEmbling\CacheMachine\CacheMachineServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public Repository $cacheRepository;

    public $cacheRepositorySpy;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2024-01-01 00:00:00');

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'JoshEmbling\\CacheMachine\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $this->loadMigrationsFrom(__DIR__.'/migrations');

        $this->cacheRepository = Cache::driver();

        $this->cacheRepositorySpy = Mockery::spy($this->cacheRepository);

        Cache::swap($this->cacheRepositorySpy);
    }

    protected function getPackageProviders($app)
    {
        return [
            CacheMachineServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        //$migration = include __DIR__.'/../database/migrations/create_cache-machine_table.php.stub';
        //$migration->up();

    }
}
