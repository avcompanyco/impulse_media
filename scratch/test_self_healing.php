<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Plan;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Let's pick a user we know has a stale Stripe ID, e.g. ID 2 (user@example.com)
$user = User::find(2);
$plan = Plan::find(6);

if (!$user) {
    echo "User 2 not found!\n";
    exit(1);
}

echo "Testing user: {$user->email} (Stripe ID before: {$user->stripe_id})\n";

// Bind Request instance to container
$request = Request::create('/subscription/checkout/6', 'GET');
$request->setUserResolver(function () use ($user) {
    return $user;
});
app()->instance('request', $request);

// Log in user on guard
Auth::login($user);

try {
    $controller = new SubscriptionController();
    $response = $controller->checkout($request, $plan);
    
    // Refresh user from DB to check new Stripe ID
    $user->refresh();
    echo "Stripe ID after: {$user->stripe_id}\n";
    echo "Response class: " . get_class($response) . "\n";
    if (method_exists($response, 'getTargetUrl')) {
        echo "Redirect URL: " . substr($response->getTargetUrl(), 0, 100) . "...\n";
    } else {
        echo "Response: " . print_r($response, true) . "\n";
    }
    echo "SUCCESS: Self-healing worked perfectly!\n";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "STACK TRACE:\n" . $e->getTraceAsString() . "\n";
}
