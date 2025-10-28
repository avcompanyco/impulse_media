<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;
use App\Traits\Serie\HasUpdateSerie;
use App\Http\Requests\Serie\UpdateSerieRequest;

use App\Models\Serie;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UpdateSerieController extends Controller
{
    use HasUpdateSerie;

    public function __invoke(UpdateSerieRequest $request, Serie $serie)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to update a serie"));
            }

            $_user = User::find(Auth::user()->id);
            if ($_user->id !== $serie->user_id) {
                throw new \Exception(__("You are not authorized to update this serie"));
            }

            $data = $request->validated();
            $this->update($serie, $data);

            return inertiaSuccessHandler(
                __("Success"),
                __("Serie updated successfully")
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
