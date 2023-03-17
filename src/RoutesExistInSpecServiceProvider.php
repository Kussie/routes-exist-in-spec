<?php

namespace Kussie\RoutesExistInSpec;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Kussie\RoutesExistInSpec\Commands\RoutesExistInSpecCommand;

class RoutesExistInSpecServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        AboutCommand::add('RoutesExistInSpec', fn () => ['Version' => '1.0.0']);

        if (App::runningInConsole()) {
            $this->publishConfig();
            $this->commands([
                RoutesExistInSpecCommand::class,
            ]);
        }
    }

    protected function getConfigPath(): string
    {
        return config_path('openapi.php');
    }

    protected function publishConfig(): void
    {
        $configPath = __DIR__ . '/../config/openapi.php';
        $this->publishes([$configPath => $this->getConfigPath()], 'config');
    }
}
