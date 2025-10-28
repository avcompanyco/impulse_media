<?php

namespace App\Http\Controllers\Serie;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Serie\HasUploadHorizontalImageSerie;
use App\Models\Serie;
use App\Http\Requests\Serie\UploadHorizontalImageSerieRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UploadHorizontalImageSerieController extends Controller
{
    use HasUploadHorizontalImageSerie;

    public function __invoke(UploadHorizontalImageSerieRequest $request, Serie $serie)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to upload a horizontal image for a serie"));
            }

            $_user = User::find(Auth::user()->id);
            if ($serie->user_id !== $_user->id) {
                throw new \Exception(__("You are not authorized to upload a horizontal image for a serie"));
            }

            $this->uploadHorizontalImage($serie, $request->validated());

            return inertiaSuccessHandler(
                __("Success"),
                __("Horizontal image serie uploaded successfully")
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
