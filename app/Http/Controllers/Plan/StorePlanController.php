<?php

namespace App\Http\Controllers\Plan;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Requests\Plan\StorePlanRequest;
use App\Traits\Plan\HasCreatePlan;

class StorePlanController extends Controller
{
    use HasCreatePlan;

    public function __invoke(StorePlanRequest $request)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to create a plan"));
            }

            $this->create($request->validated());

            return inertiaSuccessHandler(
                __("Success"),
                __("Plan created successfully")
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
