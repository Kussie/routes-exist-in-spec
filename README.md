# routes-exist-in-spec
![Code Standards](https://github.com/Kussie/routes-exist-in-spec/actions/workflows/codestandards.yml/badge.svg) 
![Unit Tests](https://github.com/Kussie/routes-exist-in-spec/actions/workflows/phpunit.yml/badge.svg)

A Laravel package for comparing routes against an OpenAPI spec and ensuring they are all documented in the spec


* `route:openapi` - Compare the applications routes against the OpenAPI docs specified in the configuration and ensure they are all documented.
* `route:openapi {PathToYaml}` - Compare the applications routes against the OpenAPI docs specified in the argument list and ensure they are all documented.
* `route:openapi --baseline` - Generate a baseline file of all the routes in the application. This file can be used to compare against in the future to ensure no new routes are added without being documented in the OpenAPI spec.