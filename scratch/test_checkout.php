<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;

$user = User::where('email', 'jwellingtondye@gmail.com')->first();
if (!$user) {
    echo "User not found!\n";
    exit(1);
}
$plan = Plan::find(6);
if (!$plan) {
    echo "Plan 6 not found!\n";
    exit(1);
}

echo "Testing user: " . $user->email . "\n";
echo "Plan price ID: " . $plan->stripe_price_id . "\n";

try {
    // Auth::login($user);
    
    // Ensure Stripe Customer exists
    if (!$user->hasStripeId()) {
        $user->createAsStripeCustomer();
    }
    
    $subscription = $user->newSubscription('default', $plan->stripe_price_id);
    echo "Subscription builder created successfully\n";
    
    // Now let's try creating a checkout session
    $checkout = $subscription->allowPromotionCodes()
        ->checkout([
            'success_url' => 'http://localhost/success',
            'cancel_url' => 'http://localhost/cancel',
            'payment_method_types' => ['card'],
        ]);
    echo "Checkout Session created successfully: " . $checkout->url . "\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "STACK TRACE:\n" . $e->getTraceAsString() . "\n";
}
