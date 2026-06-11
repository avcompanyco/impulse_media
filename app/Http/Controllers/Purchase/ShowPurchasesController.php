<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Purchase;

class ShowPurchasesController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();

        // Get completed purchases for the user, eager loading content, and morph contentable (Movie, Serie, Short)
        $purchases = Purchase::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with([
                'content.contentable' => function ($query) {
                    // Eager load category and creator (user) for the morph contentable
                    $query->with(['category', 'user']);
                }
            ])
            ->latest()
            ->get();

        // Filter out any purchases where content or contentable is missing
        $purchases = $purchases->filter(function ($purchase) {
            return $purchase->content && $purchase->content->contentable;
        })->values();

        return Inertia::render('user/purchases/Index', [
            'purchases' => $purchases,
        ]);
    }
}
