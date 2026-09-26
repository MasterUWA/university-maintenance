<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Assignment;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_creates_a_session_and_returns_user(): void
    {
        $user = User::factory()->create(['email' => 'api@example.com', 'password' => bcrypt('password')]);

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.email', $user->email);

        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_api_user_can_create_and_filter_requests(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'STUDENT_STAFF', 'guard_name' => 'web']));
        $category = Category::create(['name' => 'Electrical']);

        $this->actingAs($user)->postJson('/api/requests', [
            'title' => 'Broken lamp',
            'description' => 'The lamp is not working.',
            'location' => 'Library',
            'priority' => 'HIGH',
            'category_id' => $category->id,
        ])->assertCreated()->assertJsonPath('data.title', 'Broken lamp');

        $this->actingAs($user)->getJson('/api/requests?search=lamp&limit=5')
            ->assertOk()
            ->assertJsonPath('meta.limit', 5)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_guest_cannot_access_protected_api_requests(): void
    {
        $this->getJson('/api/requests')->assertUnauthorized();
    }

    public function test_request_owner_can_update_and_delete_a_request(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'STUDENT_STAFF', 'guard_name' => 'web']));
        $category = Category::create(['name' => 'Plumbing']);

        $request = $user->requestsMade()->create([
            'title' => 'Leaking tap',
            'description' => 'Water is leaking.',
            'location' => 'Residence hall',
            'priority' => 'MEDIUM',
            'category_id' => $category->id,
        ]);

        $this->actingAs($user)->patchJson('/api/requests/'.$request->id, [
            'priority' => 'URGENT',
        ])->assertOk()->assertJsonPath('data.priority', 'URGENT');

        $this->actingAs($user)->deleteJson('/api/requests/'.$request->id)
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertDatabaseMissing('service_requests', ['id' => $request->id]);
    }

    public function test_officer_can_view_all_requests_but_only_update_assigned_requests(): void
    {
        /** @var User $officer */
        $officer = User::factory()->create();
        $officer->assignRole(Role::create(['name' => 'OFFICER', 'guard_name' => 'web']));
        /** @var User $requester */
        $requester = User::factory()->create();
        $category = Category::create(['name' => 'Furniture']);
        $serviceRequest = ServiceRequest::create([
            'title' => 'Broken desk',
            'description' => 'Desk needs repair.',
            'location' => 'Room 2',
            'priority' => 'LOW',
            'category_id' => $category->id,
            'requester_id' => $requester->id,
        ]);

        $this->actingAs($officer)->getJson('/api/requests')
            ->assertOk()
            ->assertJsonPath('meta.total', 1);

        $this->actingAs($officer)->patchJson('/api/requests/'.$serviceRequest->id.'/status', [
            'status' => 'IN_PROGRESS',
        ])->assertForbidden();

        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole(Role::create(['name' => 'ADMIN', 'guard_name' => 'web']));
        Assignment::create([
            'request_id' => $serviceRequest->id,
            'officer_id' => $officer->id,
            'assigned_by' => $admin->id,
        ]);

        $this->actingAs($officer)->patchJson('/api/requests/'.$serviceRequest->id.'/status', [
            'status' => 'IN_PROGRESS',
            'comment' => 'Started work',
        ])->assertOk();
    }
}