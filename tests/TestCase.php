<?php
namespace Rennokki\Schedule\Test;

use Orchestra\Testbench\TestCase as Orchestra;

use Rennokki\Schedule\Test\Models\User;
use Rennokki\Schedule\Models\ScheduleModel;

abstract class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();
        
        $this->resetDatabase();
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->withFactories(__DIR__.'/../database/factories');
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    protected function getPackageProviders($app)
    {
        return [
            \Rennokki\Schedule\ScheduleServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => __DIR__.'/database.sqlite',
            'prefix' => '',
        ]);
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('app.key', 'wslxrEFGWY6GfGhvN9L3wH3KSRJQQpBD');
        $app['config']->set('schedule.model', ScheduleModel::class);
    }

    protected function resetDatabase()
    {
        file_put_contents(__DIR__.'/database.sqlite', null);
    }
}