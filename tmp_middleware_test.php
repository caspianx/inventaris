<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$request = Illuminate\Http\Request::create('/install/setup', 'POST', ['_token' => 'test']);
$middleware = new App\Http\Middleware\HandleInstallationPhase();
$response = $middleware->handle($request, function ($request) {
    return 'next-called';
});
var_dump($response);
var_dump(app()->resolved('session.store'));
var_dump(app()->resolved('session'));
var_dump(app()->resolved(Illuminate\Session\SessionManager::class));
var_dump(app()->resolved(Illuminate\Session\Middleware\StartSession::class));
