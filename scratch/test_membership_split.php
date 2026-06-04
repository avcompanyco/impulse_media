<?php

use App\Models\User;
use App\Models\Content;
use App\Models\Movie;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\WatchLog;
use App\Models\Setting;
use App\Models\CreatorEarning;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

function assertTestEquals($expected, $actual, $message) {
    if (abs((float)$expected - (float)$actual) < 0.01) {
        echo "✅ PASS: $message\n";
    } else {
        echo "❌ FAIL: $message. Expected " . var_export($expected, true) . ", got " . var_export($actual, true) . "\n";
        exit(1);
    }
}

echo "=== STARTING MEMBERSHIP SPLIT TEST ===\n";

// 1. Setup global settings
Setting::set('revenue_split_ratio', 60, 'float'); // 60% creator, 40% platform

// 2. Fetch test users
$spectator = User::where('user_type', 'spectator')->first();
$creator1 = User::where('user_type', 'creator')->where('id', '!=', $spectator->id)->first();
$creator2 = User::where('user_type', 'creator')->where('id', '!=', $spectator->id)->where('id', '!=', $creator1->id)->first();

if (!$creator2) {
    // Create second creator if needed
    $creator2 = User::create([
        'name' => 'Second Creator',
        'email' => 'creator2_test_' . time() . '@example.com',
        'username' => 'creator2_test_' . time(),
        'password' => bcrypt('password'),
        'user_type' => \App\Enums\User\UserType::CREATOR,
        'accepted_terms_at' => now(),
    ]);
}

echo "Creator 1 ID: {$creator1->id}, Creator 2 ID: {$creator2->id}\n";

// 3. Create spectator plan if not exists
$plan = Plan::where('plan_type', 'spectator')->first();
if (!$plan) {
    $plan = Plan::create([
        'name' => 'Spectator Premium Plan',
        'description' => 'Unlimited watch access.',
        'price' => 15.00,
        'billing_period' => 'monthly',
        'free_days_trial' => 0,
        'is_unlimited_content' => true,
        'movies_upload_count' => 0,
        'series_upload_count' => 0,
        'shorts_upload_count' => 0,
        'plan_type' => 'spectator',
    ]);
}

// 4. Create completed membership payment for June 2026
$paymentDate = Carbon::create(2026, 6, 15, 12, 0, 0);
$payment = Payment::create([
    'user_id' => $spectator->id,
    'plan_id' => $plan->id,
    'amount' => 100.00, // total pool = $100
    'currency' => 'USD',
    'status' => 'completed',
    'billing_period' => 'monthly',
    'paid_at' => $paymentDate,
]);

// 5. Create contents for both creators
$movie1 = Movie::create([
    'title' => 'Creator 1 Premium Movie',
    'description' => 'Test movie 1',
    'movie_video' => '',
    'trailer_video' => '',
    'horizontal_image' => '',
    'vertical_image' => '',
    'user_id' => $creator1->id,
]);
$content1 = $movie1->content()->create([
    'status' => \App\Enums\Content\ContentStatus::PUBLISHED,
    'type' => \App\Enums\Content\ContentType::MOVIE,
    'user_id' => $creator1->id,
    'allow_membership' => true,
]);

$movie2 = Movie::create([
    'title' => 'Creator 2 Premium Movie',
    'description' => 'Test movie 2',
    'movie_video' => '',
    'trailer_video' => '',
    'horizontal_image' => '',
    'vertical_image' => '',
    'user_id' => $creator2->id,
]);
$content2 = $movie2->content()->create([
    'status' => \App\Enums\Content\ContentStatus::PUBLISHED,
    'type' => \App\Enums\Content\ContentType::MOVIE,
    'user_id' => $creator2->id,
    'allow_membership' => true,
]);

// 6. Log watch time for both contents in June 2026
$watchLogDate = Carbon::create(2026, 6, 20, 15, 0, 0);

$log1 = WatchLog::create([
    'user_id' => $spectator->id,
    'content_id' => $content1->id,
    'duration_seconds' => 30,
]);
$log1->created_at = $watchLogDate;
$log1->save();

$log2 = WatchLog::create([
    'user_id' => $spectator->id,
    'content_id' => $content2->id,
    'duration_seconds' => 10,
]);
$log2->created_at = $watchLogDate;
$log2->save();

// Calculate expected shares based on the actual total watch time in the DB
$totalWatchSeconds = (int) WatchLog::whereYear('created_at', 2026)
    ->whereMonth('created_at', 6)
    ->sum('duration_seconds');

$creatorPoolShare = round((100.0 * 60) / 100, 2); // $60

$expectedCreator1Share = round(($creatorPoolShare * 30) / $totalWatchSeconds, 2);
$expectedCreator2Share = round(($creatorPoolShare * 10) / $totalWatchSeconds, 2);

echo "Total watch seconds counted in June 2026: {$totalWatchSeconds}s\n";
echo "Expected Creator 1 Share: \${$expectedCreator1Share}\n";
echo "Expected Creator 2 Share: \${$expectedCreator2Share}\n";

// 7. Run the console command for June 2026
Artisan::call('impulse:process-membership-split', ['month' => '2026-06']);
$output = Artisan::output();
echo "Command Output:\n" . $output . "\n";

// 8. Verify creator earnings allocation
$earning1 = CreatorEarning::where('creator_id', $creator1->id)
    ->where('source', 'membership_split')
    ->where('description', 'like', '%2026-06%')
    ->first();

$earning2 = CreatorEarning::where('creator_id', $creator2->id)
    ->where('source', 'membership_split')
    ->where('description', 'like', '%2026-06%')
    ->first();

assertTestEquals(true, !empty($earning1), "Creator 1 allocated membership split earning");
assertTestEquals($expectedCreator1Share, (float)$earning1->amount, "Creator 1 share calculated correctly");

assertTestEquals(true, !empty($earning2), "Creator 2 allocated membership split earning");
assertTestEquals($expectedCreator2Share, (float)$earning2->amount, "Creator 2 share calculated correctly");

// 9. Clean up test records
$earning1->delete();
$earning2->delete();
$log1->delete();
$log2->delete();
$content1->delete();
$content2->delete();
$movie1->delete();
$movie2->delete();
$payment->delete();

echo "=== MEMBERSHIP SPLIT TEST COMPLETED SUCCESSFULLY ===\n";
