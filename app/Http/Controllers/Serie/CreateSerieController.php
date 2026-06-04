<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\User;
use App\Models\Content;
use App\Models\Serie;
use App\Models\Category;
use App\Enums\Content\ContentType;
use App\Enums\Content\ContentStatus;

class CreateSerieController extends Controller
{
    public function __invoke()
    {
        $_user = User::find(Auth::user()->id);

        // Spectators cannot upload content
        if ($_user->isSpectator()) {
            return redirect()->route('dashboard')->with([
                'type' => 'error',
                'title' => __('Access Denied'),
                'message' => __('Spectator accounts cannot upload content. Switch to a Creator account to start uploading.'),
            ]);
        }

        // get content with movie morph,
        $serie = Serie::where('user_id', $_user->id)
            ->whereHas('content', function ($query) {
                $query->where('type', ContentType::SERIE->value)
                    ->where('status', ContentStatus::DRAFT->value);
            })
            ->first();

        if (!$serie) {
            $serie = Serie::create([
                'title' => '',
                'description' => '',
                'trailer_video' => '',
                'horizontal_image' => '',
                'vertical_image' => '',
                'user_id' => $_user->id,
            ]);
        }

        $content = $serie->content;
        if (!$content) {
            $serie->content()->create([
                'type' => ContentType::SERIE->value,
                'status' => ContentStatus::DRAFT->value,
                'user_id' => $_user->id,
            ]);
        }

        $categories = Category::with('subcategories')->orderBy('name', 'asc')->get();

        $plan = $_user->getCurrentPlan();
        
        if (!$plan) {
            return redirect()->route('user.profile.subscription');
        }

        $canPublish = true;
        $seriesCount = $_user->series()
            ->where('status', ContentStatus::PUBLISHED->value)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
        if (!$plan->is_unlimited_content)
        {
            $canPublish = $plan->series_upload_count > $seriesCount;
        }

        return Inertia::render('user/serie/CreateSerie', [
            'serie' => $serie,
            'categories' => $categories,
            'isCanPublish' => $canPublish,
            'seriesCount' => $seriesCount,
            'plan' => $plan,
        ]);
    }
}
