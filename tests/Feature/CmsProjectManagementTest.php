<?php

namespace Tests\Feature;

use App\Models\ProjectFolderItem;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_developers_can_view_the_projects_page(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'North Studio',
            'slug' => 'north-studio',
            'status' => 'active',
            'company_email' => 'hej@northstudio.dk',
            'cvr_number' => '12345678',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'North Studio Site',
            'slug' => 'north-studio-site',
            'theme' => 'base',
            'status' => 'draft',
            'is_online' => false,
        ]);

        ProjectFolderItem::query()->create([
            'tenant_id' => $tenant->id,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($developer)->get(route('cms.projects.index'));

        $response->assertOk();
        $response->assertSee('Find kunder til projektmappen');
        $response->assertSee('Kunder i projektmappen');
        $response->assertSee('North Studio');
        $response->assertSee('North Studio Site');
        $response->assertSee(route('cms.sites.show', $site), false);
    }

    public function test_customer_managers_can_add_customers_to_the_project_folder(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
            'developer_access' => User::DEVELOPER_ACCESS_CUSTOMER_MANAGER,
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Maaneskoen',
            'slug' => 'maaneskoen',
            'status' => 'active',
        ]);

        $response = $this->actingAs($developer)->post(route('cms.projects.store', $tenant));

        $response->assertRedirect(route('cms.projects.index'));

        $this->assertDatabaseHas('project_folder_items', [
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_customer_managers_can_remove_customers_from_the_project_folder(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
            'developer_access' => User::DEVELOPER_ACCESS_CUSTOMER_MANAGER,
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Maaneskoen',
            'slug' => 'maaneskoen',
            'status' => 'active',
        ]);

        $item = ProjectFolderItem::query()->create([
            'tenant_id' => $tenant->id,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($developer)->delete(route('cms.projects.destroy', $item));

        $response->assertRedirect(route('cms.projects.index'));
        $this->assertDatabaseMissing('project_folder_items', [
            'id' => $item->id,
        ]);
    }

    public function test_customer_managers_can_save_notes_on_project_folder_items(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
            'developer_access' => User::DEVELOPER_ACCESS_CUSTOMER_MANAGER,
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'North Studio',
            'slug' => 'north-studio',
            'status' => 'active',
        ]);

        $item = ProjectFolderItem::query()->create([
            'tenant_id' => $tenant->id,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($developer)->patch(route('cms.projects.update', $item), [
            'notes' => 'Afventer feedback på hero og næste step er footer.',
        ]);

        $response->assertRedirect(route('cms.projects.index'));

        $this->assertDatabaseHas('project_folder_items', [
            'id' => $item->id,
            'notes' => 'Afventer feedback på hero og næste step er footer.',
        ]);
    }

    public function test_clients_cannot_view_or_manage_projects(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Blocked Tenant',
            'slug' => 'blocked-tenant',
            'status' => 'active',
        ]);

        $this->actingAs($client)
            ->get(route('cms.projects.index'))
            ->assertForbidden();

        $this->actingAs($client)
            ->post(route('cms.projects.store', $tenant))
            ->assertForbidden();
    }

    public function test_read_only_developers_can_view_but_not_modify_projects(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
            'developer_access' => User::DEVELOPER_ACCESS_READ_ONLY,
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Read Only Tenant',
            'slug' => 'read-only-tenant',
            'status' => 'active',
        ]);

        $this->actingAs($developer)
            ->get(route('cms.projects.index'))
            ->assertOk();

        $this->actingAs($developer)
            ->post(route('cms.projects.store', $tenant))
            ->assertForbidden();

        $item = ProjectFolderItem::query()->create([
            'tenant_id' => $tenant->id,
            'sort_order' => 1,
        ]);

        $this->actingAs($developer)
            ->patch(route('cms.projects.update', $item), [
                'notes' => 'Må ikke kunne gemmes.',
            ])
            ->assertForbidden();
    }
}
