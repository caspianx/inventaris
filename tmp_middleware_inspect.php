<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Foundation\Http\Kernel::class);
$reflector = new ReflectionClass($kernel);
if ($reflector->hasProperty('middleware')) {
    $prop = $reflector->getProperty('middleware');
    $prop->setAccessible(true);
    var_dump($prop->getValue($kernel));
}
if ($reflector->hasProperty('middlewareGroups')) {
    $prop = $reflector->getProperty('middlewareGroups');
    $prop->setAccessible(true);
    var_dump($prop->getValue($kernel));
}
if ($reflector->hasProperty('middlewarePriority')) {
    $prop = $reflector->getProperty('middlewarePriority');
    $prop->setAccessible(true);
    var_dump($prop->getValue($kernel));
}
