<?php

use App\Controllers\Api\AttendanceRecordsController;
use App\Controllers\Api\AuthController;
use App\Controllers\Students;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// STUDENTS API
$routes->resource('students');
$routes->resource('attendancerecords', ['controller' => 'Api\AttendanceRecordsController']);
// $routes->get('api/attendancerecords', [AttendanceRecordsController::class, "index"]);
$routes->patch('api/students/update-rfid/(:num)', [Students::class, "updateRfid/$1"]);

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
