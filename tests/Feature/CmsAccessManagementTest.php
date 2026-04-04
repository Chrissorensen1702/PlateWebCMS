<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsAccessManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owners_can_view_the_access_management_page(): void
    {
        $owner = User::factory()->create([
            'name' => 'Mia Jensen',
        ]);

        $viewer = User::factory()->create([
            'name' => 'Jonas Nielsen',
            'email' => 'viewer@example.com',
        ]);

        $tenant = $this->tenantForUser($owner, 'north-studio', 'North Studio', 'owner');
        $tenant->users()->attach($viewer->id, ['role' => 'viewer']);

        $response = $this->actingAs($owner)->get('/cms/access');

        $response->assertOk();
        $response->assertSee('North Studio');
        $response->assertSee('Jonas Nielsen');
    }

    public function test_full_access_developers_can_manage_other_developer_accounts(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
            'developer_access' => User::DEVELOPER_ACCESS_FULL,
        ]);

        $this->actingAs($developer)
            ->get('/cms/access')
            ->assertOk()
            ->assertSee('Platform adgang')
            ->assertSee('Developer-konti og adgangsniveauer');

        $response = $this->actingAs($developer)->post('/cms/access/developers', [
            'name' => 'Emma Developer',
            'email' => 'emma.developer@example.com',
            'password' => 'password123',
            'developer_access' => User::DEVELOPER_ACCESS_CUSTOMER_MANAGER,
            'employment_role' => 'Websupport',
        ]);

        $response->assertRedirect('/cms/access');

        $this->assertDatabaseHas('users', [
            'email' => 'emma.developer@example.com',
            'role' => 'developer',
            'developer_access' => User::DEVELOPER_ACCESS_CUSTOMER_MANAGER,
            'employment_role' => 'Websupport',
        ]);

        $this->actingAs($developer)
            ->get('/cms/access')
            ->assertOk()
            ->assertSee('Emma Developer - Websupport')
            ->assertSee('Ansættelsesrolle: Websupport');
    }

    public function test_customer_manager_developers_can_view_access_page_but_cannot_manage_developer_accounts(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
            'developer_access' => User::DEVELOPER_ACCESS_CUSTOMER_MANAGER,
        ]);

        $this->actingAs($developer)
            ->get('/cms/access')
            ->assertOk()
            ->assertSee('Tenants')
            ->assertSee('Søg kunde');

        $this->actingAs($developer)
            ->post('/cms/access/developers', [
                'name' => 'Blocked Dev',
                'email' => 'blocked.dev@example.com',
                'password' => 'password123',
                'developer_access' => User::DEVELOPER_ACCESS_READ_ONLY,
            ])
            ->assertForbidden();
    }

    public function test_read_only_developers_cannot_access_access_management(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
            'developer_access' => User::DEVELOPER_ACCESS_READ_ONLY,
        ]);

        $this->actingAs($developer)
            ->get('/cms/access')
            ->assertForbidden();
    }

    public function test_owners_can_create_new_sub_accounts_for_their_tenant(): void
    {
        $owner = User::factory()->create();
        $tenant = $this->tenantForUser($owner, 'owner-tenant', 'Owner Tenant', 'owner');

        $response = $this->actingAs($owner)->post("/cms/access/tenants/{$tenant->id}/users", [
            'form_target' => "tenant-{$tenant->id}",
            'name' => 'Emma Larsen',
            'email' => 'emma@example.com',
            'password' => 'password123',
            'tenant_role' => 'editor',
        ]);

        $response->assertRedirect('/cms/access');

        $member = User::query()->firstWhere('email', 'emma@example.com');

        $this->assertNotNull($member);
        $this->assertSame('client', $member->role);
        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $tenant->id,
            'user_id' => $member->id,
            'role' => 'editor',
        ]);
    }

    public function test_owners_can_attach_existing_accounts_to_their_tenant(): void
    {
        $owner = User::factory()->create();
        $existingMember = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $tenant = $this->tenantForUser($owner, 'attach-tenant', 'Attach Tenant', 'owner');

        $response = $this->actingAs($owner)->post("/cms/access/tenants/{$tenant->id}/users", [
            'form_target' => "tenant-{$tenant->id}",
            'name' => '',
            'email' => 'existing@example.com',
            'password' => '',
            'tenant_role' => 'viewer',
        ]);

        $response->assertRedirect('/cms/access');

        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $tenant->id,
            'user_id' => $existingMember->id,
            'role' => 'viewer',
        ]);
    }

    public function test_viewers_cannot_access_or_manage_tenant_access(): void
    {
        $viewer = User::factory()->create();
        $tenant = $this->tenantForUser($viewer, 'viewer-tenant', 'Viewer Tenant', 'viewer');

        $this->actingAs($viewer)
            ->get('/cms/access')
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post("/cms/access/tenants/{$tenant->id}/users", [
                'form_target' => "tenant-{$tenant->id}",
                'name' => 'No Access',
                'email' => 'no-access@example.com',
                'password' => 'password123',
                'tenant_role' => 'viewer',
            ])
            ->assertForbidden();
    }

    public function test_owners_cannot_manage_other_tenants_access(): void
    {
        $owner = User::factory()->create();
        $ownedTenant = $this->tenantForUser($owner, 'owned-tenant', 'Owned Tenant', 'owner');
        $otherTenant = Tenant::query()->create([
            'name' => 'Other Tenant',
            'slug' => 'other-tenant',
            'status' => 'active',
        ]);

        $this->assertNotSame($ownedTenant->id, $otherTenant->id);

        $this->actingAs($owner)
            ->post("/cms/access/tenants/{$otherTenant->id}/users", [
                'form_target' => "tenant-{$otherTenant->id}",
                'name' => 'Outside User',
                'email' => 'outside@example.com',
                'password' => 'password123',
                'tenant_role' => 'viewer',
            ])
            ->assertForbidden();
    }

    private function tenantForUser(User $user, string $slug, string $name, string $role): Tenant
    {
        $tenant = Tenant::query()->create([
            'name' => $name,
            'slug' => $slug,
            'status' => 'active',
        ]);

        $tenant->users()->attach($user->id, ['role' => $role]);

        return $tenant;
    }
}
