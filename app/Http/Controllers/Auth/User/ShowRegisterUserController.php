<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class ShowRegisterUserController extends Controller
{
    /**
     * Show the login page.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('auth/RegisterUser');
    }
}
