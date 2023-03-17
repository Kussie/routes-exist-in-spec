<?php

return [
    'yaml' => [
        'path' => env('OPENAPI_YAML_PATH', base_path('docs/openapi.yaml')),
    ],
    'baseline' => [
        'path' => env('ROUTESPEC_BASELINE_PATH', base_path('.routespec-baseline.json')),
    ],
];
