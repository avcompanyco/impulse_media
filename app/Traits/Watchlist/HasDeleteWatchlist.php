<?php

namespace App\Traits\Watchlist;

use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Support\Facades\Auth;

trait HasDeleteWatchlist
{
    public function delete()
    {
        $_user = User::find(Auth::user()->id);
        $_washlist = Watchlist::where('user_id', $_user->id)->delete();
    }
}
