<?php

namespace Tests\Feature\Terms;

use Tests\TestCase;
use App\Models\User;
use App\Models\TermsCondition;
use App\Enums\User\UserType;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TermsAndConditionsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'creator']);
        Role::firstOrCreate(['name' => 'spectator']);
    }

    public function test_admin_can_view_terms_management_page()
    {
        $admin = User::factory()->create(['user_type' => UserType::ADMIN]);
        $admin->assignRole('admin');

        TermsCondition::create([
            'type' => 'spectator',
            'title' => 'Spectator Terms',
            'content' => '<p>Initial spectator terms</p>',
            'version' => '1.0',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/terms');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('admin/terms/IndexTerms')->has('terms'));
    }

    public function test_admin_can_update_terms_and_conditions()
    {
        $admin = User::factory()->create(['user_type' => UserType::ADMIN]);
        $admin->assignRole('admin');

        $term = TermsCondition::create([
            'type' => 'creator',
            'title' => 'Original Creator Terms',
            'content' => '<p>Original creator content</p>',
            'version' => '1.0',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->from('/admin/terms')
            ->put("/admin/terms/{$term->id}", [
                'title' => 'Updated Creator Terms v2.0',
                'content' => '<p>Updated creator monetization policy content</p>',
                'version' => '2.0',
            ]);

        $response->assertRedirect('/admin/terms');

        $term->refresh();
        $this->assertEquals('Updated Creator Terms v2.0', $term->title);
        $this->assertEquals('<p>Updated creator monetization policy content</p>', $term->content);
        $this->assertEquals('2.0', $term->version);
    }

    public function test_registration_page_receives_active_terms()
    {
        TermsCondition::create([
            'type' => 'spectator',
            'title' => 'Active Spectator Terms',
            'content' => '<p>Spectator content for registration</p>',
            'version' => '1.1',
            'is_active' => true,
        ]);

        TermsCondition::create([
            'type' => 'creator',
            'title' => 'Active Creator Terms',
            'content' => '<p>Creator content for registration</p>',
            'version' => '1.2',
            'is_active' => true,
        ]);

        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('auth/RegisterUser')
            ->has('spectatorTerms')
            ->has('creatorTerms')
        );
    }

    public function test_non_admin_cannot_update_terms()
    {
        $spectator = User::factory()->create(['user_type' => UserType::SPECTATOR]);
        $spectator->assignRole('spectator');

        $term = TermsCondition::create([
            'type' => 'spectator',
            'title' => 'Spectator Terms',
            'content' => '<p>Content</p>',
            'version' => '1.0',
            'is_active' => true,
        ]);

        $response = $this->actingAs($spectator)
            ->put("/admin/terms/{$term->id}", [
                'title' => 'Hacked Title',
                'content' => '<p>Hacked content</p>',
                'version' => '9.9',
            ]);

        // Expect redirect to admin dashboard or home because IsAdmin middleware redirects
        $this->assertDatabaseHas('terms_conditions', [
            'id' => $term->id,
            'title' => 'Spectator Terms',
        ]);
    }
}
