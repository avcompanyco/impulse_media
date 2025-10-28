<?php

namespace App\Http\Controllers\Serie;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Serie\HasUploadVerticalImageSerie;
use App\Models\Serie;
use App\Http\Requests\Serie\UploadVerticalImageSerieRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UploadVerticalImageSerieController extends Controller
{
    use HasUploadVerticalImageSerie;

    public function __invoke(UploadVerticalImageSerieRequest $request, Serie $serie)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to upload a vertical image for a serie"));
            }

            $_user = User::find(Auth::user()->id);
            if ($serie->user_id !== $_user->id) {
                throw new \Exception(__("You are not authorized to upload a vertical image for a serie"));
            }

            $this->uploadVerticalImage($serie, $request->validated());

            return inertiaSuccessHandler(
                __("Success"),
                __("Vertical image serie uploaded successfully")
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
