<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'amount_formatted' => $this->amount_formatted,
            'currency' => $this->currency,
            'status' => $this->status,
            'billing_period' => $this->billing_period,
            'payment_method' => $this->payment_method,
            'stripe_payment_intent_id' => $this->stripe_payment_intent_id,
            'stripe_subscription_id' => $this->stripe_subscription_id,
            'stripe_invoice_id' => $this->stripe_invoice_id,
            'paid_at' => $this->paid_at?->format('Y-m-d H:i:s'),
            'failed_at' => $this->failed_at?->format('Y-m-d H:i:s'),
            'refunded_at' => $this->refunded_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'plan' => $this->whenLoaded('plan', function () {
                return [
                    'id' => $this->plan->id,
                    'name' => $this->plan->name,
                    'price' => $this->plan->price,
                    'billing_period' => $this->plan->billing_period,
                ];
            }),
        ];
    }
}