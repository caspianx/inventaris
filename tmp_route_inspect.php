<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$router = $app->make('router');
$kernel = $app->make(Illuminate\Foundation\Http\Kernel::class);
$middlewareGroups = method_exists($kernel, 'getMiddlewareGroups') ? $kernel->getMiddlewareGroups() : null;
var_dump($middlewareGroups);
if ($middlewareGroups && isset($middlewareGroups['web'])) {
    foreach ($middlewareGroups['web'] as $i => $m) {
        echo "web[$i] = $m\n";
    }
}
