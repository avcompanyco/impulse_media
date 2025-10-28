<?php

namespace App\Http\Controllers\Plan;

use App\Http\Controllers\Controller;

use App\Traits\Plan\HasDeletePlan;
use App\Models\Plan;

class DestroyPlanController extends Controller
{
    use HasDeletePlan;

    public function __invoke(Plan $plan)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to delete a plan"));
            }

            $this->delete($plan);

            return inertiaSuccessHandler(
                __("Success"),
                __("Plan deleted successfully")
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
