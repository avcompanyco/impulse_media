<?php

namespace App\Traits\Plan;

use App\Models\Plan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

trait HasDeletePlan
{
    public function delete(Plan $plan)
    {
        try {
            DB::beginTransaction();

            $stripe = new StripeClient(config('cashier.secret'));

            // Archivar el precio en Stripe para que no se puedan crear nuevas suscripciones
            if ($plan->stripe_price_id) {
                $stripe->prices->update($plan->stripe_price_id, [
                    'active' => false,
                ]);
            }

            // Archivar el producto en Stripe
            if ($plan->stripe_product_id) {
                $stripe->products->update($plan->stripe_product_id, [
                    'active' => false,
                ]);
            }

            // Eliminar el plan de la base de datos
            $plan->delete();

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            throw $th;
        }
    }
}
