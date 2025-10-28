<?php

namespace App\Traits\User;

use Illuminate\Support\Facades\DB;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Movie;
use App\Models\Serie;
use App\Models\Short;
use App\Models\Binacle;

trait HasDeleteUser
{
    public function delete(User $user)
    {
        // Prevent admin from deleting themselves
        if ($user->id === Auth::id()) {
            throw new \Exception(__("You cannot delete your own account"));
        }

        if ($user->hasRole('admin')) {
            throw new \Exception(__("You cannot delete an admin account"));
        }


        try {
            $user->binacles()->delete();

            // Delete all movies
            foreach ($user->movies as $movie) {
                if ($movie instanceof Movie) {
                    $movie->deleteAll();
                    $movie->delete();
                }
            }

            // Delete all series
            foreach ($user->series as $serie) {
                if ($serie instanceof Serie) {
                    $serie->deleteAll();
                    $serie->delete();
                }
            }

            // Delete all shorts
            foreach ($user->shorts as $short) {
                if ($short instanceof Short) {
                    $short->deleteAll();
                    $short->delete();
                }
            }

            // Delete user image if exists
            $user->deleteImage();
            
            $user->followers()->delete();

            // Remove all roles
            $user->syncRoles([]);

            $user->delete();
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
