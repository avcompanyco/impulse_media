<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Http\Resources\Content\ContentCollection;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Enums\Content\ContentStatus;
use App\Enums\Content\ContentType;
use App\Models\Short;
use App\Models\Movie;
use App\Models\Serie;

class DatatableContentController extends Controller
{
    public function __invoke(Request $request)
    {
        try {

            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to access this resource"));
            }

            $contents = Content::query()->with(['user', 'contentable'])
                ->whereNot('status', ContentStatus::DRAFT)->orderBy('id', 'desc');

            $page = $request->query('page', 1);
            $perPage = $request->query('perPage', 10);
            $search = $request->query('search', '');

            if ($search) {
                $contents->whereHasMorph('contentable', [Short::class, Movie::class, Serie::class], function ($query, $type) use ($search) {
                    if ($type == Short::class) {
                        $query->where('text_caption', 'like', '%' . $search . '%');
                    } else if ($type == Movie::class) {
                        $query->where('title', 'like', '%' . $search . '%');
                    } else if ($type == Serie::class) {
                        $query->where('title', 'like', '%' . $search . '%');
                    }
                });

                $contents->orWhereHas('user', function ($query) use ($search) {
                    $query->where('username', 'like', '%' . $search . '%');
                });
            }

            return new ContentCollection($contents->paginate($perPage, ['*'], 'page', $page));
        } catch (\Throwable $th) {
            return response()->json([]);
        }
    }


    public function canAccess()
    {
        $_user = User::find(Auth::user()->id);
        if ($_user && $_user->hasRole('admin')) {
            return true;
        }
        return false;
    }
}
