<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// STUDENTS API
$routes->resource('students');

// AUTHENTICATION
service('auth')->routes($routes);
