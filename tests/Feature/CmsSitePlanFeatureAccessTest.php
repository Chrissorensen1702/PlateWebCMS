<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Site;
use App\Models\SitePageDraft;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsSitePlanFeatureAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_atelier_allows_website_modules_but_blocks_booking_and_custom_code(): void
    {
        ['user' => $user, 'site' => $site] = $this->createSiteContext([
            'name' => 'Atelier',
            'slug' => 'atelier',
            'kind' => 'template',
            'sort_order' => 1,
        ]);

        $draftPage = $this->draftPageFor($user, $site);

        $this->actingAs($user)
            ->get("/cms/sites/{$site->id}/global-content/theme")
            ->assertOk();

        $this->actingAs($user)
            ->get("/cms/sites/{$site->id}/global-content/booking")
            ->assertForbidden();

        $this->actingAs($user)
            ->get("/cms/sites/{$site->id}/pages/{$draftPage->id}/custom-code")
            ->assertForbidden();
    }

    public function test_studio_allows_booking_but_blocks_direct_custom_code_updates(): void
    {
        ['user' => $user, 'site' => $site] = $this->createSiteContext([
            'name' => 'Studio',
            'slug' => 'studio',
            'kind' => 'template',
            'sort_order' => 2,
        ]);

        $draftPage = $this->draftPageFor($user, $site);

        $this->actingAs($user)
            ->get("/cms/sites/{$site->id}/global-content/booking")
            ->assertOk();

        $this->actingAs($user)
            ->patch("/cms/sites/{$site->id}/pages/{$draftPage->id}", [
                'return_to' => 'custom-code',
                'name' => $draftPage->name,
                'slug' => $draftPage->slug,
                'title' => $draftPage->title,
                'meta_description' => $draftPage->meta_description,
                'sort_order' => $draftPage->sort_order,
                'is_published' => 0,
                'is_home' => 1,
                'layout_mode' => 'custom-main',
                'custom_html' => '<section>Secret</section>',
                'custom_css' => '.secret { color: red; }',
            ])
            ->assertForbidden();
    }

    public function test_signature_allows_customer_access_to_custom_code_editor(): void
    {
        ['user' => $user, 'site' => $site] = $this->createSiteContext([
            'name' => 'Signature',
            'slug' => 'signature',
            'kind' => 'custom',
            'sort_order' => 3,
        ]);

        $draftPage = $this->draftPageFor($user, $site);

        $this->actingAs($user)
            ->get("/cms/sites/{$site->id}/pages/{$draftPage->id}/custom-code")
            ->assertOk()
            ->assertSee('Udvidet CMS')
            ->assertSee('Fri HTML og CSS');
    }

    public function test_chairflow_allows_booking_but_blocks_page_builder(): void
    {
        ['user' => $user, 'site' => $site] = $this->createSiteContext([
            'name' => 'Chairflow',
            'slug' => 'chairflow',
            'kind' => 'template',
            'sort_order' => 4,
        ]);

        $draftPage = $this->draftPageFor($user, $site);

        $this->actingAs($user)
            ->get("/cms/sites/{$site->id}")
            ->assertOk()
            ->assertSee("inkluderer ikke sidebygger og indholdsredigering");

        $this->actingAs($user)
            ->get("/cms/sites/{$site->id}/global-content/booking")
            ->assertOk();

        $this->actingAs($user)
            ->get("/cms/sites/{$site->id}/pages/{$draftPage->id}")
            ->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $planAttributes
     * @return array{user: User, tenant: Tenant, site: Site, plan: Plan}
     */
    private function createSiteContext(array $planAttributes): array
    {
        $user = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Feature Tenant',
            'slug' => fake()->unique()->slug(),
            'status' => 'active',
        ]);

        $tenant->users()->attach($user->id, ['role' => 'owner']);

        $plan = Plan::query()->create([
            'name' => $planAttributes['name'],
            'slug' => $planAttributes['slug'],
            'kind' => $planAttributes['kind'],
            'headline' => $planAttributes['name'],
            'summary' => 'Feature test plan',
            'price_from' => 100,
            'build_time' => 'N/A',
            'is_active' => true,
            'sort_order' => $planAttributes['sort_order'],
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'name' => "{$plan->name} Demo",
            'slug' => fake()->unique()->slug(),
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => "{$plan->name} Demo",
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        return compact('user', 'tenant', 'site', 'plan');
    }

    private function draftPageFor(User $user, Site $site): SitePageDraft
    {
        $this->actingAs($user)->get("/cms/sites/{$site->id}")->assertOk();

        return $site->fresh()->draftPages()->firstOrFail();
    }
}
