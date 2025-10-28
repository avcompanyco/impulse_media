<?php

namespace App\Traits\Plan;

use App\Models\Plan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;
use App\Enums\Plan\BillingPeriod;

trait HasUpdatePlan
{
    public function update(Plan $plan, array $data)
    {
        try {
            DB::beginTransaction();
            
            $data['is_unlimited_content'] = $data['is_unlimited_content'] ?? false;
            $data['movies_upload_count'] = $data['movies_upload_count'] ?? 0;
            $data['shorts_upload_count'] = $data['shorts_upload_count'] ?? 0;
            $data['series_upload_count'] = $data['series_upload_count'] ?? 0;

            if (Plan::where('name', $data['name'])->where('id', '!=', $plan->id)->exists()) {
                throw new \Exception(__("Plan already exists"));
            }

            $stripe = new StripeClient(config('cashier.secret'));

            // Actualizar el producto en Stripe si el nombre o descripción cambió
            if (isset($data['name']) || isset($data['description'])) {
                $productData = [];
                if (isset($data['name'])) {
                    $productData['name'] = $data['name'];
                }
                if (isset($data['description'])) {
                    $productData['description'] = $data['description'];
                }
                
                $stripe->products->update($plan->stripe_product_id, $productData);
            }

            // Si cambió el precio o el período de facturación, crear un nuevo precio en Stripe
            if (isset($data['price']) || isset($data['billing_period'])) {
                // Convertir nuestro billing_period a la nomenclatura de Stripe
                $interval = match($data['billing_period'] ?? $plan->billing_period) {
                    BillingPeriod::DAILY->value => 'day',
                    BillingPeriod::MONTHLY->value => 'month',
                    BillingPeriod::YEARLY->value => 'year',
                    default => 'month',
                };

                // Crear un nuevo precio en Stripe
                $price = $stripe->prices->create([
                    'product' => $plan->stripe_product_id,
                    'unit_amount' => ($data['price'] ?? $plan->price) * 100,
                    'currency' => config('cashier.currency'),
                    'recurring' => [
                        'interval' => $interval,
                    ],
                ]);

                // Archivar el precio anterior en Stripe
                $stripe->prices->update($plan->stripe_price_id, [
                    'active' => false,
                ]);

                $data['stripe_price_id'] = $price->id;
            }

            $plan->update($data);
            
            DB::commit();

            return $plan;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            throw $th;
        }
    }
}
