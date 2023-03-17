<?php

use Illuminate\Console\Command;

it('returns an error when the spec file is not found', function (): void {
    $this->artisan('route:openapi')
        ->expectsOutput('openapi.yaml not found')
        ->assertExitCode(Command::FAILURE);
});

it('returns an error when the spec file is empty', function (): void {
    config()->set('openapi.yaml.path', __DIR__ . '/fixtures/empty-test-spec.yaml');

    $this->artisan('route:openapi')
        ->expectsOutput('openapi.yaml is empty')
        ->assertExitCode(Command::FAILURE);
});

it('returns an error when the spec file is invalid', function (): void {
    config()->set('openapi.yaml.path', __DIR__ . '/fixtures/invalid-test-spec.yaml');

    $this->artisan('route:openapi')
        ->expectsOutput('openapi.yaml is invalid')
        ->assertExitCode(Command::FAILURE);
});

it('returns as successful when all routes are defined', function (): void {
    $router = app()->make('router');
    $router->get('/api/test/', 'TestController@index')->name('test.index');
    config()->set('openapi.yaml.path', __DIR__ . '/fixtures/valid-test-spec.yaml');

    $this->artisan('route:openapi')
        ->expectsOutput('All API routes accounted for in openapi.yaml.')
        ->assertExitCode(Command::SUCCESS);
});

it('returns as error when a route is not documented in the spec', function (): void {
    $router = app()->make('router');
    $router->get('/api/test/', 'TestController@index')->name('test.index');
    $router->get('/api/foobar/', 'TestController@index')->name('test.foobar');
    config()->set('openapi.yaml.path', __DIR__ . '/fixtures/valid-test-spec.yaml');

    $this->artisan('route:openapi')
        ->expectsOutput('The following routes were not found in the openapi.yaml file:')
        ->expectsOutput('GET /api/foobar')
        ->assertExitCode(Command::FAILURE);
});

it('can accept an argument that points to the yaml file', function (): void {
    $router = app()->make('router');
    $router->get('/api/test/', 'TestController@index')->name('test.index');

    $yamlPath = __DIR__ . '/fixtures/valid-test-spec.yaml';
    $this->artisan("route:openapi {$yamlPath}")
        ->expectsOutput('All API routes accounted for in openapi.yaml.')
        ->assertExitCode(Command::SUCCESS);
});

it('can use the baseline file to ignore existing missing route documentation in the spec file', function (): void {
    $baselineFilePath = __DIR__ . '/test-baseline.json';
    config()->set('openapi.baseline.path', $baselineFilePath);
    $router = app()->make('router');
    $router->get('/api/test/', 'TestController@index')->name('test.index');
    $router->get('/api/foobar/', 'TestController@index')->name('test.index');

    $yamlPath = __DIR__ . '/fixtures/valid-test-spec.yaml';
    $this->artisan("route:openapi {$yamlPath}")
        ->expectsOutput('The following routes were not found in the openapi.yaml file:')
        ->expectsOutput('GET /api/foobar')
        ->assertExitCode(Command::FAILURE);

    $this->assertFileDoesNotExist(config('openapi.baseline.path'));
    $this->artisan('route:openapi --baseline')
        ->expectsOutput("Baseline file generated successfully at {$baselineFilePath}")
        ->assertExitCode(Command::SUCCESS);
    $this->assertFileExists(config('openapi.baseline.path'));

    $this->artisan("route:openapi {$yamlPath}")
        ->expectsOutput('All API routes accounted for in openapi.yaml.')
        ->assertExitCode(Command::SUCCESS);

    $router->get('/api/helloworld/', 'TestController@index')->name('test.index');
    $this->artisan("route:openapi {$yamlPath}")
        ->expectsOutput('The following routes were not found in the openapi.yaml file:')
        ->expectsOutput('GET /api/helloworld')
        ->assertExitCode(Command::FAILURE);

});

it('can generate a baseline file', function (): void {
    $baselineFilePath = __DIR__ . '/test-baseline.json';
    config()->set('openapi.baseline.path', $baselineFilePath);
    $router = app()->make('router');
    $router->get('/api/test/', 'TestController@index')->name('test.index');

    $this->assertFileDoesNotExist(config('openapi.baseline.path'));
    $this->artisan('route:openapi --baseline')
        ->expectsOutput("Baseline file generated successfully at {$baselineFilePath}")
        ->assertExitCode(Command::SUCCESS);

    $this->assertFileExists(config('openapi.baseline.path'));
});

afterEach(function (): void {
    $baselineFilePath = __DIR__ . '/test-baseline.json';

    if (file_exists($baselineFilePath)) {
        unlink($baselineFilePath);
    }
});
