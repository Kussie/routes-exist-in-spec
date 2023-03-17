<?php

namespace Kussie\RoutesExistInSpec\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

use function in_array;
use function is_array;

class RoutesExistInSpecCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'route:openapi
                                {yaml? : Path to the OpenAPI Yaml spec file}
                                {--baseline : Generates a baseline file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all routes are defined in the OpenAPI spec file';

    /**
     * @return array|bool
     */
    protected function loadOpenApiContents()
    {
        if ($this->argument('yaml')) {
            $openApiPath = $this->argument('yaml');
        } else {
            $openApiPath = config('openapi.yaml.path');
        }

        if (! File::exists($openApiPath) || ! File::isReadable($openApiPath)) {
            $this->error('openapi.yaml not found');

            return false;
        }

        $openApiContents = file_get_contents($openApiPath);

        if (empty($openApiContents)) {
            $this->error('openapi.yaml is empty');

            return false;
        }

        try {
            return Yaml::parse($openApiContents);
        } catch (ParseException $e) {
            $this->error('openapi.yaml is invalid');

            return false;
        }
    }

    protected function generateAppRouteList(): Collection
    {
        $ignoredRoutes = collect();

        if (file_exists(config('openapi.baseline.path'))) {
            $ignoredRoutes = collect(json_decode(file_get_contents(config('openapi.baseline.path')), true));
        }

        return collect(Route::getRoutes())
            ->filter(fn ($route) => 0 === mb_strpos($route->uri, 'api/') && false === Str::contains($route->uri, '{fallbackPlaceholder}'))
            ->map(fn ($route) => $route->methods()[0] . ' /' . $route->uri)
            ->reject(function ($item) use ($ignoredRoutes) {
                return $ignoredRoutes->contains($item);
            });
    }

    public function handle(): int
    {
        if ($this->option('baseline')) {
            if (file_exists(config('openapi.baseline.path'))) {
                unlink(config('openapi.baseline.path'));
            }

            $routes = $this->generateAppRouteList();

            file_put_contents(config('openapi.baseline.path', base_path('.routespec-baseline.json')), $routes->toJson());

            $this->info('Baseline file generated successfully at ' . config('openapi.baseline.path', base_path('.routespec.baseline')));

            return Command::SUCCESS;
        }

        $this->callSilent('route:clear');

        $openApi = $this->loadOpenApiContents();

        if (! $openApi || ! is_array($openApi)) {
            return Command::FAILURE;
        }

        $routes = $this->generateAppRouteList();

        $oaRoutes = [];

        foreach ($openApi['paths'] as $uri => $oaRoute) {
            foreach (array_keys($oaRoute) as $method) {
                $oaRoutes[] = mb_strtoupper($method) . ' /api' . $uri;
            }
        }

        $nonExistentRoutes = $routes->filter(fn ($route) => ! in_array($route, $oaRoutes));

        if ($nonExistentRoutes->count() > 0) {
            $this->error('The following routes were not found in the openapi.yaml file:');

            foreach ($nonExistentRoutes as $route) {
                $this->error($route);
            }

            return Command::FAILURE;
        }

        $this->info("All API routes accounted for in openapi.yaml.");

        return Command::SUCCESS;
    }
}
