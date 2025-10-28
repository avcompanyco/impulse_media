<?php

namespace App\Http\Controllers\Plan;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Requests\Plan\UpdatePlanRequest;
use App\Traits\Plan\HasUpdatePlan;
use App\Models\Plan;

class UpdatePlanController extends Controller
{
    use HasUpdatePlan;

    public function __invoke(UpdatePlanRequest $request, Plan $plan)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to update a plan"));
            }

            $this->update($plan, $request->validated());

            return inertiaSuccessHandler(
                __("Success"),
                __("Plan updated successfully")
            );
        } catch (\Throwable $th) {
            return inertiaErrorHandler(
                __("Error"),
                $th->getMessage()
            );
        }
    }

    public function canAccess()
    {
        return true;
    }
}
