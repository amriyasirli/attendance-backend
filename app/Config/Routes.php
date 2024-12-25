<?php

use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Shield\Entities\AccessToken;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// STUDENTS API
$routes->resource('students');

// AUTHENTICATION
service('auth')->routes($routes);

$routes->get('access/token', static function () {
    $token = auth()->user()->accessTokens();

    dd($token);
});
