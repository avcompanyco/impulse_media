<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Serie;
use App\Models\SeasonSerie;
use App\Models\ChapterSerie;
use App\Models\Short;
use App\Models\Content;
use App\Enums\Content\ContentType;
use App\Enums\Content\ContentStatus;
use App\Enums\Content\ChapterStatus;

class TestContentSeeder extends Seeder
{
    public function run(): void
    {
        // Get a user to assign content to
        $user = User::where('email', 'user@example.com')->first();
        if (!$user) {
            return;
        }

        // Get or create a category
        $category = Category::first();
        if (!$category) {
            return;
        }

        $subcategory = $category->subcategories()->first();

        // Create a test serie with seasons and chapters
        $serie = Serie::create([
            'title' => 'Test Adventure Series',
            'description' => 'An epic adventure series with multiple seasons and exciting episodes.',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'subcategory_id' => $subcategory?->id,
            'vertical_image' => 'series/vertical/test-serie-vertical.jpg',
            'horizontal_image' => 'series/horizontal/test-serie-horizontal.jpg',
            'trailer_video' => 'series/trailers/test-serie-trailer.mp4',
        ]);

        // Create content entry for the serie
        Content::create([
            'contentable_type' => Serie::class,
            'contentable_id' => $serie->id,
            'type' => ContentType::SERIE->value,
            'status' => ContentStatus::PUBLISHED->value,
            'user_id' => $user->id,
        ]);

        // Create seasons and chapters
        for ($seasonNumber = 1; $seasonNumber <= 2; $seasonNumber++) {
            $season = SeasonSerie::create([
                'serie_id' => $serie->id,
                'user_id' => $user->id,
            ]);

            // Create chapters for each season
            for ($chapterNumber = 1; $chapterNumber <= 3; $chapterNumber++) {
                ChapterSerie::create([
                    'chapter_number' => $chapterNumber,
                    'title' => "Episode {$chapterNumber}: The " . ['Beginning', 'Journey', 'Discovery'][$chapterNumber - 1],
                    'thumbnail' => "chapters/thumbnails/s{$seasonNumber}e{$chapterNumber}-thumb.jpg",
                    'chapter_video' => "chapters/videos/s{$seasonNumber}e{$chapterNumber}-video.mp4",
                    'status' => ChapterStatus::PUBLISHED,
                    'season_id' => $season->id,
                    'user_id' => $user->id,
                ]);
            }
        }

        // Create some test shorts
        for ($i = 1; $i <= 5; $i++) {
            $short = Short::create([
                'short_video' => "shorts/test-short-{$i}.mp4",
                'text_caption' => "This is test short number {$i}. Swipe up/down to navigate between shorts!",
                'user_id' => $user->id,
            ]);

            // Create content entry for the short
            Content::create([
                'contentable_type' => Short::class,
                'contentable_id' => $short->id,
                'type' => ContentType::SHORT->value,
                'status' => ContentStatus::PUBLISHED->value,
                'user_id' => $user->id,
            ]);
        }
    }
}