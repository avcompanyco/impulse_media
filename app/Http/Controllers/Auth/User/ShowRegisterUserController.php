<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use App\Models\TermsCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class ShowRegisterUserController extends Controller
{
    /**
     * Show the registration page with terms & conditions data.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('auth/RegisterUser', [
            'spectatorTerms' => TermsCondition::getActiveForType('spectator'),
            'creatorTerms' => TermsCondition::getActiveForType('creator'),
        ]);
    }
}
