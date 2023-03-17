<?php

it('returns an error when the route file is not found', function (): void {
    $this->artisan('route:openapi')
        ->expectsOutput('openapi.yaml not found')
        ->assertExitCode(1);
});

it('returns an error when the route file is empty', function (): void {
    config()->set('openapi.yaml.path', __DIR__ . '/fixtures/empty-test-spec.yaml');

    $this->artisan('route:openapi')
        ->expectsOutput('openapi.yaml is empty')
        ->assertExitCode(1);
});

it('returns an error when the route file is invalid', function (): void {
    config()->set('openapi.yaml.path', __DIR__ . '/fixtures/invalid-test-spec.yaml');

    $this->artisan('route:openapi')
        ->expectsOutput('openapi.yaml is invalid')
        ->assertExitCode(1);
});

it('returns as successful when all routes are defined', function (): void {
    $router = app()->make('router');
    $router->get('/api/test/', 'TestController@index')->name('test.index');
    config()->set('openapi.yaml.path', __DIR__ . '/fixtures/valid-test-spec.yaml');

    $this->artisan('route:openapi')
        ->expectsOutput('All 1 API routes accounted for in openapi.yaml.')
        ->assertExitCode(0);
});

it('returns as error when a route is not documented in the sec', function (): void {
    $router = app()->make('router');
    $router->get('/api/test/', 'TestController@index')->name('test.index');
    $router->get('/api/foobar/', 'TestController@index')->name('test.foobar');
    config()->set('openapi.yaml.path', __DIR__ . '/fixtures/valid-test-spec.yaml');

    $this->artisan('route:openapi')
        ->expectsOutput('The following routes were not found in the openapi.yaml file:')
        ->expectsOutput('GET /api/foobar')
        ->assertExitCode(1);
});

it('can accept an argument that point to the yaml file', function (): void {
    $router = app()->make('router');
    $router->get('/api/test/', 'TestController@index')->name('test.index');

    $yamlPath = __DIR__ . '/fixtures/valid-test-spec.yaml';
    $this->artisan("route:openapi {$yamlPath}")
        ->expectsOutput('All 1 API routes accounted for in openapi.yaml.')
        ->assertExitCode(0);
});
