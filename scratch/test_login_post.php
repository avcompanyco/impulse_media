<?php
// Boot Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// Manually log in or authenticate
$request = Illuminate\Http\Request::create('/login-admin', 'POST', [
    'email' => 'admin@example.com',
    'password' => 'password',
]);

// Build LoginAdminRequest from Illuminate Request
$loginRequest = App\Http\Requests\Auth\LoginAdminRequest::createFrom($request);
$loginRequest->setContainer($app);
// Bind it to container so dependencies resolve correctly
$app->instance(App\Http\Requests\Auth\LoginAdminRequest::class, $loginRequest);
$app->instance('request', $loginRequest);

// Set session
$session = $app->make('session')->driver();
$loginRequest->setLaravelSession($session);

// Run the controller manually!
try {
    $controller = new App\Http\Controllers\Auth\Admin\LoginAdminController();
    $response = $controller($loginRequest);
    echo "Status: " . $response->getStatusCode() . PHP_EOL;
    if (method_exists($response, 'getTargetUrl')) {
        echo "Target URL: " . $response->getTargetUrl() . PHP_EOL;
    } else {
        echo "Content: " . substr($response->getContent(), 0, 1000) . PHP_EOL;
    }
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
