<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stripe\StripeClient;

use App\Enums\Plan\BillingPeriod;
use App\Models\Plan;

class SyncPlans extends Command
{
    protected $signature = 'stripe:sync-plans';
    protected $description = 'Sincroniza planes con Stripe';

    public function handle()
    {
        $stripe = new StripeClient(config('cashier.secret'));
        $plans = Plan::all();

        foreach ($plans as $plan) {
            // Crear producto en Stripe
            $product = $stripe->products->create([
                'name' => $plan->name,
                'description' => $plan->description,
            ]);
            
            $interval = match ($plan->billing_period) {
                BillingPeriod::DAILY->value => 'day',
                BillingPeriod::MONTHLY->value => 'month',
                BillingPeriod::YEARLY->value => 'year',
                default => 'month',
            };

            // Crear precio en Stripe
            $price = $stripe->prices->create([
                'product' => $product->id,
                'unit_amount' => $plan->price * 100, // Convertir a centavos
                'currency' => 'usd',
                'recurring' => [
                    'interval' => $interval,
                ],
            ]);

            // Guardar IDs en BD
            $plan->update([
                'stripe_product_id' => $product->id,
                'stripe_price_id' => $price->id,
            ]);
        }

        $this->info('Planes sincronizados con Stripe.');
    }
}
