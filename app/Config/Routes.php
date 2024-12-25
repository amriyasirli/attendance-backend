<?php

use App\Controllers\Api\AuthController;
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

// API Routes
$routes->post("/api/register", [AuthController::class, "register"]);
$routes->post("/api/login", [AuthController::class, "login"]);

// Protected API Routes
$routes->group("api", ["namespace" => "App\Controllers\Api", "filter" => "shield_auth"], function ($routes) {

    $routes->get("profile", [AuthController::class, "profile"]);
    $routes->get("logout", [AuthController::class, "logout"]);
});
