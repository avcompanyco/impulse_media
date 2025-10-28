<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Content;
use App\Enums\Content\ContentStatus;

class PublishContentController extends Controller
{

    public function __invoke(Content $content)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to publish this content"));
            }

            $content->update([
                'status' => ContentStatus::PUBLISHED->value
            ]);

            return inertiaSuccessHandler(
                __("Success"),
                __("Content published successfully")
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
        $_user = User::find(Auth::user()->id);
        if ($_user && $_user->hasRole('admin')) {
            return true;
        }
        return false;
    }
}
