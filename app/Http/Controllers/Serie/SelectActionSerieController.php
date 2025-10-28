<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class SelectActionSerieController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('user/serie/SelectActionSerie');
    }
}
