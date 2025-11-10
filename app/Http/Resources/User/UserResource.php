<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'status' => $this->status,
            'image_url' => $this->image_url,
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }),
            'roles_display' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name')->join(', ') ?: __('No roles assigned');
            }),
            'plan' => $this->getCurrentPlan(),
            'plan_id' => $this->plan_id,
            'trial_days' => $this->getTrialDays(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
