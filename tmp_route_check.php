<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$router = $app->make('router');
$route = $router->getRoutes()->getByName('install');
var_dump($route);
var_dump($router->getMiddlewareGroups());
