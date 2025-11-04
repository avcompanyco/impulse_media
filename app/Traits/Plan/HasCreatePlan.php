<?php

namespace App\Traits\Plan;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Plan;

use Stripe\StripeClient;
use App\Enums\Plan\BillingPeriod;


trait HasCreatePlan
{
    public function create(array $data)
    {
        try {
            DB::beginTransaction();
            $data['is_unlimited_content'] = $data['is_unlimited_content'] ?? false;
            $data['movies_upload_count'] = $data['movies_upload_count'] ?? 0;
            $data['shorts_upload_count'] = $data['shorts_upload_count'] ?? 0;
            $data['series_upload_count'] = $data['series_upload_count'] ?? 0;

            if (env('APP_ENV') == 'production') {
                $stripe = new StripeClient(config('cashier.secret'));

                // Convertir nuestro billing_period a la nomenclatura de Stripe
                $interval = match ($data['billing_period']) {
                    BillingPeriod::DAILY->value => 'day',
                    BillingPeriod::MONTHLY->value => 'month',
                    BillingPeriod::YEARLY->value => 'year',
                    default => 'month',
                };

                // Crear el producto en Stripe
                $product = $stripe->products->create([
                    'name' => $data['name'],
                    'description' => $data['description'],
                ]);

                $price = $stripe->prices->create([
                    'product' => $product->id,
                    'unit_amount' => $data['price'] * 100,
                    'currency' => config('cashier.currency'),
                    'recurring' => [
                        'interval' => $interval,
                    ],
                ]);

                $data['stripe_product_id'] = $product->id;
                $data['stripe_price_id'] = $price->id;
            }

            $data['status'] = 'active';

            $plan = Plan::create($data);

            DB::commit();

            return $plan;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            throw $th;
        }
    }
}
