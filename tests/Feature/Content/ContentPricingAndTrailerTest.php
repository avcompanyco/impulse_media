<?php

namespace Tests\Feature\Content;

use Tests\TestCase;
use App\Models\User;
use App\Models\Movie;
use App\Models\Content;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Setting;
use App\Models\Plan;
use App\Enums\User\UserType;
use App\Enums\Content\ContentType;
use App\Enums\Content\ContentStatus;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentPricingAndTrailerTest extends TestCase
{
    use RefreshDatabase;

    protected Plan $plan;
    protected Category $category;
    protected Subcategory $subcategory;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('min_ppv_price', 0.99, 'float');

        Role::firstOrCreate(['name' => 'creator']);

        $this->plan = Plan::factory()->create([
            'status' => 'active',
            'plan_type' => 'creator',
        ]);

        $this->category = Category::create(['name' => 'Action', 'description' => 'Action movies']);
        $this->subcategory = Subcategory::create(['name' => 'Thriller', 'description' => 'Thriller movies', 'category_id' => $this->category->id]);
    }

    private function createCreator(): User
    {
        $creator = User::factory()->create([
            'user_type' => UserType::CREATOR,
            'plan_id' => $this->plan->id,
        ]);
        $creator->assignRole('creator');
        return $creator;
    }

    public function test_creator_can_update_content_pricing_in_realtime()
    {
        $creator = $this->createCreator();

        $movie = Movie::create([
            'user_id' => $creator->id,
            'title' => 'Test Movie',
            'description' => 'A test movie description.',
            'category_id' => $this->category->id,
            'subcategory_id' => $this->subcategory->id,
            'movie_video' => 'movies/test.mp4',
            'horizontal_image' => 'images/h.jpg',
            'vertical_image' => 'images/v.jpg',
        ]);

        $content = $movie->content()->create([
            'user_id' => $creator->id,
            'title' => $movie->title,
            'type' => ContentType::MOVIE->value,
            'status' => ContentStatus::PUBLISHED->value,
            'ppv_price' => 0.00,
            'allow_membership' => true,
        ]);

        $response = $this->actingAs($creator)
            ->from('/creator/dashboard')
            ->put("/creator/content/{$content->id}/pricing", [
                'ppv_price' => 4.99,
                'allow_membership' => 0,
            ]);

        $response->assertRedirect('/creator/dashboard');
        $response->assertSessionHas('success');

        $content->refresh();
        $this->assertEquals(4.99, (float)$content->ppv_price);
        $this->assertFalse((bool)$content->allow_membership);
    }

    public function test_content_pricing_enforces_minimum_ppv_price()
    {
        $creator = $this->createCreator();

        $movie = Movie::create([
            'user_id' => $creator->id,
            'title' => 'Test Movie 2',
            'description' => 'Description',
            'category_id' => $this->category->id,
            'subcategory_id' => $this->subcategory->id,
            'movie_video' => 'movies/test2.mp4',
            'horizontal_image' => 'images/h.jpg',
            'vertical_image' => 'images/v.jpg',
        ]);

        $content = $movie->content()->create([
            'user_id' => $creator->id,
            'title' => $movie->title,
            'type' => ContentType::MOVIE->value,
            'status' => ContentStatus::PUBLISHED->value,
            'ppv_price' => 0.00,
            'allow_membership' => true,
        ]);

        $response = $this->actingAs($creator)
            ->from('/creator/dashboard')
            ->put("/creator/content/{$content->id}/pricing", [
                'ppv_price' => 0.50,
                'allow_membership' => 1,
            ]);

        $response->assertRedirect('/creator/dashboard');
        $response->assertSessionHasErrors(['ppv_price']);

        $content->refresh();
        $this->assertEquals(0.00, (float)$content->ppv_price);
    }

    public function test_movie_can_be_published_without_trailer()
    {
        $creator = $this->createCreator();

        $movie = Movie::create([
            'user_id' => $creator->id,
            'title' => 'Movie Without Trailer',
            'description' => 'Description for movie without trailer',
            'category_id' => $this->category->id,
            'subcategory_id' => $this->subcategory->id,
            'movie_video' => 'movies/movie123.mp4',
            'horizontal_image' => 'images/horiz.jpg',
            'vertical_image' => 'images/vert.jpg',
            'trailer_video' => null,
        ]);

        $content = $movie->content()->create([
            'user_id' => $creator->id,
            'title' => $movie->title,
            'type' => ContentType::MOVIE->value,
            'status' => ContentStatus::DRAFT->value,
            'ppv_price' => 0.00,
            'allow_membership' => true,
        ]);

        $response = $this->actingAs($creator)
            ->post("/movie/{$movie->id}/publish", [
                'title' => $movie->title,
                'description' => $movie->description,
                'category_id' => $this->category->id,
                'subcategory_id' => $this->subcategory->id,
                'ppv_price' => 0.00,
                'allow_membership' => 1,
            ]);

        $movie->refresh();
        $this->assertNull($movie->trailer_video);

        $content->refresh();
        $this->assertEquals(ContentStatus::PUBLISHED, $content->status);
    }
}
