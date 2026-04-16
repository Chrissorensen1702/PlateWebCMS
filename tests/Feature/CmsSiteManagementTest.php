<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CmsSiteManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_can_view_their_own_tenant_site_editor(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
            'name' => 'Mia Jensen',
        ]);

        $tenant = $this->tenantForUser($client, 'north-studio', 'North Studio', 'owner');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Demo Site',
            'slug' => 'demo-site',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Demo Site',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($client)->get("/cms/sites/{$site->id}");

        $response->assertOk();
        $response->assertSee('Demo Site');
        $response->assertSee('Globalt website indhold');
        $response->assertSee('Websitekonfiguration');
        $response->assertSee('Style');
        $response->assertSee('Integration og synlighed');
        $response->assertSee('Abonnement');
        $this->assertDatabaseHas('site_page_drafts', [
            'site_id' => $site->id,
            'slug' => 'home',
        ]);
    }

    public function test_clients_can_open_a_single_page_editor_for_their_tenant_site(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'single-page-tenant', 'Single Page Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Focused Demo',
            'slug' => 'focused-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $site->pages()->create([
            'name' => 'Kontakt',
            'slug' => 'kontakt',
            'title' => 'Kontakt os',
            'is_home' => false,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($client)->get("/cms/sites/{$site->id}");
        $draftPage = $site->draftPages()->firstOrFail();

        $response = $this->actingAs($client)->get("/cms/sites/{$site->id}/pages/{$draftPage->id}");

        $response->assertOk();
        $response->assertSee('Kontakt');
        $response->assertSee('Kladdepreview');
        $response->assertSee('Gem kladde');
        $response->assertSee('Modulbibliotek');
        $response->assertSee('Intro');
        $response->assertSee('Indhold og produkter');
    }

    public function test_clients_can_open_an_authenticated_draft_preview_inside_the_page_editor(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'draft-preview-tenant', 'Draft Preview Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Draft Preview Demo',
            'slug' => 'draft-preview-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Draft Preview Demo',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($client)->get("/cms/sites/{$site->id}");
        $draftPage = $site->draftPages()->firstOrFail();

        $response = $this->actingAs($client)->get("/cms/sites/{$site->id}/pages/{$draftPage->id}/preview");

        $response->assertOk();
        $response->assertSee('Draft Preview Demo');
        $response->assertSee('Dette indholdsomraade er endnu ikke mappet til et theme.');
    }

    public function test_read_only_developers_can_view_sites_but_cannot_update_them(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
            'developer_access' => User::DEVELOPER_ACCESS_READ_ONLY,
        ]);

        $owner = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($owner, 'read-only-dev-tenant', 'Read Only Dev Tenant', 'owner');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Read Only Demo',
            'slug' => 'read-only-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $this->actingAs($developer)
            ->get("/cms/sites/{$site->id}")
            ->assertOk();

        $this->actingAs($developer)
            ->patch("/cms/sites/{$site->id}", [
                'name' => 'Should Not Save',
            ])
            ->assertForbidden();
    }

    public function test_editors_can_autosave_page_changes_via_json_and_get_preview_payload(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'autosave-tenant', 'Autosave Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Autosave Demo',
            'slug' => 'autosave-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Autosave Demo',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($client)->get("/cms/sites/{$site->id}");
        $draftPage = $site->draftPages()->firstOrFail();

        $hero = $draftPage->areas()->create([
            'area_key' => 'hero',
            'area_type' => 'hero',
            'label' => 'Topsektion',
            'sort_order' => 1,
            'is_active' => true,
            'data' => [],
        ]);

        $response = $this->actingAs($client)->patchJson("/cms/sites/{$site->id}/pages/{$draftPage->id}", [
            'return_to' => 'design',
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Autosave Demo',
            'meta_description' => '',
            'sort_order' => 1,
            'is_published' => false,
            'is_home' => true,
            'areas' => [
                $hero->id => [
                    'is_active' => true,
                    'eyebrow' => 'Ny overtekst',
                    'title' => 'Opdateret hero',
                    'copy' => 'Denne tekst blev gemt via autosave.',
                    'heading_size' => 'large',
                    'text_align' => 'left',
                    'button_align' => 'left',
                    'secondary_cta_mode' => 'hide',
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'saved',
            'message' => "Siden 'Forside' er gemt i kladden.",
            'preview_url' => route('cms.pages.preview', [$site, $draftPage]),
        ]);

        $hero->refresh();

        $this->assertSame('Opdateret hero', $hero->data['title'] ?? null);
        $this->assertSame('Denne tekst blev gemt via autosave.', $hero->data['copy'] ?? null);
        $this->assertSame('hide', $hero->data['secondary_cta_mode'] ?? null);
    }

    public function test_editors_can_add_a_new_section_from_the_module_library(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'module-library-tenant', 'Module Library Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Module Library Demo',
            'slug' => 'module-library-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Module Library Demo',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($client)->get("/cms/sites/{$site->id}");
        $draftPage = $site->draftPages()->firstOrFail();

        $response = $this->actingAs($client)->post("/cms/sites/{$site->id}/pages/{$draftPage->id}/sections", [
            'area_type' => 'faq',
        ]);

        $newArea = $draftPage->fresh()->areas()->where('area_type', 'faq')->first();

        $this->assertNotNull($newArea);
        $response->assertRedirect("/cms/sites/{$site->id}/pages/{$draftPage->id}#area-{$newArea->id}");

        $this->assertDatabaseHas('site_page_draft_areas', [
            'id' => $newArea->id,
            'site_page_draft_id' => $draftPage->id,
            'area_type' => 'faq',
            'is_active' => true,
        ]);
    }

    public function test_editors_can_reorder_sections_from_drag_and_drop_sidebar_flow(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'section-order-tenant', 'Section Order Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Section Order Demo',
            'slug' => 'section-order-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $draftPage = $site->draftPages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Section Order Demo',
            'is_home' => true,
            'is_published' => false,
            'sort_order' => 1,
        ]);

        $hero = $draftPage->areas()->create([
            'area_key' => 'hero',
            'area_type' => 'hero',
            'label' => 'Topsektion',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $content = $draftPage->areas()->create([
            'area_key' => 'content',
            'area_type' => 'content',
            'label' => 'Indhold',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $faq = $draftPage->areas()->create([
            'area_key' => 'faq',
            'area_type' => 'faq',
            'label' => 'FAQ',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        $response = $this->actingAs($client)->patch("/cms/sites/{$site->id}/pages/{$draftPage->id}/sections/reorder", [
            'section_ids' => [$hero->id, $faq->id, $content->id],
            'focus_section_id' => $faq->id,
        ]);

        $response->assertRedirect("/cms/sites/{$site->id}/pages/{$draftPage->id}#area-{$faq->id}");

        $this->assertDatabaseHas('site_page_draft_areas', [
            'id' => $hero->id,
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('site_page_draft_areas', [
            'id' => $faq->id,
            'sort_order' => 2,
        ]);

        $this->assertDatabaseHas('site_page_draft_areas', [
            'id' => $content->id,
            'sort_order' => 3,
        ]);
    }

    public function test_editors_can_toggle_section_visibility_from_the_sidebar_actions(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'section-visibility-tenant', 'Section Visibility Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Section Visibility Demo',
            'slug' => 'section-visibility-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $draftPage = $site->draftPages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Section Visibility Demo',
            'is_home' => true,
            'is_published' => false,
            'sort_order' => 1,
        ]);

        $content = $draftPage->areas()->create([
            'area_key' => 'content',
            'area_type' => 'content',
            'label' => 'Indhold',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($client)->patch("/cms/sites/{$site->id}/pages/{$draftPage->id}/sections/{$content->id}/visibility", [
            'is_active' => 0,
        ]);

        $response->assertRedirect("/cms/sites/{$site->id}/pages/{$draftPage->id}#area-{$content->id}");

        $this->assertDatabaseHas('site_page_draft_areas', [
            'id' => $content->id,
            'is_active' => false,
        ]);
    }

    public function test_editors_can_delete_sections_from_the_sidebar_actions(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'section-delete-tenant', 'Section Delete Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Section Delete Demo',
            'slug' => 'section-delete-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $draftPage = $site->draftPages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Section Delete Demo',
            'is_home' => true,
            'is_published' => false,
            'sort_order' => 1,
        ]);

        $hero = $draftPage->areas()->create([
            'area_key' => 'hero',
            'area_type' => 'hero',
            'label' => 'Topsektion',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $faq = $draftPage->areas()->create([
            'area_key' => 'faq',
            'area_type' => 'faq',
            'label' => 'FAQ',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($client)->delete("/cms/sites/{$site->id}/pages/{$draftPage->id}/sections/{$hero->id}");

        $response->assertRedirect("/cms/sites/{$site->id}/pages/{$draftPage->id}#area-{$faq->id}");

        $this->assertDatabaseMissing('site_page_draft_areas', [
            'id' => $hero->id,
        ]);

        $this->assertDatabaseHas('site_page_draft_areas', [
            'id' => $faq->id,
            'sort_order' => 1,
        ]);
    }

    public function test_editors_can_delete_sections_from_the_sidebar_modal_without_redirect_via_json(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'section-delete-json-tenant', 'Section Delete Json Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Section Delete Json Demo',
            'slug' => 'section-delete-json-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $draftPage = $site->draftPages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Section Delete Json Demo',
            'is_home' => true,
            'is_published' => false,
            'sort_order' => 1,
        ]);

        $hero = $draftPage->areas()->create([
            'area_key' => 'hero',
            'area_type' => 'hero',
            'label' => 'Topsektion',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $faq = $draftPage->areas()->create([
            'area_key' => 'faq',
            'area_type' => 'faq',
            'label' => 'FAQ',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($client)->deleteJson("/cms/sites/{$site->id}/pages/{$draftPage->id}/sections/{$hero->id}");

        $response->assertOk();
        $response->assertJson([
            'status' => 'deleted',
            'message' => "Afsnittet 'Topsektion' er fjernet fra siden.",
            'focus_section_id' => $faq->id,
        ]);

        $this->assertDatabaseMissing('site_page_draft_areas', [
            'id' => $hero->id,
        ]);
    }

    public function test_clients_can_open_page_settings_for_their_tenant_site(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'settings-open-tenant', 'Settings Open Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Settings Open Demo',
            'slug' => 'settings-open-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $site->pages()->create([
            'name' => 'Kontakt',
            'slug' => 'kontakt',
            'title' => 'Kontakt os',
            'is_home' => false,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($client)->get("/cms/sites/{$site->id}");
        $draftPage = $site->draftPages()->firstOrFail();

        $response = $this->actingAs($client)->get("/cms/sites/{$site->id}/pages/{$draftPage->id}/settings");

        $response->assertOk();
        $response->assertSee('Sideopsaetning');
        $response->assertSee('Slug / URL-del');
        $response->assertSee('Aaben designer');
    }

    public function test_clients_cannot_open_the_developer_only_custom_code_editor(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'custom-code-tenant', 'Custom Code Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Custom Code Demo',
            'slug' => 'custom-code-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Custom Code Demo',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($client)->get("/cms/sites/{$site->id}");
        $draftPage = $site->draftPages()->firstOrFail();

        $this->actingAs($client)
            ->get("/cms/sites/{$site->id}/pages/{$draftPage->id}/custom-code")
            ->assertForbidden();
    }

    public function test_clients_can_open_a_global_configuration_module_for_their_tenant_site(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'global-module-tenant', 'Global Module Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Global Module Demo',
            'slug' => 'global-module-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $response = $this->actingAs($client)->get("/cms/sites/{$site->id}/global-content/theme");

        $response->assertOk();
        $response->assertSee('Website-theme');
        $response->assertSee('Themevalg');
    }

    public function test_legacy_global_module_urls_redirect_to_global_content_pages(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'legacy-global-tenant', 'Legacy Global Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Legacy Global Module Demo',
            'slug' => 'legacy-global-module-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $this->actingAs($client)
            ->get("/cms/sites/{$site->id}/header")
            ->assertRedirect("/cms/sites/{$site->id}/global-content/header");
    }

    public function test_clients_cannot_view_other_tenants_site_editor(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $otherClient = User::factory()->create([
            'role' => 'client',
        ]);

        $otherTenant = $this->tenantForUser($otherClient, 'locked-tenant', 'Locked Tenant', 'owner');

        $site = Site::query()->create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Locked Site',
            'slug' => 'locked-site',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $response = $this->actingAs($client)->get("/cms/sites/{$site->id}");

        $response->assertForbidden();
    }

    public function test_clients_cannot_view_other_tenants_single_page_editor(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $otherClient = User::factory()->create([
            'role' => 'client',
        ]);

        $otherTenant = $this->tenantForUser($otherClient, 'locked-page-tenant', 'Locked Page Tenant', 'owner');

        $site = Site::query()->create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Locked Site',
            'slug' => 'locked-site',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $site->pages()->create([
            'name' => 'Kontakt',
            'slug' => 'kontakt',
            'title' => 'Kontakt',
            'is_home' => false,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($otherClient)->get("/cms/sites/{$site->id}");
        $draftPage = $site->draftPages()->firstOrFail();

        $this->actingAs($client)
            ->get("/cms/sites/{$site->id}/pages/{$draftPage->id}")
            ->assertForbidden();
    }

    public function test_editors_can_update_their_tenant_site_content_in_draft_without_touching_live_site(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'editor-tenant', 'Editor Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Demo Site',
            'slug' => 'demo-site',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $page = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Demo Site',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $area = $page->areas()->create([
            'key' => 'hero-main',
            'type' => 'hero',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $area->syncData([
            'title' => 'Gammel titel',
        ]);

        $this->actingAs($client)->get("/cms/sites/{$site->id}");
        $draftPage = $site->draftPages()->with('areas.fields')->firstOrFail();
        $draftArea = $draftPage->areas->firstOrFail();

        $response = $this->actingAs($client)->patch("/cms/sites/{$site->id}/pages/{$draftPage->id}", [
            'form_target' => "page-{$draftPage->id}",
            'name' => $draftPage->name,
            'slug' => $draftPage->slug,
            'title' => $draftPage->title,
            'meta_description' => $draftPage->meta_description,
            'sort_order' => $draftPage->sort_order,
            'is_published' => '1',
            'is_home' => '1',
            'areas' => [
                $draftArea->id => [
                    'is_active' => '1',
                    'title' => 'Ny hero titel',
                    'copy' => 'Opdateret tekst',
                    'image_url' => '/images/demo/maison-glow-hero.svg',
                    'image_alt' => 'Salon hero',
                    'image_focus' => 'right',
                    'heading_size' => 'standard',
                    'text_align' => 'center',
                    'button_align' => 'right',
                    'secondary_cta_mode' => 'hide',
                    'primary_cta_label' => 'Kontakt os',
                    'primary_cta_href' => '/kontakt',
                ],
            ],
        ]);

        $response->assertRedirect("/cms/sites/{$site->id}/pages/{$draftPage->id}");

        $this->assertDatabaseHas('site_page_draft_areas', [
            'id' => $draftArea->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('site_page_draft_area_fields', [
            'site_page_draft_area_id' => $draftArea->id,
            'field_key' => 'title',
            'position' => 1,
            'value' => 'Ny hero titel',
        ]);

        $this->assertDatabaseHas('site_page_draft_area_fields', [
            'site_page_draft_area_id' => $draftArea->id,
            'field_key' => 'heading_size',
            'position' => 1,
            'value' => 'standard',
        ]);

        $this->assertDatabaseHas('site_page_draft_area_fields', [
            'site_page_draft_area_id' => $draftArea->id,
            'field_key' => 'button_align',
            'position' => 1,
            'value' => 'right',
        ]);

        $this->assertDatabaseHas('site_page_draft_area_fields', [
            'site_page_draft_area_id' => $draftArea->id,
            'field_key' => 'image_url',
            'position' => 1,
            'value' => '/images/demo/maison-glow-hero.svg',
        ]);

        $this->assertDatabaseHas('site_page_draft_area_fields', [
            'site_page_draft_area_id' => $draftArea->id,
            'field_key' => 'image_focus',
            'position' => 1,
            'value' => 'right',
        ]);

        $this->assertDatabaseHas('site_page_area_fields', [
            'site_page_area_id' => $area->id,
            'field_key' => 'title',
            'position' => 1,
            'value' => 'Gammel titel',
        ]);
    }

    public function test_developers_can_save_and_publish_custom_code_for_a_page(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Custom Build Tenant',
            'slug' => 'custom-build-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Custom Build Site',
            'slug' => 'custom-build-site',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $page = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Custom Build Site',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $page->areas()->create([
            'key' => 'hero-main',
            'type' => 'hero',
            'sort_order' => 1,
            'is_active' => true,
        ])->syncData([
            'title' => 'Eksisterende sektion',
        ]);

        $this->actingAs($developer)->get("/cms/sites/{$site->id}");
        $draftPage = $site->draftPages()->firstOrFail();

        $this->actingAs($developer)
            ->get("/cms/sites/{$site->id}/pages/{$draftPage->id}/custom-code")
            ->assertOk()
            ->assertSee('Udvidet CMS')
            ->assertSee('Fri HTML og CSS');

        $response = $this->actingAs($developer)->patch("/cms/sites/{$site->id}/pages/{$draftPage->id}", [
            'return_to' => 'custom-code',
            'name' => $draftPage->name,
            'slug' => $draftPage->slug,
            'title' => $draftPage->title,
            'meta_description' => $draftPage->meta_description,
            'sort_order' => $draftPage->sort_order,
            'is_published' => '1',
            'is_home' => '1',
            'layout_mode' => 'custom-full',
            'custom_html' => '<section class="custom-hero"><h1>PlateWeb Custom</h1></section>',
            'custom_css' => '.custom-hero { padding: 5rem 0; }',
            'publish_after_save' => '1',
        ]);

        $response->assertRedirect("/cms/sites/{$site->id}/pages/{$draftPage->id}/custom-code");

        $this->assertDatabaseHas('site_page_drafts', [
            'id' => $draftPage->id,
            'layout_mode' => 'custom-full',
        ]);

        $this->assertDatabaseHas('site_pages', [
            'id' => $page->id,
            'layout_mode' => 'custom-full',
        ]);

        $page->refresh();
        $this->assertStringContainsString('PlateWeb Custom', (string) $page->custom_html);
        $this->assertStringContainsString('.custom-hero', (string) $page->custom_css);
    }

    public function test_editors_can_upload_a_hero_image_to_draft_content(): void
    {
        Storage::fake('public');
        config()->set('filesystems.site_media_disk', 'public');

        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'upload-demo-tenant', 'Upload Demo Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Upload Demo',
            'slug' => 'upload-demo',
            'theme' => 'editorial',
            'status' => 'ready',
        ]);

        $page = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Upload Demo',
            'template_key' => 'landing',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $area = $page->areas()->create([
            'key' => 'hero-main',
            'type' => 'hero',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $area->syncData([
            'title' => 'Upload titel',
        ]);

        $this->actingAs($client)->get("/cms/sites/{$site->id}");

        $draftPage = $site->draftPages()->with('areas.fields')->firstOrFail();
        $draftArea = $draftPage->areas->firstOrFail();

        $this->actingAs($client)->patch("/cms/sites/{$site->id}/pages/{$draftPage->id}", [
            'form_target' => "page-{$draftPage->id}",
            'name' => $draftPage->name,
            'slug' => $draftPage->slug,
            'title' => $draftPage->title,
            'meta_description' => $draftPage->meta_description,
            'sort_order' => $draftPage->sort_order,
            'is_published' => '1',
            'is_home' => '1',
            'areas' => [
                $draftArea->id => [
                    'is_active' => '1',
                    'title' => 'Upload titel',
                    'copy' => 'Opdateret med billede',
                    'image_upload' => UploadedFile::fake()->image('salon-hero.jpg', 1600, 1200),
                    'image_alt' => 'Salon med rolige toner',
                ],
            ],
        ])->assertRedirect("/cms/sites/{$site->id}/pages/{$draftPage->id}");

        $imageField = DB::table('site_page_draft_area_fields')
            ->where('site_page_draft_area_id', $draftArea->id)
            ->where('field_key', 'image_url')
            ->first();

        $this->assertNotNull($imageField);
        $this->assertStringStartsWith('site-media/upload-demo/home/hero-main/', $imageField->value);
        Storage::disk('public')->assertExists($imageField->value);
    }

    public function test_editors_can_create_a_new_page_from_a_finished_page_template_in_draft(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'page-tenant', 'Page Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Page Demo',
            'slug' => 'page-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $response = $this->actingAs($client)->post("/cms/sites/{$site->id}/pages", [
            'form_target' => 'create-page',
            'name' => 'Services',
            'slug' => 'services',
            'title' => 'Vores services',
            'meta_description' => 'Kort overblik over services',
            'sort_order' => 2,
            'is_published' => '1',
            'is_home' => '0',
            'page_template' => 'services',
        ]);

        $page = $site->draftPages()->first();

        $this->assertNotNull($page);
        $response->assertRedirect("/cms/sites/{$site->id}?page={$page->id}");
        $this->assertSame('Services', $page->name);
        $this->assertTrue($page->is_home);
        $this->assertSame('services', $page->template_key);
        $this->assertCount(4, $page->areas()->get());
        $this->assertDatabaseHas('site_page_draft_areas', [
            'site_page_draft_id' => $page->id,
            'area_key' => 'services-hero',
            'area_type' => 'hero',
        ]);
        $this->assertDatabaseHas('site_page_draft_areas', [
            'site_page_draft_id' => $page->id,
            'area_key' => 'services-process',
            'area_type' => 'content',
        ]);
        $this->assertDatabaseHas('site_page_draft_areas', [
            'site_page_draft_id' => $page->id,
            'area_key' => 'services-cta',
            'area_type' => 'contact',
        ]);
        $this->assertDatabaseMissing('site_pages', [
            'site_id' => $site->id,
            'slug' => 'services',
        ]);
    }

    public function test_editors_can_create_a_new_page_from_prisside_template_in_draft(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'pricing-page-tenant', 'Pricing Page Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Pricing Demo',
            'slug' => 'pricing-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $response = $this->actingAs($client)->post("/cms/sites/{$site->id}/pages", [
            'form_target' => 'create-page',
            'name' => 'Priser',
            'slug' => 'priser',
            'title' => 'Priser',
            'meta_description' => 'Overblik over pakker og prisniveauer',
            'sort_order' => 2,
            'is_published' => '1',
            'is_home' => '0',
            'page_template' => 'pricing',
        ]);

        $page = $site->draftPages()->with('areas.fields')->first();

        $this->assertNotNull($page);
        $response->assertRedirect("/cms/sites/{$site->id}?page={$page->id}");
        $this->assertSame('pricing', $page->template_key);
        $this->assertCount(5, $page->areas);

        $packagesArea = $page->areas->firstWhere('area_key', 'pricing-packages');
        $includedArea = $page->areas->firstWhere('area_key', 'pricing-included');
        $quoteArea = $page->areas->firstWhere('area_key', 'pricing-quote');
        $contactArea = $page->areas->firstWhere('area_key', 'pricing-cta');

        $this->assertNotNull($packagesArea);
        $this->assertNotNull($includedArea);
        $this->assertNotNull($quoteArea);
        $this->assertNotNull($contactArea);
        $this->assertSame('stats', $packagesArea->area_type);
        $this->assertSame('cards', $packagesArea->data['display_style'] ?? null);
        $this->assertSame('accent', $packagesArea->data['section_tone'] ?? null);
        $this->assertSame('list', $includedArea->data['items_style'] ?? null);
        $this->assertSame('quote', $quoteArea->area_type);
        $this->assertSame('center', $quoteArea->data['text_align'] ?? null);
        $this->assertSame('center', $contactArea->data['layout_style'] ?? null);
    }

    public function test_editors_must_choose_a_page_template_when_creating_a_page(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'page-validation-tenant', 'Page Validation Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Validation Demo',
            'slug' => 'validation-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $response = $this->from("/cms/sites/{$site->id}")->actingAs($client)->post("/cms/sites/{$site->id}/pages", [
            'form_target' => 'create-page',
            'name' => 'Om os',
            'slug' => 'om-os',
            'title' => 'Om os',
            'is_published' => '1',
            'is_home' => '0',
        ]);

        $response->assertRedirect("/cms/sites/{$site->id}");
        $response->assertSessionHasErrors(['page_template'], null, 'createPage');
        $this->assertDatabaseMissing('site_page_drafts', [
            'site_id' => $site->id,
            'slug' => 'om-os',
        ]);
    }

    public function test_editors_can_update_page_metadata_in_draft(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'page-edit-tenant', 'Page Edit Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Meta Demo',
            'slug' => 'meta-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $firstPage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Meta Demo',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $site->pages()->create([
            'name' => 'Kontakt',
            'slug' => 'kontakt',
            'title' => 'Kontakt os',
            'is_home' => false,
            'is_published' => true,
            'sort_order' => 2,
        ]);

        $this->actingAs($client)->get("/cms/sites/{$site->id}");
        $secondDraftPage = $site->draftPages()->where('slug', 'kontakt')->firstOrFail();

        $response = $this->actingAs($client)->patch("/cms/sites/{$site->id}/pages/{$secondDraftPage->id}", [
            'return_to' => 'settings',
            'form_target' => "page-{$secondDraftPage->id}",
            'name' => 'Om os',
            'slug' => 'om-os',
            'title' => 'Om virksomheden',
            'meta_description' => 'Ny metabeskrivelse',
            'sort_order' => 1,
            'is_published' => '1',
            'is_home' => '1',
        ]);

        $response->assertRedirect("/cms/sites/{$site->id}/pages/{$secondDraftPage->id}/settings");

        $this->assertDatabaseHas('site_page_drafts', [
            'id' => $secondDraftPage->id,
            'name' => 'Om os',
            'slug' => 'om-os',
            'title' => 'Om virksomheden',
            'is_home' => true,
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('site_pages', [
            'id' => $firstPage->id,
            'is_home' => true,
        ]);
    }

    public function test_editors_can_update_page_settings_without_clearing_designer_content(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'settings-tenant', 'Settings Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Settings Demo',
            'slug' => 'settings-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $page = $site->pages()->create([
            'name' => 'Kontakt',
            'slug' => 'kontakt',
            'title' => 'Kontakt os',
            'template_key' => 'contact',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $area = $page->areas()->create([
            'key' => 'contact-hero',
            'type' => 'hero',
            'label' => 'Topsektion',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $area->syncData([
            'title' => 'Behold denne titel',
            'copy' => 'Denne tekst maa ikke blive nulstillet.',
        ]);

        $this->actingAs($client)->get("/cms/sites/{$site->id}");
        $draftPage = $site->draftPages()->with('areas.fields')->firstOrFail();
        $draftArea = $draftPage->areas->firstOrFail();

        $response = $this->actingAs($client)->patch("/cms/sites/{$site->id}/pages/{$draftPage->id}", [
            'return_to' => 'settings',
            'form_target' => "page-{$draftPage->id}",
            'name' => 'Kontakt',
            'slug' => 'kontakt-os',
            'title' => 'Kontakt PlateWeb',
            'meta_description' => 'Opdateret sidebeskrivelse',
            'sort_order' => 2,
            'is_published' => '1',
            'is_home' => '1',
        ]);

        $response->assertRedirect("/cms/sites/{$site->id}/pages/{$draftPage->id}/settings");

        $this->assertDatabaseHas('site_page_drafts', [
            'id' => $draftPage->id,
            'slug' => 'kontakt-os',
            'title' => 'Kontakt PlateWeb',
            'sort_order' => 2,
        ]);

        $this->assertDatabaseHas('site_page_draft_area_fields', [
            'site_page_draft_area_id' => $draftArea->id,
            'field_key' => 'title',
            'position' => 1,
            'value' => 'Behold denne titel',
        ]);

        $this->assertDatabaseHas('site_page_draft_area_fields', [
            'site_page_draft_area_id' => $draftArea->id,
            'field_key' => 'copy',
            'position' => 1,
            'value' => 'Denne tekst maa ikke blive nulstillet.',
        ]);
    }

    public function test_editors_can_save_page_settings_from_site_dashboard_modal(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'dashboard-settings-tenant', 'Dashboard Settings Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Dashboard Settings Demo',
            'slug' => 'dashboard-settings-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $page = $site->pages()->create([
            'name' => 'Kontakt',
            'slug' => 'kontakt',
            'title' => 'Kontakt os',
            'template_key' => 'contact',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($client)->get("/cms/sites/{$site->id}");
        $draftPage = $site->draftPages()->firstOrFail();

        $response = $this->actingAs($client)->patch("/cms/sites/{$site->id}/pages/{$draftPage->id}", [
            'return_to' => 'dashboard',
            'name' => 'Kontaktside',
            'slug' => 'kontaktside',
            'title' => 'Kontakt PlateWeb',
            'meta_description' => 'Kontakt vores team i dag.',
            'sort_order' => 2,
            'is_published' => '1',
            'is_home' => '1',
        ]);

        $response->assertRedirect("/cms/sites/{$site->id}?page={$draftPage->id}");

        $this->assertDatabaseHas('site_page_drafts', [
            'id' => $draftPage->id,
            'name' => 'Kontaktside',
            'slug' => 'kontaktside',
            'title' => 'Kontakt PlateWeb',
            'sort_order' => 2,
        ]);
    }

    public function test_editors_can_delete_a_page_from_draft_and_are_redirected_to_another_draft_page(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'page-delete-tenant', 'Page Delete Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Delete Demo',
            'slug' => 'delete-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Delete Demo',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $contactPage = $site->pages()->create([
            'name' => 'Kontakt',
            'slug' => 'kontakt',
            'title' => 'Kontakt',
            'is_home' => false,
            'is_published' => true,
            'sort_order' => 2,
        ]);

        $this->actingAs($client)->get("/cms/sites/{$site->id}");
        $homeDraft = $site->draftPages()->where('slug', 'home')->firstOrFail();
        $contactDraft = $site->draftPages()->where('slug', 'kontakt')->firstOrFail();

        $response = $this->actingAs($client)->delete("/cms/sites/{$site->id}/pages/{$homeDraft->id}");

        $response->assertRedirect("/cms/sites/{$site->id}?page={$contactDraft->id}");
        $this->assertDatabaseMissing('site_page_drafts', [
            'id' => $homeDraft->id,
        ]);
        $this->assertTrue($contactDraft->fresh()->is_home);
        $this->assertDatabaseHas('site_pages', [
            'id' => $homePage->id,
            'is_home' => true,
        ]);
        $this->assertDatabaseHas('site_pages', [
            'id' => $contactPage->id,
        ]);
    }

    public function test_viewers_can_view_but_not_update_tenant_content(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'viewer-tenant', 'Viewer Tenant', 'viewer');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Viewer Site',
            'slug' => 'viewer-site',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $page = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Viewer Site',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $area = $page->areas()->create([
            'key' => 'hero-main',
            'type' => 'hero',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $area->syncData([
            'title' => 'Kun laesning',
        ]);

        $this->actingAs($client)
            ->get("/cms/sites/{$site->id}")
            ->assertOk()
            ->assertSee('læseadgang');

        $draftPage = $site->draftPages()->firstOrFail();
        $draftArea = $draftPage->areas()->firstOrFail();

        $this->actingAs($client)
            ->get("/cms/sites/{$site->id}/pages/{$draftPage->id}")
            ->assertOk()
            ->assertSee('læseadgang');

        $this->actingAs($client)
            ->patch("/cms/sites/{$site->id}/pages/{$draftPage->id}", [
                'form_target' => "page-{$draftPage->id}",
                'name' => $draftPage->name,
                'slug' => $draftPage->slug,
                'title' => $draftPage->title,
                'sort_order' => $draftPage->sort_order,
                'is_published' => '1',
                'is_home' => '1',
                'areas' => [
                    $draftArea->id => [
                        'is_active' => '1',
                        'title' => 'Maa ikke gemmes',
                    ],
                ],
            ])
            ->assertForbidden();

        $this->actingAs($client)
            ->post("/cms/sites/{$site->id}/pages", [
                'form_target' => 'create-page',
                'name' => 'Ny side',
                'title' => 'Ny side',
                'slug' => 'ny-side',
                'is_published' => '1',
                'is_home' => '0',
                'page_template' => 'contact',
            ])
            ->assertForbidden();

        $this->actingAs($client)
            ->delete("/cms/sites/{$site->id}/pages/{$draftPage->id}")
            ->assertForbidden();

        $this->assertSame('Kun laesning', $draftArea->fresh()->data['title']);
    }

    public function test_editors_can_publish_all_draft_pages_in_one_action(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'publish-tenant', 'Publish Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Publish Demo',
            'slug' => 'publish-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Publish Demo',
            'template_key' => 'contact',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $heroArea = $homePage->areas()->create([
            'key' => 'hero-main',
            'type' => 'hero',
            'label' => 'Hero',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $heroArea->syncData([
            'title' => 'Live titel',
            'copy' => 'Live tekst',
        ]);

        $aboutPage = $site->pages()->create([
            'name' => 'Om os',
            'slug' => 'om-os',
            'title' => 'Om os',
            'template_key' => 'about',
            'is_home' => false,
            'is_published' => true,
            'sort_order' => 2,
        ]);

        $this->actingAs($client)->get("/cms/sites/{$site->id}");

        $homeDraft = $site->draftPages()->where('slug', 'home')->with('areas')->firstOrFail();
        $homeDraftArea = $homeDraft->areas->firstOrFail();

        $this->actingAs($client)->patch("/cms/sites/{$site->id}/pages/{$homeDraft->id}", [
            'form_target' => "page-{$homeDraft->id}",
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Publish Demo',
            'sort_order' => 1,
            'is_published' => '1',
            'is_home' => '1',
            'areas' => [
                $homeDraftArea->id => [
                    'is_active' => '1',
                    'title' => 'Ny draft titel',
                    'copy' => 'Ny draft tekst',
                ],
            ],
        ])->assertRedirect("/cms/sites/{$site->id}/pages/{$homeDraft->id}");

        $this->actingAs($client)->post("/cms/sites/{$site->id}/pages", [
            'form_target' => 'create-page',
            'name' => 'Kontakt',
            'slug' => 'kontakt',
            'title' => 'Kontakt os',
            'sort_order' => 3,
            'is_published' => '1',
            'is_home' => '0',
            'page_template' => 'contact',
        ])->assertRedirect();

        $this->actingAs($client)
            ->post("/cms/sites/{$site->id}/publish", [
                'redirect_to' => "/cms/sites/{$site->id}",
            ])
            ->assertRedirect("/cms/sites/{$site->id}");

        $homePage->refresh();
        $aboutPage->refresh();
        $heroArea->refresh();

        $this->assertSame('Ny draft titel', $heroArea->data['title']);
        $this->assertSame('Ny draft tekst', $heroArea->data['copy']);
        $this->assertDatabaseHas('site_pages', [
            'site_id' => $site->id,
            'slug' => 'om-os',
            'title' => 'Om os',
        ]);
        $this->assertDatabaseHas('site_pages', [
            'site_id' => $site->id,
            'slug' => 'kontakt',
            'title' => 'Kontakt os',
        ]);
        $this->assertNotNull($site->fresh()->last_published_at);
    }

    public function test_editors_can_publish_current_page_without_separate_save_first(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'publish-direct-tenant', 'Publish Direct Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Publish Direct Demo',
            'slug' => 'publish-direct-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $page = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Publish Direct Demo',
            'template_key' => 'contact',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $area = $page->areas()->create([
            'key' => 'hero-main',
            'type' => 'hero',
            'label' => 'Hero',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $area->syncData([
            'title' => 'Live titel',
            'copy' => 'Live tekst',
        ]);

        $this->actingAs($client)->get("/cms/sites/{$site->id}");

        $draftPage = $site->draftPages()->with('areas.fields')->firstOrFail();
        $draftArea = $draftPage->areas->firstOrFail();

        $this->actingAs($client)
            ->patch("/cms/sites/{$site->id}/pages/{$draftPage->id}", [
                'form_target' => "page-{$draftPage->id}",
                'name' => $draftPage->name,
                'slug' => $draftPage->slug,
                'title' => $draftPage->title,
                'sort_order' => $draftPage->sort_order,
                'is_published' => '1',
                'is_home' => '1',
                'publish_after_save' => '1',
                'areas' => [
                    $draftArea->id => [
                        'is_active' => '1',
                        'title' => 'Publiceret uden separat gem',
                        'copy' => 'Ny tekst direkte fra editoren',
                    ],
                ],
            ])
            ->assertRedirect("/cms/sites/{$site->id}/pages/{$draftPage->id}");

        $this->assertDatabaseHas('site_page_draft_area_fields', [
            'site_page_draft_area_id' => $draftArea->id,
            'field_key' => 'title',
            'value' => 'Publiceret uden separat gem',
        ]);

        $this->assertDatabaseHas('site_page_area_fields', [
            'site_page_area_id' => $area->id,
            'field_key' => 'title',
            'value' => 'Publiceret uden separat gem',
        ]);
    }

    public function test_editors_can_toggle_site_online_visibility(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'visibility-tenant', 'Visibility Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Visibility Demo',
            'slug' => 'visibility-demo',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => true,
        ]);

        $this->actingAs($client)
            ->post("/cms/sites/{$site->id}/visibility", [
                'is_online' => '0',
                'redirect_to' => "/cms/sites/{$site->id}",
            ])
            ->assertRedirect("/cms/sites/{$site->id}");

        $this->assertDatabaseHas('sites', [
            'id' => $site->id,
            'is_online' => false,
        ]);
    }

    public function test_editors_can_update_site_name_from_the_overview(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'rename-tenant', 'Rename Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Originalt navn',
            'slug' => 'originalt-navn',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $this->actingAs($client)
            ->patch("/cms/sites/{$site->id}", [
                'name' => 'Nyt websitenavn',
                'redirect_to' => "/cms/sites/{$site->id}",
            ])
            ->assertRedirect("/cms/sites/{$site->id}");

        $this->assertDatabaseHas('sites', [
            'id' => $site->id,
            'name' => 'Nyt websitenavn',
        ]);
    }

    public function test_external_redirect_targets_are_ignored_on_site_updates(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'redirect-tenant', 'Redirect Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Redirect Demo',
            'slug' => 'redirect-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $this->actingAs($client)
            ->patch("/cms/sites/{$site->id}", [
                'name' => 'Stadig lokalt',
                'redirect_to' => 'https://evil.example/phishing',
            ])
            ->assertRedirect("/cms/sites/{$site->id}");
    }

    public function test_editors_can_update_site_theme_from_global_content(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'theme-tenant', 'Theme Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Theme Demo',
            'slug' => 'theme-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $this->actingAs($client)
            ->patch("/cms/sites/{$site->id}/theme", [
                'theme' => 'midnight',
                'redirect_to' => "/cms/sites/{$site->id}/global-content#theme",
            ])
            ->assertRedirect("/cms/sites/{$site->id}/global-content#theme");

        $this->assertDatabaseHas('sites', [
            'id' => $site->id,
            'theme' => 'midnight',
        ]);
    }

    public function test_editors_can_update_site_colors_from_global_content(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'colors-tenant', 'Colors Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Colors Demo',
            'slug' => 'colors-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $this->actingAs($client)
            ->patch("/cms/sites/{$site->id}/colors", [
                'palette_key' => 'forest',
                'redirect_to' => "/cms/sites/{$site->id}/global-content/colors",
            ])
            ->assertRedirect("/cms/sites/{$site->id}/global-content/colors");

        $this->assertDatabaseHas('site_color_settings', [
            'site_id' => $site->id,
            'palette_key' => 'forest',
        ]);
    }

    public function test_editors_can_update_global_header_settings(): void
    {
        Storage::fake('public');
        Config::set('filesystems.site_media_disk', 'public');

        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'header-tenant', 'Header Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Header Demo',
            'slug' => 'header-demo',
            'theme' => 'editorial',
            'status' => 'ready',
        ]);

        $this->actingAs($client)
            ->patch("/cms/sites/{$site->id}/header", [
                'brand_name' => 'Salon Maaneskoen',
                'show_brand_name' => '1',
                'tagline' => 'Skoenhed og velvaere',
                'show_tagline' => '1',
                'logo_alt' => 'Salon Maaneskoen logo',
                'cta_label' => 'Book behandling',
                'cta_href' => '/kontakt',
                'show_cta' => '1',
                'background_style' => 'dark',
                'text_color_style' => 'light',
                'shadow_style' => 'strong',
                'sticky_mode' => 'sticky',
                'logo_upload' => UploadedFile::fake()->image('header-logo.png', 600, 240),
                'redirect_to' => "/cms/sites/{$site->id}/global-content#header",
            ])
            ->assertRedirect("/cms/sites/{$site->id}/global-content#header");

        $settings = $site->fresh()->headerSettings;

        $this->assertNotNull($settings);
        $this->assertSame('Salon Maaneskoen', $settings->brand_name);
        $this->assertTrue($settings->show_brand_name);
        $this->assertSame('Skoenhed og velvaere', $settings->tagline);
        $this->assertTrue($settings->show_tagline);
        $this->assertSame('Book behandling', $settings->cta_label);
        $this->assertSame('/kontakt', $settings->cta_href);
        $this->assertTrue($settings->show_cta);
        $this->assertSame('dark', $settings->background_style);
        $this->assertSame('light', $settings->text_color_style);
        $this->assertSame('strong', $settings->shadow_style);
        $this->assertSame('sticky', $settings->sticky_mode);
        $this->assertNotNull($settings->logo_path);
        Storage::disk('public')->assertExists($settings->logo_path);
    }

    public function test_editors_can_save_external_header_links_without_scheme(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'header-link-tenant', 'Header Link Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Header Link Demo',
            'slug' => 'header-link-demo',
            'theme' => 'minimal',
            'status' => 'ready',
        ]);

        $this->actingAs($client)
            ->patch("/cms/sites/{$site->id}/header", [
                'cta_label' => 'Gaa til PlateBook',
                'cta_href' => 'www.platebook.dk',
                'show_cta' => '1',
                'redirect_to' => "/cms/sites/{$site->id}/global-content/header",
            ])
            ->assertRedirect("/cms/sites/{$site->id}/global-content/header");

        $settings = $site->fresh()->headerSettings;

        $this->assertNotNull($settings);
        $this->assertSame('https://www.platebook.dk', $settings->cta_href);
        $this->assertTrue($settings->show_cta);
    }

    public function test_editors_can_update_global_footer_content(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'footer-tenant', 'Footer Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Footer Demo',
            'slug' => 'footer-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $this->actingAs($client)
            ->patch("/cms/sites/{$site->id}/footer", [
                'navigation_links' => [
                    ['label' => 'Forside', 'href' => '/'],
                    ['label' => 'Behandlinger', 'href' => '/behandlinger'],
                ],
                'information_links' => [
                    ['label' => 'Privatlivspolitik', 'href' => '/privatliv'],
                    ['label' => 'Handelsbetingelser', 'href' => '/betingelser'],
                ],
                'social_links' => [
                    'instagram' => [
                        'enabled' => '1',
                        'href' => 'https://instagram.com/maaneskoen',
                    ],
                    'facebook' => [
                        'enabled' => '0',
                        'href' => '',
                    ],
                ],
                'contact_email' => 'footer@example.test',
                'show_contact_email' => '1',
                'contact_phone' => '+45 11 22 33 44',
                'show_contact_phone' => '1',
                'contact_address' => "Strøget 12\n7430 Ikast",
                'show_contact_address' => '1',
                'contact_cvr' => '12345678',
                'show_contact_cvr' => '0',
                'redirect_to' => "/cms/sites/{$site->id}/global-content/footer",
            ])
            ->assertRedirect("/cms/sites/{$site->id}/global-content/footer");

        $settings = $site->fresh()->footerSettings;

        $this->assertNotNull($settings);
        $this->assertSame([
            ['label' => 'Forside', 'href' => '/'],
            ['label' => 'Behandlinger', 'href' => '/behandlinger'],
        ], $settings->navigation_links);
        $this->assertSame([
            ['label' => 'Privatlivspolitik', 'href' => '/privatliv'],
            ['label' => 'Handelsbetingelser', 'href' => '/betingelser'],
        ], $settings->information_links);
        $this->assertTrue($settings->social_links['instagram']['enabled']);
        $this->assertSame('https://instagram.com/maaneskoen', $settings->social_links['instagram']['href']);
        $this->assertFalse($settings->social_links['facebook']['enabled']);
        $this->assertNull($settings->social_links['facebook']['href']);
        $this->assertSame('footer@example.test', $settings->contact_email);
        $this->assertSame('+45 11 22 33 44', $settings->contact_phone);
        $this->assertSame('12345678', $settings->contact_cvr);
        $this->assertTrue($settings->show_contact_email);
        $this->assertTrue($settings->show_contact_phone);
        $this->assertTrue($settings->show_contact_address);
        $this->assertFalse($settings->show_contact_cvr);
    }

    public function test_editors_cannot_save_more_than_eight_footer_links_per_section(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'footer-limit-tenant', 'Footer Limit Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Footer Limit Demo',
            'slug' => 'footer-limit-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $tooManyLinks = collect(range(1, 9))->map(fn (int $index) => [
            'label' => "Link {$index}",
            'href' => "/link-{$index}",
        ])->all();

        $this->actingAs($client)
            ->from("/cms/sites/{$site->id}/global-content/footer")
            ->patch("/cms/sites/{$site->id}/footer", [
                'navigation_links' => $tooManyLinks,
                'information_links' => $tooManyLinks,
                'redirect_to' => "/cms/sites/{$site->id}/global-content/footer",
            ])
            ->assertRedirect("/cms/sites/{$site->id}/global-content/footer")
            ->assertSessionHasErrors([
                'navigation_links',
                'information_links',
            ], null, 'updateSiteFooter');
    }

    public function test_editors_cannot_save_unsafe_header_or_footer_links(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'unsafe-links-tenant', 'Unsafe Links Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Unsafe Links Demo',
            'slug' => 'unsafe-links-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $this->actingAs($client)
            ->from("/cms/sites/{$site->id}/global-content/header")
            ->patch("/cms/sites/{$site->id}/header", [
                'cta_label' => 'Book nu',
                'cta_href' => 'javascript:alert(1)',
                'show_cta' => '1',
                'redirect_to' => "/cms/sites/{$site->id}/global-content/header",
            ])
            ->assertRedirect("/cms/sites/{$site->id}/global-content/header")
            ->assertSessionHasErrors(['cta_href'], null, 'updateSiteHeader');

        $this->actingAs($client)
            ->from("/cms/sites/{$site->id}/global-content/footer")
            ->patch("/cms/sites/{$site->id}/footer", [
                'navigation_links' => [
                    ['label' => 'Ondt link', 'href' => 'javascript:alert(1)'],
                ],
                'information_links' => [
                    ['label' => 'Data link', 'href' => 'data:text/html;base64,SGVq'],
                ],
                'social_links' => [
                    'instagram' => [
                        'enabled' => '1',
                        'href' => 'javascript:alert(2)',
                    ],
                ],
                'redirect_to' => "/cms/sites/{$site->id}/global-content/footer",
            ])
            ->assertRedirect("/cms/sites/{$site->id}/global-content/footer")
            ->assertSessionHasErrors([
                'navigation_links.0.href',
                'information_links.0.href',
                'social_links.instagram.href',
            ], null, 'updateSiteFooter');
    }


    public function test_editors_can_update_global_newsletter_settings(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'newsletter-tenant', 'Newsletter Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Newsletter Demo',
            'slug' => 'newsletter-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $this->actingAs($client)
            ->patch("/cms/sites/{$site->id}/newsletter", [
                'is_enabled' => '1',
                'headline' => 'Tilmeld dig vores nyhedsbrev',
                'copy' => 'Få nyheder, inspiration og særlige tilbud direkte i indbakken.',
                'button_label' => 'Tilmeld',
                'placement' => 'both',
                'delivery_mode' => 'cms',
                'consent_text' => 'Ja tak, jeg vil gerne modtage nyheder og relevante tilbud på e-mail.',
                'redirect_to' => "/cms/sites/{$site->id}/global-content/newsletter",
            ])
            ->assertRedirect("/cms/sites/{$site->id}/global-content/newsletter");

        $this->assertDatabaseHas('site_newsletter_settings', [
            'site_id' => $site->id,
            'is_enabled' => true,
            'headline' => 'Tilmeld dig vores nyhedsbrev',
            'button_label' => 'Tilmeld',
            'placement' => 'both',
            'delivery_mode' => 'cms',
            'consent_text' => 'Ja tak, jeg vil gerne modtage nyheder og relevante tilbud på e-mail.',
        ]);
    }

    public function test_editors_can_link_an_existing_booking_system_with_reference_code(): void
    {
        config()->set('services.bookingsystem.base_url', 'http://localhost/bookingsystem/public');

        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'booking-tenant', 'Booking Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Booking Demo',
            'slug' => 'booking-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $this->actingAs($client)
            ->patch("/cms/sites/{$site->id}/booking", [
                'is_enabled' => '1',
                'connection_mode' => 'existing',
                'booking_reference' => 'salon-maane',
                'submit_action' => 'save',
                'redirect_to' => "/cms/sites/{$site->id}/global-content/booking",
            ])
            ->assertRedirect("/cms/sites/{$site->id}/global-content/booking");

        $this->assertDatabaseHas('site_booking_settings', [
            'site_id' => $site->id,
            'is_enabled' => true,
            'connection_mode' => 'existing',
            'booking_reference' => 'salon-maane',
            'dashboard_url' => 'http://localhost/bookingsystem/public/login',
            'booking_url' => null,
            'owner_name' => null,
            'owner_email' => null,
            'cta_label' => null,
            'use_on_website' => false,
            'show_in_header' => false,
            'show_in_contact_sections' => false,
            'open_in_new_tab' => false,
        ]);
    }

    public function test_editors_can_provision_a_booking_account_from_cms(): void
    {
        config()->set('services.bookingsystem.base_url', 'http://localhost/bookingsystem/public');
        config()->set('services.bookingsystem.integration_token', 'cms-secret');
        config()->set('services.bookingsystem.provision_endpoint', '/integrations/cms/booking-accounts');

        Http::fake([
            'http://localhost/bookingsystem/public/integrations/cms/booking-accounts' => Http::response([
                'tenant_id' => 12,
                'tenant_slug' => 'north-studio',
                'owner_user_id' => 41,
                'owner_email' => 'mia@example.test',
                'location_id' => 1,
                'location_slug' => 'hovedafdeling',
                'dashboard_url' => 'http://localhost/bookingsystem/public/login',
                'app_url' => 'http://localhost/bookingsystem/public/app',
                'booking_url' => 'http://localhost/bookingsystem/public/book-tid?tenant=north-studio&location_id=1',
                'verification_email_sent' => true,
                'message' => 'Bookingkonto og ejerlogin er oprettet.',
            ], 201),
        ]);

        $client = User::factory()->create([
            'role' => 'client',
            'name' => 'Mia Jensen',
            'email' => 'mia@example.test',
        ]);

        $tenant = $this->tenantForUser($client, 'north-studio', 'North Studio', 'owner');
        $tenant->update([
            'company_email' => 'hello@northstudio.test',
            'phone' => '+45 11 22 33 44',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'North Studio',
            'slug' => 'north-studio',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $this->actingAs($client)
            ->patch("/cms/sites/{$site->id}/booking", [
                'is_enabled' => '1',
                'connection_mode' => 'create',
                'submit_action' => 'provision',
                'redirect_to' => "/cms/sites/{$site->id}/global-content/booking",
            ])
            ->assertRedirect("/cms/sites/{$site->id}/global-content/booking")
            ->assertSessionHas('status', 'Bookingsystemet er aktiveret og koblet til sitet.');

        Http::assertSent(function (\Illuminate\Http\Client\Request $request) use ($client): bool {
            return $request->url() === 'http://localhost/bookingsystem/public/integrations/cms/booking-accounts'
                && $request->hasHeader('X-CMS-INTEGRATION-TOKEN', 'cms-secret')
                && $request['tenant_name'] === 'North Studio'
                && $request['tenant_slug'] === 'north-studio'
                && $request['company_email'] === 'hello@northstudio.test'
                && $request['phone'] === '+45 11 22 33 44'
                && $request['site_name'] === 'North Studio'
                && $request['owner_name'] === 'Mia Jensen'
                && $request['owner_email'] === 'mia@example.test'
                && $request['owner_password_hash'] === $client->getAuthPassword();
        });

        $this->assertDatabaseHas('site_booking_settings', [
            'site_id' => $site->id,
            'is_enabled' => true,
            'connection_mode' => 'existing',
            'booking_reference' => 'north-studio',
            'booking_url' => 'http://localhost/bookingsystem/public/book-tid?tenant=north-studio&location_id=1',
            'dashboard_url' => 'http://localhost/bookingsystem/public/login',
            'owner_name' => 'Mia Jensen',
            'owner_email' => 'mia@example.test',
            'cta_label' => null,
            'use_on_website' => false,
            'show_in_header' => false,
            'show_in_contact_sections' => false,
            'open_in_new_tab' => false,
        ]);

        $settings = $site->fresh()->bookingSettings;

        $this->assertNotNull($settings);
        $this->assertNotNull($settings->provisioned_at);
    }

    public function test_booking_reference_is_required_when_linking_an_existing_booking_system(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'booking-validation-tenant', 'Booking Validation Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Booking Validation Demo',
            'slug' => 'booking-validation-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $this->actingAs($client)
            ->from("/cms/sites/{$site->id}/global-content/booking")
            ->patch("/cms/sites/{$site->id}/booking", [
                'is_enabled' => '1',
                'connection_mode' => 'existing',
                'booking_reference' => '',
                'submit_action' => 'save',
                'redirect_to' => "/cms/sites/{$site->id}/global-content/booking",
            ])
            ->assertRedirect("/cms/sites/{$site->id}/global-content/booking")
            ->assertSessionHasErrors(['booking_reference'], null, 'updateSiteBooking');
    }

    public function test_editors_can_upload_svg_logo_for_global_header(): void
    {
        Storage::fake('public');
        Config::set('filesystems.site_media_disk', 'public');

        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = $this->tenantForUser($client, 'header-svg-tenant', 'Header SVG Tenant', 'editor');

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Header SVG Demo',
            'slug' => 'header-svg-demo',
            'theme' => 'editorial',
            'status' => 'ready',
        ]);

        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 24">
    <script>alert('x')</script>
    <text x="0" y="18" font-size="18" fill="#111">SALON</text>
</svg>
SVG;

        $this->actingAs($client)
            ->patch("/cms/sites/{$site->id}/header", [
                'brand_name' => 'Salon SVG',
                'show_brand_name' => '1',
                'logo_upload' => UploadedFile::fake()->createWithContent('logo.svg', $svg),
                'redirect_to' => "/cms/sites/{$site->id}/global-content#header",
            ])
            ->assertRedirect("/cms/sites/{$site->id}/global-content#header");

        $settings = $site->fresh()->headerSettings;

        $this->assertNotNull($settings);
        $this->assertNotNull($settings->logo_path);
        Storage::disk('public')->assertExists($settings->logo_path);

        $storedSvg = Storage::disk('public')->get($settings->logo_path);

        $this->assertStringNotContainsString('<script', strtolower($storedSvg));
        $this->assertStringContainsString('<svg', strtolower($storedSvg));
        $this->assertStringContainsString('<text', strtolower($storedSvg));
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
