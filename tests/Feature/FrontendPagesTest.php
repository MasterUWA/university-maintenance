<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FrontendPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_landing_page_is_rendered(): void
    {
        $this->get('/')->assertOk()->assertSee('CampusCare')->assertSee('Keep every campus space moving.');
    }

    public function test_public_about_page_explains_the_service_and_links_to_login(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('About CampusCare')
            ->assertSee('Better campus spaces start with clear communication.')
            ->assertSee(route('login'));
    }

    public function test_authentication_pages_are_rendered(): void
    {
        $this->get('/login')->assertOk()->assertSee('Email');
        $this->get('/register')->assertOk()->assertSee('Confirm Password');
    }

    public function test_authenticated_dashboard_is_rendered(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('Your service desk');
    }

    public function test_admin_can_update_and_delete_service_requests(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole(Role::create(['name' => 'ADMIN', 'guard_name' => 'web']));
        $category = Category::create(['name' => 'Electrical']);
        $serviceRequest = ServiceRequest::create([
            'title' => 'Original title',
            'description' => 'Original description',
            'location' => 'Old location',
            'priority' => 'LOW',
            'status' => 'PENDING',
            'category_id' => $category->id,
            'requester_id' => $admin->id,
        ]);

        $this->actingAs($admin)->patch(route('admin.requests.update', $serviceRequest), [
            'title' => 'Updated title',
            'description' => 'Updated description',
            'location' => 'New location',
            'priority' => 'HIGH',
            'status' => 'IN_PROGRESS',
            'category_id' => $category->id,
        ])->assertRedirect(route('requests.show', $serviceRequest));

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'title' => 'Updated title',
            'status' => 'IN_PROGRESS',
        ]);

        $this->actingAs($admin)->delete(route('admin.requests.destroy', $serviceRequest))
            ->assertRedirect(route('admin.requests'));
        $this->assertDatabaseMissing('service_requests', ['id' => $serviceRequest->id]);
    }
}