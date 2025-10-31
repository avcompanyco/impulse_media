<?php

namespace App\Http\Controllers\Short;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\User;
use App\Models\Content;
use App\Models\Short;
use App\Models\Category;
use App\Enums\Content\ContentType;
use App\Enums\Content\ContentStatus;

class CreateShortController extends Controller
{
    public function __invoke()
    {
        $_user = User::find(Auth::user()->id);

        // get content with movie morph,
        $short = Short::where('user_id', $_user->id)
            ->whereHas('content', function ($query) {
                $query->where('type', ContentType::SHORT->value)
                    ->where('status', ContentStatus::DRAFT->value);
            })
            ->first();

        if (!$short) {
            $short = Short::create([
                'short_video' => '',
                'text_caption' => '',
                'user_id' => $_user->id,
            ]);
        }

        $content = $short->content;
        if (!$content) {
            $short->content()->create([
                'type' => ContentType::SHORT->value,
                'status' => ContentStatus::DRAFT->value,
                'user_id' => $_user->id,
            ]);
        }

        $short->load('content');

        $plan = $_user->plan;

        if (!$plan) {
            return redirect()->route('user.profile.subscription');
        }

        $canPublish = true;
        $shortsCount = $_user->shorts()->where('status', ContentStatus::PUBLISHED->value)->count();
        if (!$plan->is_unlimited_content)
        {
            $canPublish = $plan->shorts_upload_count > $shortsCount;
        }

        return Inertia::render('user/short/CreateShort', [
            'short' => $short,
            'isCanPublish' => $canPublish,
            'shortsCount' => $shortsCount,
            'plan' => $plan,
        ]);
    }
}
