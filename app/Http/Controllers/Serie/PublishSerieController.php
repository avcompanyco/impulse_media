<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;
use App\Traits\Serie\HasPublishSerie;
use App\Http\Requests\Serie\UpdateSerieRequest;

use App\Models\Serie;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PublishSerieController extends Controller
{
    use HasPublishSerie;

    public function __invoke(UpdateSerieRequest $request, Serie $serie)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to publish a serie"));
            }

            $_user = User::find(Auth::user()->id);
            if ($_user->id !== $serie->user_id) {
                throw new \Exception(__("You are not authorized to publish this serie"));
            }

            $data = $request->validated();
            $this->publish($serie, $data);
            return redirect()->route('user.upload.serie')
                ->with('success', __("Serie published successfully"));

            // return inertiaSuccessHandler(
            //     __("Success"),
            //     __("Serie published successfully")
            // );
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
