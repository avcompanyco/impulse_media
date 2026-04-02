<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\TermsCondition;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SpectatorSetupSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. Spatie Roles ───
        Role::firstOrCreate(['name' => 'spectator', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'creator', 'guard_name' => 'web']);
        $this->command->info('✓ Roles created: spectator, creator');

        // ─── 2. Mark existing plans as "creator" type ───
        Plan::query()->update(['plan_type' => 'creator', 'has_ads' => false]);
        $this->command->info('✓ Existing plans marked as creator type');

        // ─── 3. Spectator Plans ───
        $planA = Plan::firstOrCreate(
            ['name' => 'Standard'],
            [
                'description' => 'Stream movies, series and shorts with ads',
                'price' => 10.00,
                'billing_period' => 'monthly',
                'free_days_trial' => 0,
                'is_unlimited_content' => false,
                'movies_upload_count' => 0,
                'series_upload_count' => 0,
                'shorts_upload_count' => 0,
                'status' => 'active',
                'plan_type' => 'spectator',
                'has_ads' => true,
                'stripe_product_id' => '',
                'stripe_price_id' => '',
            ]
        );

        $planB = Plan::firstOrCreate(
            ['name' => 'Premium'],
            [
                'description' => 'Stream movies, series and shorts without ads',
                'price' => 15.00,
                'billing_period' => 'monthly',
                'free_days_trial' => 0,
                'is_unlimited_content' => false,
                'movies_upload_count' => 0,
                'series_upload_count' => 0,
                'shorts_upload_count' => 0,
                'status' => 'active',
                'plan_type' => 'spectator',
                'has_ads' => false,
                'stripe_product_id' => '',
                'stripe_price_id' => '',
            ]
        );

        // Create Stripe products/prices if keys are configured
        if (config('cashier.secret')) {
            $stripe = new \Stripe\StripeClient(config('cashier.secret'));

            try {
                // Plan A - Standard
                if (empty($planA->stripe_product_id)) {
                    $product = $stripe->products->create([
                        'name' => 'Standard (Spectator)',
                        'description' => 'Stream movies, series and shorts with ads — $10/mo',
                    ]);
                    $price = $stripe->prices->create([
                        'product' => $product->id,
                        'unit_amount' => 1000, // $10.00 in cents
                        'currency' => config('cashier.currency', 'usd'),
                        'recurring' => ['interval' => 'month'],
                    ]);
                    $planA->update([
                        'stripe_product_id' => $product->id,
                        'stripe_price_id' => $price->id,
                    ]);
                    $this->command->info("✓ Stripe: Standard plan created (product: {$product->id})");
                }

                // Plan B - Premium
                if (empty($planB->stripe_product_id)) {
                    $product = $stripe->products->create([
                        'name' => 'Premium (Spectator)',
                        'description' => 'Stream movies, series and shorts without ads — $15/mo',
                    ]);
                    $price = $stripe->prices->create([
                        'product' => $product->id,
                        'unit_amount' => 1500, // $15.00 in cents
                        'currency' => config('cashier.currency', 'usd'),
                        'recurring' => ['interval' => 'month'],
                    ]);
                    $planB->update([
                        'stripe_product_id' => $product->id,
                        'stripe_price_id' => $price->id,
                    ]);
                    $this->command->info("✓ Stripe: Premium plan created (product: {$product->id})");
                }
            } catch (\Exception $e) {
                $this->command->warn("⚠ Stripe error: {$e->getMessage()}. Plans created locally without Stripe IDs.");
            }
        } else {
            $this->command->warn("⚠ Stripe keys not found — plans created without Stripe IDs.");
        }

        // ─── 4. Terms & Conditions ───
        TermsCondition::firstOrCreate(
            ['type' => 'spectator', 'is_active' => true],
            [
                'title' => 'Spectator Terms & Conditions',
                'version' => '1.0',
                'content' => $this->getSpectatorTerms(),
            ]
        );

        TermsCondition::firstOrCreate(
            ['type' => 'creator', 'is_active' => true],
            [
                'title' => 'Creator Terms & Conditions',
                'version' => '1.0',
                'content' => $this->getCreatorTerms(),
            ]
        );

        $this->command->info('✓ Terms & Conditions created for spectator and creator');
    }

    private function getSpectatorTerms(): string
    {
        return <<<'TERMS'
<h2>Impulse Media — Spectator Terms & Conditions</h2>
<p><strong>Last Updated:</strong> March 2026</p>

<h3>1. Acceptance of Terms</h3>
<p>By creating a Spectator account on Impulse Media, you agree to abide by these Terms & Conditions. If you do not agree, please do not create an account.</p>

<h3>2. Account Registration</h3>
<p>You must provide accurate and complete information during registration. You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.</p>

<h3>3. Subscription Plans</h3>
<p>Spectator accounts require a paid subscription to access movies, series, and unlimited shorts. Two plans are available:</p>
<ul>
    <li><strong>Standard Plan ($10/month):</strong> Full access to all content with advertisements displayed during playback.</li>
    <li><strong>Premium Plan ($15/month):</strong> Full access to all content without any advertisements.</li>
</ul>
<p>Subscriptions are billed monthly and will auto-renew unless cancelled before the end of the billing period.</p>

<h3>4. Content Usage</h3>
<p>All content on Impulse Media is provided for personal, non-commercial viewing only. You may not download, record, reproduce, distribute, or publicly display any content without prior written consent from Impulse Media and the respective content creators.</p>

<h3>5. Advertisements</h3>
<p>If you are subscribed to the Standard Plan, you agree to view advertisements during content playback. Advertisements are delivered by third-party advertising networks and Impulse Media does not guarantee the content of such advertisements.</p>

<h3>6. User Conduct</h3>
<p>You agree not to:</p>
<ul>
    <li>Use the platform for any unlawful purpose.</li>
    <li>Attempt to circumvent any access control, security, or advertising mechanisms.</li>
    <li>Harass, abuse, or harm other users or content creators.</li>
    <li>Share your account credentials with third parties.</li>
</ul>

<h3>7. Payment & Refunds</h3>
<p>Payments are processed securely through Stripe. By subscribing, you authorize Impulse Media to charge your payment method on a recurring basis. Refunds are subject to our refund policy and may be granted on a case-by-case basis.</p>

<h3>8. Termination</h3>
<p>Impulse Media reserves the right to suspend or terminate your account at any time for violation of these terms, without prior notice or liability.</p>

<h3>9. Changes to Terms</h3>
<p>Impulse Media may update these terms from time to time. Continued use of the platform after changes constitutes acceptance of the modified terms.</p>

<h3>10. Limitation of Liability</h3>
<p>Impulse Media is provided "as is" without warranties of any kind. In no event shall Impulse Media be liable for any indirect, incidental, special, or consequential damages arising from your use of the platform.</p>

<p><strong>By creating your account, you acknowledge that you have read, understood, and agree to be bound by these Terms & Conditions.</strong></p>
TERMS;
    }

    private function getCreatorTerms(): string
    {
        return <<<'TERMS'
<h2>Impulse Media — Creator Terms & Conditions</h2>
<p><strong>Last Updated:</strong> March 2026</p>

<h3>1. Acceptance of Terms</h3>
<p>By creating a Creator account on Impulse Media, you agree to abide by these Terms & Conditions. If you do not agree, please do not create an account.</p>

<h3>2. Account Registration</h3>
<p>You must provide accurate and complete information during registration. You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.</p>

<h3>3. Creator Plans & Upload Limits</h3>
<p>Creator accounts have different subscription tiers with varying upload limits:</p>
<ul>
    <li><strong>Free Plan:</strong> Limited uploads (1 movie, 1 series, 3 shorts).</li>
    <li><strong>Silver Plan ($25/month):</strong> Increased upload limits (5 movies, 2 series, 20 shorts).</li>
    <li><strong>Golden Plan ($50/month):</strong> Unlimited content uploads.</li>
</ul>
<p>When you reach the upload limit of your current plan, you will be prompted to upgrade to a higher tier to continue uploading content.</p>

<h3>4. Content Ownership & Rights</h3>
<p>You retain full ownership of all content you upload to Impulse Media. By uploading content, you grant Impulse Media a non-exclusive, worldwide, royalty-free license to host, store, distribute, display, and stream your content on the platform.</p>
<p>You represent and warrant that you have all necessary rights, licenses, and permissions to upload and distribute the content, and that your content does not infringe on the intellectual property rights of any third party.</p>

<h3>5. Revenue Sharing</h3>
<p>Creators may earn revenue based on the viewing time generated by their content. Revenue calculations are made monthly, and payments are processed according to the platform's payment schedule. Impulse Media reserves the right to modify the revenue-sharing model with prior notice.</p>

<h3>6. Content Guidelines</h3>
<p>You agree that your content will not:</p>
<ul>
    <li>Contain illegal, harmful, threatening, or defamatory material.</li>
    <li>Infringe on the copyrights, trademarks, or other intellectual property rights of others.</li>
    <li>Contain viruses, malware, or any other malicious code.</li>
    <li>Promote violence, discrimination, or illegal activities.</li>
</ul>
<p>Impulse Media reserves the right to remove any content that violates these guidelines without prior notice.</p>

<h3>7. Payment & Billing</h3>
<p>Plan subscriptions are billed monthly through Stripe. Creator revenue payments are disbursed monthly. Impulse Media will make reasonable efforts to process payments in a timely manner but is not responsible for delays caused by third-party payment processors.</p>

<h3>8. Termination</h3>
<p>Impulse Media reserves the right to suspend or terminate your account at any time for violation of these terms. Upon termination, your content may remain on the platform for a grace period of 30 days, after which it will be permanently removed.</p>

<h3>9. Changes to Terms</h3>
<p>Impulse Media may update these terms from time to time. Continued use of the platform after changes constitutes acceptance of the modified terms.</p>

<h3>10. Limitation of Liability</h3>
<p>Impulse Media is provided "as is" without warranties of any kind. In no event shall Impulse Media be liable for any indirect, incidental, special, or consequential damages arising from your use of the platform.</p>

<p><strong>By creating your account, you acknowledge that you have read, understood, and agree to be bound by these Terms & Conditions.</strong></p>
TERMS;
    }
}
