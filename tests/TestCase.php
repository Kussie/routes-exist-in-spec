<?php

namespace Kussie\RoutesExistInSpec\Tests;

use Kussie\RoutesExistInSpec\RoutesExistInSpecServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            RoutesExistInSpecServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
    }
}
