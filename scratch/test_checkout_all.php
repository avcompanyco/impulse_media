<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Plan;

$plan = Plan::find(6);
$users = User::all();

foreach ($users as $user) {
    if (!$user->stripe_id) {
        continue; // Skip users without Stripe ID
    }
    
    echo "Testing user ID {$user->id}: {$user->email} (Stripe ID: {$user->stripe_id})\n";
    try {
        $subscription = $user->newSubscription('default', $plan->stripe_price_id);
        
        $checkout = $subscription->allowPromotionCodes()
            ->checkout([
                'success_url' => 'http://localhost/success',
                'cancel_url' => 'http://localhost/cancel',
                'payment_method_types' => ['card'],
            ]);
        echo "  [SUCCESS] URL: " . substr($checkout->url, 0, 50) . "...\n";
    } catch (\Throwable $e) {
        echo "  [FAILED] ERROR: " . $e->getMessage() . "\n";
    }
}
